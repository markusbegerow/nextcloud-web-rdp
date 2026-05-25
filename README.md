# Web RDP for Nextcloud

<img alt="nextcloud-web-rdp" src="https://github.com/user-attachments/assets/9486f908-1543-4d74-8c39-da1ce1b1e2be" />

A Nextcloud app that lets users open RDP (Remote Desktop Protocol) sessions directly in the browser — no VPN client, no separate remote-desktop software needed. Connections are stored securely in Nextcloud and accessible to any logged-in user.

---

## Features

- Create and manage multiple RDP connections from the Nextcloud sidebar
- Live RDP sessions rendered in the browser via Apache Guacamole
- Passwords stored encrypted in the Nextcloud database
- Configurable resolution per connection (720p, 1080p, 1440p, or custom)
- Fullscreen mode
- Works in any modern browser; no plugins required

---

## Prerequisites

| Requirement | Notes |
|---|---|
| Nextcloud ≥ 27 | Already installed and running |
| Debian / Ubuntu server | Where Nextcloud runs; `apt` access required |
| Node.js 18+ | For the guaclite WebSocket proxy |
| An RDP-enabled target machine | Windows Remote Desktop or any RDP server on TCP 3389 |

---

## Quick Start

### 1. Install guacd

```bash
sudo apt update
sudo apt install guacd
sudo systemctl enable --now guacd
```

Verify it is listening on port 4822:

```bash
sudo systemctl status guacd
```

### 2. Set the encryption secret

Choose a random 32-character string. **It must be exactly 32 characters** — AES-256-CBC requires a 32-byte key.

```bash
# Generate one quickly
cat /dev/urandom | tr -dc 'A-Za-z0-9' | head -c 32
```

Create `/etc/guaclite.env` (readable only by root / the service user):

```
GUACD_HOST=127.0.0.1
GUACD_PORT=4822
GUAC_SECRET=change_me_to_exactly_32_chars!!!
```

```bash
sudo chmod 600 /etc/guaclite.env
```

> **Important:** This value must match the `guac_secret` you set later in the Nextcloud admin panel. If they differ, guaclite silently decrypts to garbage and all RDP connections will fail.

### 3. Set up guacamole-lite

guacamole-lite is a lightweight Node.js WebSocket proxy between the browser and guacd.

```bash
sudo mkdir /opt/guaclite
sudo npm install --prefix /opt/guaclite guacamole-lite@1
sudo cp guaclite-server.js /opt/guaclite/
```

Create the systemd service at `/etc/systemd/system/guaclite.service`:

```ini
[Unit]
Description=guacamole-lite WebSocket proxy
After=network.target guacd.service
Requires=guacd.service

[Service]
Type=simple
WorkingDirectory=/opt/guaclite
EnvironmentFile=/etc/guaclite.env
ExecStart=/usr/bin/node /opt/guaclite/guaclite-server.js
Restart=on-failure
User=nobody
Group=nogroup

[Install]
WantedBy=multi-user.target
```

Enable and start it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now guaclite
sudo systemctl status guaclite
# Should show: [guaclite] listening on :8080
```

### 4. Install the web-rdp app

Copy the app into your Nextcloud `custom_apps` directory and enable it:

```bash
# Adjust the target path to match your Nextcloud installation
sudo cp -r web-rdp /var/www/nextcloud/custom_apps/
sudo chown -R www-data:www-data /var/www/nextcloud/custom_apps/web-rdp

sudo -u www-data php /var/www/nextcloud/occ app:enable web-rdp
```

### 5. Configure the admin settings

In Nextcloud: **Admin (top-right) → Administration settings → Connected Accounts → Web RDP**

| Setting | Value |
|---|---|
| guaclite URL | `ws://localhost:8080` |
| Secret key | The same 32-character string you put in `/etc/guaclite.env` as `GUAC_SECRET` |

Or set via command line:

```bash
sudo -u www-data php /var/www/nextcloud/occ config:app:set web-rdp guaclite_url --value="ws://localhost:8080"
sudo -u www-data php /var/www/nextcloud/occ config:app:set web-rdp guac_secret --value="your_32_char_secret_here"
```

### 6. Add your first RDP connection

1. Open your Nextcloud and navigate to **Web RDP** in the app menu
2. Click **+** in the left sidebar
3. Fill in: **Name**, **Host** (IP or hostname of the target machine), **Username**, **Password**
4. Click **Save**, then click **Connect**

The RDP session opens in the main area.

---

## Configuration reference

