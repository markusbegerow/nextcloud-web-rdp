<template>
    <div class="web-rdp-viewer" ref="viewer">
        <div class="web-rdp-viewer-toolbar">
            <span class="connection-name">{{ connectionName }}</span>
            <span :class="['web-rdp-status-chip', status]">{{ statusLabel }}</span>
            <NcButton type="tertiary" :title="t('web-rdp', 'Fullscreen')" @click="toggleFullscreen">
                <template #icon><Maximize :size="20" /></template>
            </NcButton>
            <NcButton type="tertiary" :title="t('web-rdp', 'Disconnect')" @click="disconnect">
                <template #icon><X :size="20" /></template>
            </NcButton>
        </div>

        <NcNoteCard v-if="errorMsg" type="error" class="viewer-error">
            {{ errorMsg }}
        </NcNoteCard>

        <div v-if="status === 'connecting' || status === 'reconnecting'" class="viewer-loading">
            <NcLoadingIcon :size="48" />
            <p>{{ t('web-rdp', 'Connecting…') }}</p>
        </div>

        <div class="web-rdp-display-wrapper" ref="displayWrapper">
            <div ref="display"></div>
        </div>
    </div>
</template>

<script>
import Guacamole from 'guacamole-common-js'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { NcButton, NcNoteCard, NcLoadingIcon } from '@nextcloud/vue'
import { Maximize, X } from 'lucide-vue-next'

export default {
    name: 'RdpViewer',
    components: { NcButton, NcNoteCard, NcLoadingIcon, Maximize, X },
    emits: ['disconnected'],

    props: {
        connectionId: { type: Number, required: true },
        connectionName: { type: String, default: '' },
        autoReconnect: { type: Boolean, default: false },
    },

    data() {
        return {
            status: 'connecting',
            errorMsg: null,
            client: null,
            keyboard: null,
            mouse: null,
            userDisconnected: false,
        }
    },

    computed: {
        statusLabel() {
            return {
                connecting:   t('web-rdp', 'Connecting'),
                connected:    t('web-rdp', 'Connected'),
                disconnected: t('web-rdp', 'Disconnected'),
                reconnecting: t('web-rdp', 'Reconnecting…'),
                error:        t('web-rdp', 'Error'),
            }[this.status] ?? this.status
        },
    },

    watch: {
        connectionId(newId) {
            this.cleanup()
            if (newId) this.connect()
        },
    },

    async mounted() {
        await this.connect()
    },

    beforeUnmount() {
        this.cleanup()
    },

    methods: {
        async connect() {
            this.userDisconnected = false
            this.status = 'connecting'
            this.errorMsg = null

            let tokenData
            try {
                const { data } = await axios.post(
                    generateUrl(`/apps/web-rdp/api/tunnel/${this.connectionId}`)
                )
                tokenData = data
            } catch (e) {
                this.status = 'error'
                this.errorMsg = t('web-rdp', 'Failed to get connection token. Check admin settings.')
                return
            }

            const tunnel = new Guacamole.WebSocketTunnel(tokenData.wsUrl)

            this.client = new Guacamole.Client(tunnel)

            // Mount display canvas
            const display = this.$refs.display
            display.innerHTML = ''
            display.appendChild(this.client.getDisplay().getElement())

            // CSS-scale display to fit container; track scale for mouse coordinate correction
            let currentScale = 1
            const scaleDisplay = () => {
                const wrapper = this.$refs.displayWrapper
                if (!wrapper) return
                const dw = this.client.getDisplay().getWidth()
                const dh = this.client.getDisplay().getHeight()
                if (!dw || !dh) return
                currentScale = Math.min(wrapper.offsetWidth / dw, wrapper.offsetHeight / dh, 1)
                this.client.getDisplay().scale(currentScale)
            }
            this.client.getDisplay().onresize = scaleDisplay
            this._resizeObserver = new ResizeObserver(scaleDisplay)
            this._resizeObserver.observe(this.$refs.displayWrapper)

            // State changes
            this.client.onstatechange = (state) => {
                // Guacamole.Client states: 0=idle,1=connecting,2=waiting,3=connected,4=disconnecting,5=disconnected
                if (state === 3) {
                    this.status = 'connected'
                } else if (state === 5) {
                    if (!this.userDisconnected && this.autoReconnect) {
                        this.status = 'reconnecting'
                        this._reconnectTimer = setTimeout(() => this.connect(), 3000)
                    } else {
                        this.status = 'disconnected'
                        this.$emit('disconnected')
                    }
                }
            }

            this.client.onerror = (error) => {
                this.status = 'error'
                this.errorMsg = error.message ?? t('web-rdp', 'Connection error')
            }

            // Keyboard
            this.keyboard = new Guacamole.Keyboard(document)
            this.keyboard.onkeydown = (keysym) => this.client.sendKeyEvent(1, keysym)
            this.keyboard.onkeyup   = (keysym) => this.client.sendKeyEvent(0, keysym)

            // Mouse
            const displayEl = this.client.getDisplay().getElement()
            this.mouse = new Guacamole.Mouse(displayEl)
            const sendMouse = (mouseState) => {
                this.client.sendMouseState(currentScale === 1 ? mouseState : {
                    x:      Math.floor(mouseState.x / currentScale),
                    y:      Math.floor(mouseState.y / currentScale),
                    left:   mouseState.left,
                    middle: mouseState.middle,
                    right:  mouseState.right,
                    up:     mouseState.up,
                    down:   mouseState.down,
                })
            }
            this.mouse.onmousedown = sendMouse
            this.mouse.onmouseup   = sendMouse
            this.mouse.onmousemove = sendMouse

            this.client.connect(`token=${encodeURIComponent(tokenData.token)}`)
        },

        disconnect() {
            this.userDisconnected = true
            clearTimeout(this._reconnectTimer)
            this.client?.disconnect()
            this.status = 'disconnected'
            this.$emit('disconnected')
        },

        cleanup() {
            clearTimeout(this._reconnectTimer)
            this._resizeObserver?.disconnect()
            this._resizeObserver = null
            this.keyboard?.reset()
            this.keyboard = null
            this.mouse = null
            this.client?.disconnect()
            this.client = null
            if (this.$refs.display) this.$refs.display.innerHTML = ''
        },

        toggleFullscreen() {
            const el = this.$refs.viewer
            if (!document.fullscreenElement) {
                el.requestFullscreen?.()
            } else {
                document.exitFullscreen?.()
            }
        },
    },
}
</script>

<style scoped>
.web-rdp-viewer {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.viewer-loading {
    position: absolute;
    inset: 60px 0 0 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: var(--color-text-maxcontrast);
    background: rgba(0,0,0,0.4);
    z-index: 10;
}

.viewer-error {
    margin: 8px;
    flex-shrink: 0;
}
</style>
