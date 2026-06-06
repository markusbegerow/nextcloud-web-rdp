<?php
/** @var \OCP\IL10N $l */
/** @var array $_ */
style('web-rdp', 'style');
?>
<div id="web-rdp-admin-settings" class="section">
    <h2><?php p($l->t('Web RDP')); ?></h2>

    <p class="settings-hint">
        <?php p($l->t('Configure the guacamole-lite WebSocket proxy and shared secret.')); ?>
    </p>

    <div class="web-rdp-admin-form">
        <div class="form-group">
            <label for="web-rdp-guaclite-url"><?php p($l->t('guacamole-lite WebSocket URL')); ?></label>
            <input type="url"
                   id="web-rdp-guaclite-url"
                   name="guacliteUrl"
                   class="input-wide"
                   value="<?php p($_['guacliteUrl']); ?>"
                   placeholder="ws://localhost:8080" />
            <span class="hint"><?php p($l->t('WebSocket URL of the guacamole-lite proxy service')); ?></span>
        </div>

        <div class="form-group">
            <label for="web-rdp-guac-secret"><?php p($l->t('Shared Secret')); ?></label>
            <input type="password"
                   id="web-rdp-guac-secret"
                   name="guacSecret"
                   class="input-wide"
                   value="<?php p($_['guacSecret']); ?>"
                   placeholder="<?php p($l->t('32-character encryption key')); ?>" />
            <span class="hint"><?php p($l->t('Must match the GUAC_SECRET environment variable of guacamole-lite')); ?></span>
        </div>

        <button id="web-rdp-admin-save" class="button primary">
            <?php p($l->t('Save')); ?>
        </button>
        <span id="web-rdp-admin-status" class="msg" style="display:none;margin-left:10px;"></span>
    </div>
</div>

<script>
document.getElementById('web-rdp-admin-save').addEventListener('click', function() {
    const url   = document.getElementById('web-rdp-guaclite-url').value;
    const secret = document.getElementById('web-rdp-guac-secret').value;
    const status = document.getElementById('web-rdp-admin-status');

    fetch(OC.generateUrl('/apps/web-rdp/api/settings'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'requesttoken': OC.requestToken,
        },
        body: JSON.stringify({ guacliteUrl: url, guacSecret: secret }),
    })
    .then(r => r.json())
    .then(() => {
        status.textContent = t('web-rdp', 'Saved');
        status.className = 'msg success';
        status.style.display = 'inline';
        setTimeout(() => { status.style.display = 'none'; }, 3000);
    })
    .catch(() => {
        status.textContent = t('web-rdp', 'Error saving settings');
        status.className = 'msg error';
        status.style.display = 'inline';
    });
});
</script>
