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
        <div
          v-for="setting in settings.settings"
          :key="`${setting.pluginName}.${setting.name}`"
        >
          <div>
            <FormField
              v-model="settingValues[`${settings.pluginName}.${setting.name}`]"
              :form-field="{
                ...setting,
                condition: makeSettingConditionFunction(setting, settings.pluginName),
              }"
            />
          </div>
        </div>
        <input
          type="button"
          @click="save(settings.pluginName)"
          :disabled="isLoading"
          class="pluginsSettingsSubmit btn"
          :value="translate('General_Save')"
        />
        <ActivityIndicator
          :loading="isLoading || isSaving[settings.pluginName]"
        />
      </div>
    </div>
    <div class="confirm-password-modal modal">
      <div class="modal-content">
        <h2>{{ translate('UsersManager_ConfirmWithPassword') }}</h2>
        <div>
          <Field
            v-model="passwordConfirmation"
            :uicontrol="'password'"
            :name="'currentUserPassword'"
            :autocomplete="false"
            :full-width="true"
            :title="translate('UsersManager_YourCurrentPassword')"
          >
          </Field>
        </div>
      </div>
      <div class="modal-footer">
        <a
          href=""
          class="modal-action modal-close btn"
          :disabled="!passwordConfirmation ? 'disabled' : undefined"
          @click="save(this.settingsToSave)"
        >{{ translate('General_Yes') }}</a>
        <a
          href=""
          class="modal-action modal-close modal-no"
        >{{ translate('General_No') }}</a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { IScope } from 'angular';
import {
  ActivityIndicator,
  AjaxHelper,
  NotificationsStore,
  translate,
  Matomo,
} from 'CoreHome';
import FormField from '../FormField/FormField.vue';
import Field from '../Field/Field.vue';

const { $ } = window;

interface Setting {
  condition: string;
}

// TODO: have to use angularjs here until there's an expression evaluating alternative
let conditionScope: IScope;

export default defineComponent({
  props: {
    mode: String,
  },
  components: {
    FormField,
    ActivityIndicator,
    Field,
  },
  data() {
    return {
      isLoading: true,
      isSaving: {},
      passwordConfirmation: '',
      settingsToSave: null,
      settingsPerPlugin: [],
      settingValues: {},
    };
  },
  created() {
    AjaxHelper.fetch({ method: this.apiMethod }).then((settingsPerPlugin) => {
      this.isLoading = false;
      this.settingsPerPlugin = settingsPerPlugin;

      settingsPerPlugin.forEach((settings) => {
        settings.settings.forEach((setting) => {
          this.settingValues[`${settings.pluginName}.${setting.name}`] = setting.value;
        });
      });

      window.anchorLinkFix.scrollToAnchorInUrl();
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
    save(requestedPlugin: string) {
      const { saveApiMethod } = this;
      const { root } = this.$refs;

      const $root = $(root);

      if (this.mode === 'admin' && !this.passwordConfirmation) {
        this.settingsToSave = requestedPlugin;

        const onEnter = (event) => {
          const keycode = event.keyCode ? event.keyCode : event.which;
          if (keycode === '13') {
            $root.find('.confirm-password-modal').modal('close');
            this.save(requestedPlugin);
          }
        };

        $root.find('.confirm-password-modal').modal({
          dismissible: false,
          onOpenEnd: () => {
            $('.modal.open #currentUserPassword').focus();
            $('.modal.open #currentUserPassword').off('keypress').keypress(onEnter);
          },
        }).modal('open');

        return;
      }

      this.isSaving[requestedPlugin] = true;

      const settingValuesPayload = this.getValuesForPlugin(requestedPlugin);

      AjaxHelper.post(
        { method: saveApiMethod },
        { settingValues: settingValuesPayload, passwordConfirmation: this.passwordConfirmation },
      ).then(() => {
        this.isSaving[requestedPlugin] = false;

        NotificationsStore.show({
          message: translate('CoreAdminHome_PluginSettingsSaveSuccess'),
          id: 'generalSettings',
          context: 'success',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification('generalSettings');
      }).catch(() => {
        this.isSaving[requestedPlugin] = false;
      });

      this.passwordConfirmation = '';
      this.settingsToSave = null;
    },
    makeSettingConditionFunction(setting: Setting, pluginName: string) {
      const { condition } = setting;
      if (!condition) {
        return undefined;
      }

      return () => {
        if (!conditionScope) {
          const $rootScope = Matomo.helper.getAngularDependency('$rootScope');
          conditionScope = $rootScope.$new(true);
        }

        // TODO: this is definitely not as performant. would probably need a separate component
        // for a single plugin's settings so we can make this and other types of transforms
        // computed properties.
        const values = this.getConditionValuesForPlugin(pluginName);

        return conditionScope.$eval(condition, values);
      };
    },
    getConditionValuesForPlugin(requestedPlugin: string) {
      const values = {};
      Object.entries(this.settingValues).forEach(([key, value]) => {
        const [pluginName, settingName] = key.split('.');
        if (pluginName !== requestedPlugin) {
          return;
        }

        values[settingName] = value;
      });
      return values;
    },
    getValuesForPlugin(requestedPlugin: string) {
      const values = {};
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
