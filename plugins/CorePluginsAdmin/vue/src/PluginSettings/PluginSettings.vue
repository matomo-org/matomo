<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="pluginSettings" ref="root">
    <div
      class="card"
      v-for="settings in settingsPerPlugin"
      :id="`${settings.pluginName}PluginSettings`"
      :key="`${settings.pluginName}PluginSettings`"
    >
      <div class="card-content">
        <h2
          class="card-title"
          :id="settings.pluginName"
        >{{ settings.title }}</h2>
        <GroupedSettings
          :group-name="settings.pluginName"
          :settings="settings.settings"
          :all-setting-values="settingValues"
          @change="settingValues[`${settings.pluginName}.${$event.name}`] = $event.value"
        />
        <input
          type="button"
          @click="saveSetting(settings.pluginName)"
          :disabled="isLoading"
          class="pluginsSettingsSubmit btn"
          :value="translate('General_Save')"
        />
        <ActivityIndicator
          :loading="isLoading || isSaving[settings.pluginName]"
        />
      </div>
    </div>
    <PasswordConfirmation
      v-model="showPasswordConfirmModal"
      @confirmed="confirmPassword"
    >
      <h2>{{ translate('UsersManager_ConfirmWithPassword') }}</h2>
    </PasswordConfirmation>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ActivityIndicator,
  AjaxHelper,
  NotificationsStore,
  translate,
} from 'CoreHome';
import Setting from './Setting';
import SettingsForSinglePlugin from './SettingsForSinglePlugin';
import GroupedSettings from '../GroupedSettings/GroupedSettings.vue';
import PasswordConfirmation from '../PasswordConfirmation/PasswordConfirmation.vue';

const { $ } = window;

interface PluginSettingsState {
  isLoading: boolean;
  isSaving: Record<string, boolean>;
  showPasswordConfirmModal: boolean;
  settingsToSave: null|string; // name of plugin whose settings to save
  settingsPerPlugin: SettingsForSinglePlugin[];
  settingValues: Record<string, unknown>;
}

export default defineComponent({
  props: {
    mode: String,
  },
  components: {
    PasswordConfirmation,
    ActivityIndicator,
    GroupedSettings,
  },
  data(): PluginSettingsState {
    return {
      isLoading: true,
      isSaving: {},
      showPasswordConfirmModal: false,
      settingsToSave: null,
      settingsPerPlugin: [],
      settingValues: {},
    };
  },
  created() {
    AjaxHelper.fetch({
      method: this.apiMethod,
    }).then((settingsPerPlugin: SettingsForSinglePlugin[]) => {
      this.isLoading = false;
      this.settingsPerPlugin = settingsPerPlugin;

      settingsPerPlugin.forEach((settings) => {
        settings.settings.forEach((setting) => {
          this.settingValues[`${settings.pluginName}.${setting.name}`] = setting.value;
        });
      });

      window.anchorLinkFix.scrollToAnchorInUrl();

      this.addSectionsToTableOfContents();
    }).catch(() => {
      this.isLoading = false;
    });
  },
  computed: {
    apiMethod(): string {
      return this.mode === 'admin'
        ? 'CorePluginsAdmin.getSystemSettings'
        : 'CorePluginsAdmin.getUserSettings';
    },
    saveApiMethod(): string {
      return this.mode === 'admin'
        ? 'CorePluginsAdmin.setSystemSettings'
        : 'CorePluginsAdmin.setUserSettings';
    },
  },
  methods: {
    addSectionsToTableOfContents() {
      const $toc = $('#generalSettingsTOC');
      if (!$toc.length) {
        return;
      }

      const settingsPerPlugin = this.settingsPerPlugin as SettingsForSinglePlugin[];
      settingsPerPlugin.forEach((settingsForPlugin) => {
        const { pluginName, settings } = settingsForPlugin;
        if (!pluginName) {
          return;
        }

        if (pluginName === 'CoreAdminHome' && settings) {
          settings.filter((s) => s.introduction).forEach((s) => {
            $toc.append(`<a href="#/${pluginName}PluginSettings">${s.introduction}</a> `);
          });
        } else {
          $toc.append(`<a href="#/${pluginName}">${pluginName.replace(/([A-Z])/g, ' $1').trim()}</a> `);
        }
      });
    },
    confirmPassword(password: string) {
      this.showPasswordConfirmModal = false;
      this.save(this.settingsToSave as string, password);
    },
    saveSetting(requestedPlugin: string) {
      if (this.mode === 'admin') {
        this.settingsToSave = requestedPlugin;
        this.showPasswordConfirmModal = true;
      } else {
        this.save(requestedPlugin);
      }
    },
    save(requestedPlugin: string, password?: string) {
      const { saveApiMethod } = this;

      this.isSaving[requestedPlugin] = true;

      const settingValuesPayload = this.getValuesForPlugin(requestedPlugin);

      AjaxHelper.post(
        { method: saveApiMethod },
        { settingValues: settingValuesPayload, passwordConfirmation: password },
      ).then(() => {
        this.isSaving[requestedPlugin] = false;

        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_PluginSettingsSaveSuccess'),
          id: 'generalSettings',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).catch(() => {
        this.isSaving[requestedPlugin] = false;
      });

      this.settingsToSave = null;
    },
    getValuesForPlugin(requestedPlugin: string) {
      const values: Record<string, Setting[]> = {};
      if (!values[requestedPlugin]) {
        values[requestedPlugin] = [];
      }

      Object.entries(this.settingValues).forEach(([key, value]) => {
        const [pluginName, settingName] = key.split('.');
        if (pluginName !== requestedPlugin) {
          return;
        }

        let postValue = value;
        if (postValue === false) {
          postValue = '0';
        } else if (postValue === true) {
          postValue = '1';
        }

        values[pluginName].push({
          name: settingName,
          value: postValue,
        });
      });

      return values;
    },
  },
});
</script>
