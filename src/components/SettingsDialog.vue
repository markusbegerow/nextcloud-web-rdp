<template>
    <NcModal
        :name="t('web-rdp', 'Settings')"
        @close="$emit('close')">
        <div class="web-rdp-settings">
            <h2>{{ t('web-rdp', 'Settings') }}</h2>

            <div class="web-rdp-settings-group">
                <label class="web-rdp-settings-label" for="web-rdp-default-res">
                    {{ t('web-rdp', 'Default resolution for new connections') }}
                </label>
                <select id="web-rdp-default-res" v-model="form.defaultResolution" class="web-rdp-select">
                    <option value="1280x720">1280×720</option>
                    <option value="1920x1080">1920×1080</option>
                    <option value="2560x1440">2560×1440</option>
                </select>
            </div>

            <div class="web-rdp-settings-group">
                <label class="web-rdp-settings-label web-rdp-checkbox-label">
                    <input
                        v-model="form.autoReconnect"
                        type="checkbox"
                        class="web-rdp-checkbox" />
                    {{ t('web-rdp', 'Automatically reconnect when the session drops') }}
                </label>
            </div>

            <div class="web-rdp-settings-group web-rdp-settings-admin">
                <p class="web-rdp-settings-label">{{ t('web-rdp', 'Server (admin)') }}</p>
                <div class="web-rdp-settings-server">
                    <code v-if="guacliteUrl">{{ guacliteUrl }}</code>
                    <span v-else class="web-rdp-muted">{{ t('web-rdp', 'Not configured') }}</span>
                </div>
                <a :href="adminUrl" class="web-rdp-settings-adminlink">
                    {{ t('web-rdp', 'Open admin settings') }} →
                </a>
            </div>

            <NcNoteCard v-if="error" type="error">{{ error }}</NcNoteCard>

            <div class="web-rdp-settings-actions">
                <NcButton type="primary" :disabled="loading" @click="save">
                    {{ t('web-rdp', 'Save') }}
                </NcButton>
                <NcButton @click="$emit('close')">
                    {{ t('web-rdp', 'Cancel') }}
                </NcButton>
            </div>
        </div>
    </NcModal>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { NcModal, NcButton, NcNoteCard } from '@nextcloud/vue'

export default {
    name: 'SettingsDialog',
    components: { NcModal, NcButton, NcNoteCard },
    emits: ['close'],

    props: {
        guacliteUrl: { type: String, default: '' },
    },

    data() {
        return {
            form: {
                defaultResolution: '1920x1080',
                autoReconnect: false,
            },
            loading: false,
            error: null,
            adminUrl: generateUrl('/settings/admin/connected-accounts'),
        }
    },

    async mounted() {
        await this.load()
    },

    methods: {
        async load() {
            try {
                const { data } = await axios.get(generateUrl('/apps/web-rdp/api/user-settings'))
                this.form.defaultResolution = data.defaultResolution ?? '1920x1080'
                this.form.autoReconnect = !!data.autoReconnect
            } catch (e) {
                // use defaults on error
            }
        },

        async save() {
            this.error = null
            this.loading = true
            try {
                await axios.post(generateUrl('/apps/web-rdp/api/user-settings'), this.form)
                this.$emit('close')
            } catch (e) {
                this.error = e.response?.data?.error ?? t('web-rdp', 'Failed to save settings')
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.web-rdp-settings {
    padding: 24px;
    min-width: 360px;
    max-width: 480px;
}

.web-rdp-settings h2 {
    margin: 0 0 20px;
}

.web-rdp-settings-group {
    margin-bottom: 20px;
}

.web-rdp-settings-label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

.web-rdp-select {
    width: 100%;
    padding: 8px 10px;
    border: 2px solid var(--color-border-dark, #ccc);
    border-radius: var(--border-radius, 4px);
    background: var(--color-main-background);
    color: var(--color-main-text);
    font-size: 1em;
    cursor: pointer;
}

.web-rdp-select:focus {
    outline: none;
    border-color: var(--color-primary);
}

.web-rdp-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: normal;
    cursor: pointer;
}

.web-rdp-checkbox {
    width: 16px;
    height: 16px;
    cursor: pointer;
    flex-shrink: 0;
}

.web-rdp-settings-admin {
    border-top: 1px solid var(--color-border);
    padding-top: 16px;
}

.web-rdp-settings-server {
    margin-bottom: 6px;
}

.web-rdp-settings-server code {
    font-family: monospace;
    font-size: 0.9em;
    background: var(--color-background-dark);
    padding: 3px 8px;
    border-radius: 4px;
}

.web-rdp-muted {
    color: var(--color-text-maxcontrast);
    font-style: italic;
}

.web-rdp-settings-adminlink {
    font-size: 0.9em;
    color: var(--color-primary);
    text-decoration: none;
}

.web-rdp-settings-adminlink:hover {
    text-decoration: underline;
}

.web-rdp-settings-actions {
    display: flex;
    gap: 8px;
    margin-top: 20px;
}
</style>