### Nextcloud app settings (Admin → Connected Accounts → Web RDP)

| Setting | Default | Description |
|---|---|---|
| `guaclite_url` | `ws://localhost:8080` | WebSocket URL of the guaclite service. Change if you run guaclite on a different host or port. |
| `guac_secret` | *(set manually)* | Must match `GUAC_SECRET` in `/etc/guaclite.env`. Used to decrypt the encrypted token the browser sends to guaclite. |

---

## Troubleshooting

### Black / blank screen after connecting

The RDP session connects but shows nothing. Most likely cause: the `guac_secret` in the Nextcloud admin settings does not match `GUAC_SECRET` in `/etc/guaclite.env`. guaclite silently decrypts to garbage, guacd gets an invalid connection config, and sends no display data.

Fix: make sure both values are identical, then reconnect.

### `Failed to decrypt token: Unexpected token ... is not valid JSON`

Shown in guaclite logs. The secret is mismatched or the token format is wrong. Verify:
1. `GUAC_SECRET` in `/etc/guaclite.env` equals the `guac_secret` in the Nextcloud admin panel
2. The value is exactly 32 characters

```bash
journalctl -u guaclite --since "5 minutes ago"
```

### `Invalid key length` in guaclite logs

`GUAC_SECRET` is not exactly 32 characters. AES-256-CBC requires a 32-byte key. Count the characters and adjust.

### WebSocket connection blocked (browser console: `ERR_BLOCKED_BY_RESPONSE`)

The Nextcloud Content Security Policy is blocking the WebSocket. Make sure the `guaclite_url` in the admin settings exactly matches the URL the browser is trying to connect to (including `ws://` vs `wss://` and the port).

### RDP connection refused (port 3389)

- Confirm the target machine has Remote Desktop enabled
- Confirm no firewall blocks TCP 3389
- Test connectivity from the server: `nc -zv <host> 3389`

### Checking logs

```bash
# guaclite — WebSocket / token errors
journalctl -u guaclite -f

# guacd — RDP-level errors (auth failures, display issues)
journalctl -u guacd -f

# Nextcloud PHP errors
sudo tail -f /var/www/nextcloud/data/nextcloud.log
```

---

## Development

The frontend is a Vue 3 SPA built with webpack. After editing files in `src/`, rebuild:

```bash
cd custom_apps/web-rdp

# Install dependencies (first time only)
npm install

# Watch mode — rebuilds automatically on file save
npm run dev

# Production build
npm run build
```

On Windows PowerShell (the npm scripts use `NODE_ENV=x` which fails in cmd/PowerShell):

```powershell
$env:NODE_ENV='production'; npx webpack --config webpack.config.js
```

The compiled output goes to `js/web-rdp-main.js`, which is served by Nextcloud automatically — no restart needed after a rebuild.

---

## Architecture

```
Browser (Vue 3 + guacamole-common-js)
    ↕  AJAX/POST (JSON)
Nextcloud PHP  ·  ConnectionController  ·  TunnelController
    ↕  AES-256-CBC encrypted token  (WebSocket)
guacamole-lite  (ws://localhost:8080)
    ↕  Guacamole protocol  (TCP 4822)
guacd
    ↕  RDP  (TCP 3389)
Target machine (Windows Remote Desktop / any RDP server)
```

---

## License

[GPL-3.0](https://www.gnu.org/licenses/gpl-3.0.html) © [Markus Begerow](https://markus-begerow.de/linktree)

---

## 🙋‍♂️ Get Involved

If you encounter any issues or have questions:
- 🐛 [Report bugs](https://github.com/markusbegerow/nextcloud-web-rdp/issues)
- 💡 [Request features](https://github.com/markusbegerow/nextcloud-web-rdp/issues)
- ⭐ Star the repo if you find it useful!

## ☕ Support the Project

If you like this project, support further development with a repost or coffee:

<a href="https://www.linkedin.com/sharing/share-offsite/?url=https://github.com/MarkusBegerow/nextcloud-web-rdp" target="_blank"> <img src="https://img.shields.io/badge/💼-Share%20on%20LinkedIn-blue" /> </a>

[![Buy Me a Coffee](https://img.shields.io/badge/☕-Buy%20me%20a%20coffee-yellow)](https://paypal.me/MarkusBegerow)

## 📬 Contact

- 🧑‍💻 [Markus Begerow](https://linkedin.com/in/markusbegerow)
- 💾 [GitHub](https://github.com/markusbegerow)
- ✉️ [Twitter](https://x.com/markusbegerow)
