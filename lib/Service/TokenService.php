<?php

declare(strict_types=1);

namespace OCA\WebRdp\Service;

use OCP\IConfig;

class TokenService {
    public function __construct(
        private IConfig $config,
    ) {}

    /**
     * Generates an AES-256-CBC encrypted token compatible with guacamole-lite's Crypt.js.
     * Format: base64(JSON.stringify({iv: base64(iv), value: base64(ciphertext)}))
     * IV uses bytes 0–127 only: guacamole-lite decodes IV via .toString('ascii') which
     * strips bit 7 of bytes >= 128, so limiting to 0–127 avoids IV corruption.
     */
    public function generateToken(array $connectionData): string {
        $secret = $this->config->getAppValue('web-rdp', 'guac_secret', '');

        $payload = json_encode([
            'connection' => [
                'type' => 'rdp',
                'settings' => [
                    'hostname'    => $connectionData['host'],
                    'port'        => (string)$connectionData['port'],
                    'username'    => $connectionData['username'],
                    'password'    => $connectionData['password'],
                    'domain'      => $connectionData['domain'] ?? '',
                    'width'       => (string)($connectionData['width'] ?? 1920),
                    'height'      => (string)($connectionData['height'] ?? 1080),
                    'color-depth'  => '32',
                    'dpi'          => '96',
                    'ignore-cert'  => 'true',
                    'security'     => 'any',
                ],
            ],
        ]);

        // Key: exactly 32 bytes for AES-256
        $key = substr(str_pad($secret, 32, "\0"), 0, 32);

        // IV: 16 bytes restricted to 0–127 to survive guacamole-lite's ascii IV decode
        $iv = '';
        for ($i = 0; $i < 16; $i++) {
            $iv .= chr(random_int(0, 127));
        }

        $ciphertext = openssl_encrypt($payload, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // Token format expected by guacamole-lite Crypt.decrypt()
        $data = json_encode([
            'iv'    => base64_encode($iv),
            'value' => base64_encode($ciphertext),
        ]);

        return base64_encode($data);
    }

    public function getWsUrl(): string {
        return $this->config->getAppValue('web-rdp', 'guaclite_url', 'ws://localhost:8080');
    }
}
