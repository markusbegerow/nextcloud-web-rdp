<template>
    <NcContent app-name="web-rdp">
        <NcAppNavigation>
            <template #list>
                <NcAppNavigationNew
                    :text="t('web-rdp', 'New connection')"
                    @click="showForm = true">
                    <template #icon><Plus :size="20" /></template>
                </NcAppNavigationNew>
                <NcAppNavigationItem
                    v-for="conn in connections"
                    :key="conn.id"
                    :name="conn.name"
                    :class="{ active: selectedId === conn.id }"
                    menu-placement="right-start"
                    @click="selectConnection(conn.id)">
                    <template #icon>
                        <Monitor :size="20" />
                    </template>
                    <template #actions>
                        <NcActionButton @click.stop="editConnection(conn)">
                            <template #icon><Pencil :size="20" /></template>
                            {{ t('web-rdp', 'Edit') }}
                        </NcActionButton>
                        <NcActionButton @click.stop="deleteConnection(conn.id)">
                            <template #icon><Delete :size="20" /></template>
                            {{ t('web-rdp', 'Delete') }}
                        </NcActionButton>
                    </template>
                </NcAppNavigationItem>
            </template>

            <template #footer>
                <div class="web-rdp-sidebar-actions">
                    <NcButton type="tertiary" @click="showAbout = true">
                        <template #icon><Info :size="16" /></template>
                        {{ t('web-rdp', 'Info') }}
                    </NcButton>
                    <NcButton type="tertiary" @click="showSettings = true">
                        <template #icon><Settings :size="16" /></template>
                        {{ t('web-rdp', 'Settings') }}
                    </NcButton>
                </div>
            </template>
        </NcAppNavigation>

        <NcAppContent>
            <RdpViewer
                v-if="selectedId"
                :connection-id="selectedId"
                :connection-name="selectedName"
                :auto-reconnect="userPrefs.autoReconnect"
                @disconnected="selectedId = null" />
            <div v-else class="web-rdp-empty-state">
                <Monitor :size="64" />
                <p>{{ t('web-rdp', 'Select a connection from the sidebar or create a new one.') }}</p>
                <NcButton @click="showForm = true">
                    <template #icon><Plus :size="20" /></template>
                    {{ t('web-rdp', 'New connection') }}
                </NcButton>
            </div>
        </NcAppContent>
    </NcContent>

    <ConnectionForm
        v-if="showForm"
        :connection="editingConnection"
        :default-resolution="editingConnection ? null : userPrefs.defaultResolution"
        @saved="onSaved"
        @cancelled="closeForm" />

    <AboutDialog v-if="showAbout" @close="showAbout = false" />

    <SettingsDialog
        v-if="showSettings"
        :guaclite-url="guacliteUrl"
        @close="onSettingsClose" />
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import {
    NcContent,
    NcAppNavigation,
    NcAppNavigationItem,
    NcAppNavigationNew,
    NcAppContent,
    NcButton,
    NcActionButton,
} from '@nextcloud/vue'
import { Monitor, Pencil, Delete, Plus, Info, Settings } from 'lucide-vue-next'
import RdpViewer from './components/RdpViewer.vue'
import ConnectionForm from './components/ConnectionForm.vue'
import AboutDialog from './components/AboutDialog.vue'
import SettingsDialog from './components/SettingsDialog.vue'

export default {
    name: 'App',

    components: {
        NcContent, NcAppNavigation, NcAppNavigationItem, NcAppNavigationNew,
        NcAppContent, NcButton, NcActionButton,
        Monitor, Pencil, Delete, Plus, Info, Settings,
        RdpViewer, ConnectionForm, AboutDialog, SettingsDialog,
    },

    data() {
        return {
            connections: [],
            selectedId: null,
            showForm: false,
            editingConnection: null,
            showAbout: false,
            showSettings: false,
            userPrefs: { defaultResolution: '1920x1080', autoReconnect: false },
            guacliteUrl: '',
        }
    },

    computed: {
        selectedName() {
            return this.connections.find(c => c.id === this.selectedId)?.name ?? ''
        },
    },

    async created() {
        await Promise.all([
            this.loadConnections(),
            this.loadUserPrefs(),
            this.loadGuacliteUrl(),
        ])
    },

    methods: {
        async loadConnections() {
            const { data } = await axios.get(generateUrl('/apps/web-rdp/api/connections'))
            this.connections = data
        },

        async loadUserPrefs() {
            try {
                const { data } = await axios.get(generateUrl('/apps/web-rdp/api/user-settings'))
                this.userPrefs = {
                    defaultResolution: data.defaultResolution ?? '1920x1080',
                    autoReconnect: !!data.autoReconnect,
                }
            } catch (e) { /* use defaults */ }
        },

        async loadGuacliteUrl() {
            try {
                const { data } = await axios.get(generateUrl('/apps/web-rdp/api/settings'))
                this.guacliteUrl = data.guacliteUrl ?? ''
            } catch (e) { /* admin-only endpoint, ignore for regular users */ }
        },

        selectConnection(id) {
            this.selectedId = id
        },

        editConnection(conn) {
            this.editingConnection = { ...conn }
            this.showForm = true
        },

        async deleteConnection(id) {
            if (!confirm(t('web-rdp', 'Delete this connection?'))) return
            await axios.delete(generateUrl(`/apps/web-rdp/api/connections/${id}`))
            if (this.selectedId === id) this.selectedId = null
            await this.loadConnections()
        },

        async onSaved() {
            this.closeForm()
            await this.loadConnections()
        },

        closeForm() {
            this.showForm = false
            this.editingConnection = null
        },

        async onSettingsClose() {
            this.showSettings = false
            await this.loadUserPrefs()
        },
    },
}
</script>

<style scoped>
.web-rdp-sidebar-actions {
    display: flex;
    gap: 4px;
    padding: 4px 8px;
    border-top: 1px solid var(--color-border);
}

.web-rdp-sidebar-actions .button-vue {
    flex: 1;
    justify-content: center;
}
</style>
