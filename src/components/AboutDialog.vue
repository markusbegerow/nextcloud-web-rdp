<template>
    <NcModal
        :name="t('web-rdp', 'Info')"
        @close="$emit('close')">
        <div class="web-rdp-about">
            <!-- App identity -->
            <div class="web-rdp-about-header">
                <div class="web-rdp-about-icon">
                    <Monitor :size="40" />
                </div>
                <div class="web-rdp-about-title">
                    <h2>Web RDP</h2>
                    <p class="web-rdp-about-subtitle">{{ t('web-rdp', 'Browser-based RDP client for Nextcloud') }}</p>
                </div>
                <span class="web-rdp-about-version">v1.0</span>
            </div>

            <!-- Application Information -->
            <div class="web-rdp-about-section">
                <p class="web-rdp-section-heading">{{ t('web-rdp', 'Application Information') }}</p>
                <dl class="web-rdp-about-meta">
                    <div>
                        <dt>{{ t('web-rdp', 'Author') }}</dt>
                        <dd>Markus Begerow</dd>
                    </div>
                    <div>
                        <dt>{{ t('web-rdp', 'Version') }}</dt>
                        <dd>1.0</dd>
                    </div>
                    <div>
                        <dt>{{ t('web-rdp', 'License') }}</dt>
                        <dd>GPL v3</dd>
                    </div>
                </dl>
            </div>

            <!-- Features -->
            <div class="web-rdp-about-section">
                <p class="web-rdp-section-heading">{{ t('web-rdp', 'Features') }}</p>
                <ul class="web-rdp-about-features">
                    <li>{{ t('web-rdp', 'Browser-based RDP client — no plugins or client software required') }}</li>
                    <li>{{ t('web-rdp', 'Powered by Apache Guacamole (guacd) for native RDP protocol support') }}</li>
                    <li>{{ t('web-rdp', 'Saved connections with AES-256-encrypted credentials') }}</li>
                    <li>{{ t('web-rdp', 'Multiple connections with instant switching via sidebar') }}</li>
                    <li>{{ t('web-rdp', 'Fullscreen mode for immersive remote sessions') }}</li>
                    <li>{{ t('web-rdp', 'Auto-reconnect on unexpected disconnection') }}</li>
                </ul>
            </div>

            <!-- Links -->
            <div class="web-rdp-about-section">
                <p class="web-rdp-section-heading">{{ t('web-rdp', 'Links') }}</p>
                <div class="web-rdp-about-links">
                    <a href="https://markus-begerow.de/linktree" target="_blank" rel="noopener" class="web-rdp-about-link">
                        <Globe :size="16" class="web-rdp-about-link-icon" />
                        <div class="web-rdp-about-link-body">
                            <span class="web-rdp-about-link-label">{{ t('web-rdp', 'Website') }}</span>
                            <span class="web-rdp-about-link-sub">markus-begerow.de/linktree</span>
                        </div>
                        <span class="web-rdp-about-link-arrow">↗</span>
                    </a>
                    <a href="https://github.com/markusbegerow/nextcloud-web-rdp" target="_blank" rel="noopener" class="web-rdp-about-link">
                        <Github :size="16" class="web-rdp-about-link-icon" />
                        <div class="web-rdp-about-link-body">
                            <span class="web-rdp-about-link-label">GitHub</span>
                            <span class="web-rdp-about-link-sub">markusbegerow/nextcloud-web-rdp</span>
                        </div>
                        <span class="web-rdp-about-link-arrow">↗</span>
                    </a>
                    <a href="https://www.paypal.com/paypalme/MarkusBegerow" target="_blank" rel="noopener" class="web-rdp-about-link">
                        <Coffee :size="16" class="web-rdp-about-link-icon" />
                        <div class="web-rdp-about-link-body">
                            <span class="web-rdp-about-link-label">{{ t('web-rdp', 'Donate a coffee') }}</span>
                            <span class="web-rdp-about-link-sub">paypal.me/MarkusBegerow</span>
                        </div>
                        <span class="web-rdp-about-link-arrow">↗</span>
                    </a>
                </div>
            </div>
        </div>
    </NcModal>
</template>

<script>
import { NcModal } from '@nextcloud/vue'
import { Monitor, Globe, Github, Coffee } from 'lucide-vue-next'

export default {
    name: 'AboutDialog',
    components: { NcModal, Monitor, Globe, Github, Coffee },
    emits: ['close'],
}
</script>

<style scoped>
.web-rdp-about {
    padding: 20px;
    max-width: 480px;
}

/* App identity row */
.web-rdp-about-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.web-rdp-about-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-primary-element);
    border-radius: 12px;
    color: #fff;
    flex-shrink: 0;
}

.web-rdp-about-title {
    flex: 1;
    min-width: 0;
}

.web-rdp-about-title h2 {
    margin: 0 0 2px;
    font-size: 1.2em;
    font-weight: 700;
}

.web-rdp-about-subtitle {
    margin: 0;
    font-size: 0.8em;
    color: var(--color-text-maxcontrast);
}

.web-rdp-about-version {
    font-size: 0.8em;
    color: var(--color-text-maxcontrast);
    border: 1px solid var(--color-border);
    border-radius: 999px;
    padding: 2px 8px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Section wrapper */
.web-rdp-about-section {
    border-top: 1px solid var(--color-border);
    padding-top: 16px;
    margin-bottom: 16px;
}

/* Section heading label */
.web-rdp-section-heading {
    margin: 0 0 10px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    color: var(--color-text-maxcontrast);
}

/* Meta info dl */
.web-rdp-about-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin: 0;
}

.web-rdp-about-meta > div {
    display: flex;
    gap: 8px;
    font-size: 0.875em;
}

.web-rdp-about-meta dt {
    color: var(--color-text-maxcontrast);
    width: 64px;
    flex-shrink: 0;
}

.web-rdp-about-meta dd {
    margin: 0;
    font-weight: 600;
    color: var(--color-main-text);
}

/* Features list */
.web-rdp-about-features {
    margin: 0;
    padding-left: 18px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.web-rdp-about-features li {
    font-size: 0.8em;
    color: var(--color-text-maxcontrast);
    line-height: 1.5;
}

/* Links */
.web-rdp-about-links {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.web-rdp-about-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    color: var(--color-main-text);
    text-decoration: none;
    transition: border-color 0.15s, background 0.15s;
}

.web-rdp-about-link:hover {
    border-color: var(--color-primary-element);
    background: var(--color-background-hover);
}

.web-rdp-about-link-icon {
    color: var(--color-text-maxcontrast);
    flex-shrink: 0;
}

.web-rdp-about-link:hover .web-rdp-about-link-icon {
    color: var(--color-primary-element);
}

.web-rdp-about-link-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.web-rdp-about-link-label {
    font-size: 0.875em;
    font-weight: 600;
}

.web-rdp-about-link-sub {
    font-size: 0.75em;
    color: var(--color-text-maxcontrast);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.web-rdp-about-link-arrow {
    color: var(--color-text-maxcontrast);
    font-size: 0.875em;
    flex-shrink: 0;
}
</style>
