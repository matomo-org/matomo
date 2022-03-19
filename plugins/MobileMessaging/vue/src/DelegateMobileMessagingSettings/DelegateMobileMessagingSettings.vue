<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<todo>
- test in UI
- create PR
</todo>

<template>
  <div>
    <div>
      <Field
        uicontrol="radio"
        name="delegatedManagement"
        :value="delegatedManagement ? 1 : 0"
        :title="translate('MobileMessaging_Settings_LetUsersManageAPICredential')"
        v-model="enabled"
        :full-width="true"
        :options="delegateManagementOptions"
      >
      </Field>
    </div>
    <SaveButton
      @confirm="save()"
      :saving="isLoading"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  translate,
  Matomo,
  NotificationsStore,
} from 'CoreHome';
import { Field, SaveButton } from 'CorePluginsAdmin';

interface DelegateMobileMessagingSettingsState {
  isLoading: boolean;
  enabled: boolean|string|number;
}

export default defineComponent({
  props: {
    delegateManagementOptions: {
      type: Array,
      required: true,
    },
    delegatedManagement: [Number, Boolean],
  },
  components: {
    Field,
    SaveButton,
  },
  data(): DelegateMobileMessagingSettingsState {
    return {
      isLoading: false,
      enabled: false,
    };
  },
  methods: {
    save() {
      this.isLoading = true;
      AjaxHelper.post(
        {
          method: 'MobileMessaging.setDelegatedManagement',
        },
        {
          delegatedManagement: this.enabled && this.enabled !== '0' ? 'true' : 'false',
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          id: 'mobileMessagingSettings',
          type: 'transient',
          context: 'success',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
        Matomo.helper.redirect();
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
});
</script>
