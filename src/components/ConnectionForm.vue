<template>
    <NcModal
        :name="isEdit ? t('web-rdp', 'Edit connection') : t('web-rdp', 'New connection')"
        @close="$emit('cancelled')">
        <div class="web-rdp-form-modal">
            <h2>{{ isEdit ? t('web-rdp', 'Edit connection') : t('web-rdp', 'New connection') }}</h2>

            <form @submit.prevent="submit">
                <NcTextField
                    v-model="form.name"
                    :label="t('web-rdp', 'Name')"
                    :placeholder="t('web-rdp', 'My Windows Server')"
                    required />

                <NcTextField
                    v-model="form.host"
                    :label="t('web-rdp', 'Host / IP address')"
                    :placeholder="t('web-rdp', '192.168.1.100')"
                    required />

                <NcTextField
                    v-model.number="form.port"
                    type="number"
                    :label="t('web-rdp', 'Port')"
                    min="1"
                    max="65535" />

                <NcTextField
                    v-model="form.username"
                    :label="t('web-rdp', 'Username')" />

                <div class="password-field">
                    <NcTextField
                        v-model="form.password"
                        :label="t('web-rdp', 'Password')"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="isEdit ? t('web-rdp', 'Leave blank to keep existing') : ''" />
                    <NcButton
                        type="tertiary"
                        :aria-label="t('web-rdp', 'Toggle password visibility')"
                        @click="showPassword = !showPassword">
                        <template #icon>
                            <Eye v-if="!showPassword" :size="20" />
                            <EyeOff v-else :size="20" />
                        </template>
                    </NcButton>
                </div>

                <NcTextField
                    v-model="form.domain"
                    :label="t('web-rdp', 'Domain (optional)')"
                    :placeholder="t('web-rdp', 'MYDOMAIN')" />

                <div class="resolution-field">
                    <label>{{ t('web-rdp', 'Resolution') }}</label>
                    <div class="web-rdp-resolution-presets">
                        <NcButton
                            v-for="preset in resolutionPresets"
                            :key="preset.label"
                            :type="isActivePreset(preset) ? 'primary' : 'secondary'"
                            @click="applyPreset(preset)">
                            {{ preset.label }}
                        </NcButton>
                    </div>
                    <div class="resolution-custom">
                        <NcTextField
                            v-model.number="form.width"
                            type="number"
                            :label="t('web-rdp', 'Width')"
                            min="640"
                            max="7680" />
                        <span>×</span>
                        <NcTextField
                            v-model.number="form.height"
                            type="number"
                            :label="t('web-rdp', 'Height')"
                            min="480"
                            max="4320" />
                    </div>
                </div>

                <NcNoteCard v-if="error" type="error">{{ error }}</NcNoteCard>

                <div class="form-actions">
                    <NcButton native-type="submit" type="primary" :disabled="loading">
                        {{ t('web-rdp', 'Save') }}
                    </NcButton>
                    <NcButton @click="$emit('cancelled')">
                        {{ t('web-rdp', 'Cancel') }}
                    </NcButton>
                </div>
            </form>
        </div>
    </NcModal>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { NcModal, NcTextField, NcButton, NcNoteCard } from '@nextcloud/vue'
import { Eye, EyeOff } from 'lucide-vue-next'

const PRESETS = [
    { label: '1280×720',  width: 1280, height: 720  },
    { label: '1920×1080', width: 1920, height: 1080 },
    { label: '2560×1440', width: 2560, height: 1440 },
]

export default {
    name: 'ConnectionForm',
    components: { NcModal, NcTextField, NcButton, NcNoteCard, Eye, EyeOff },
    emits: ['saved', 'cancelled'],

    props: {
        connection: { type: Object, default: null },
        defaultResolution: { type: String, default: '1920x1080' },
    },

    data() {
        const c = this.connection
        const [dw, dh] = (this.defaultResolution || '1920x1080').split('x').map(Number)
        return {
            form: {
                name:     c?.name     ?? '',
                host:     c?.host     ?? '',
                port:     c?.port     ?? 3389,
                username: c?.username ?? '',
                password: '',
                domain:   c?.domain   ?? '',
                width:    c?.width    ?? (dw || 1920),
                height:   c?.height   ?? (dh || 1080),
            },
            showPassword: false,
            loading: false,
            error: null,
            resolutionPresets: PRESETS,
        }
    },

    computed: {
        isEdit() { return !!this.connection },
    },

    methods: {
        isActivePreset(preset) {
            return this.form.width === preset.width && this.form.height === preset.height
        },

        applyPreset(preset) {
            this.form.width  = preset.width
            this.form.height = preset.height
        },

        async submit() {
            this.error = null
            if (!this.form.host) {
                this.error = t('web-rdp', 'Host is required')
                return
            }
            const port = parseInt(this.form.port)
            if (port < 1 || port > 65535) {
                this.error = t('web-rdp', 'Port must be between 1 and 65535')
                return
            }

            this.loading = true
            try {
                const payload = { ...this.form }
                if (this.isEdit && !payload.password) delete payload.password

                if (this.isEdit) {
                    await axios.put(generateUrl(`/apps/web-rdp/api/connections/${this.connection.id}`), payload)
                } else {
                    await axios.post(generateUrl('/apps/web-rdp/api/connections'), payload)
                }
                this.$emit('saved')
            } catch (e) {
                this.error = e.response?.data?.error ?? t('web-rdp', 'An error occurred')
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.web-rdp-form-modal {
    padding: 20px;
    min-width: 380px;
    max-width: 520px;
}

.password-field {
    display: flex;
    align-items: flex-end;
    gap: 4px;
}

.resolution-field label {
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}

.resolution-custom {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.form-actions {
    display: flex;
    gap: 8px;
    margin-top: 16px;
}
</style>
