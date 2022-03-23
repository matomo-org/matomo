<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<todo>
- test in UI
</todo>

<template>
  <div v-form>
    <div>
      <Field
        uicontrol="radio"
        name="doNotTrack"
        v-model="enabled"
        :options="doNotTrackOptions"
        :inline-help="translate('PrivacyManager_DoNotTrack_Description')"
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
import { translate, AjaxHelper, NotificationsStore } from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';

interface DoNotTrackPreferenceState {
  isLoading: boolean;
  enabled: number;
}

export default defineComponent({
  props: {
    isSuperUser: Boolean,
    dntSupport: Boolean,
    doNotTrackOptions: {
      type: Array,
      required: true,
    },
  },
  components: {
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): DoNotTrackPreferenceState {
    return {
      isLoading: false,
      enabled: this.dntSupport ? 1 : 0,
    };
  },
  methods: {
    save() {
      this.isLoading = true;

      let action = 'deactivateDoNotTrack';
      if (this.enabled === 1) {
        action = 'activateDoNotTrack';
      }

      AjaxHelper.post({
        module: 'API',
        method: `PrivacyManager.${action}`,
      }).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('CoreAdminHome_SettingsSaveSuccess'),
          context: 'success',
          id: 'privacyManagerSettings',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
      }).finally(() => {
        this.isLoading = false;
      });
    },
  },
});
</script>
