<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="ui-confirm requestPlugin" ref="confirm">
    <button
      type="button"
      class="btn-close modal-close"
      :aria-label="translate('General_Close')"
    ><span class="icon-close"></span></button>
    <h2 class="requestPlugin__title">{{ translate(
      isInstallRequest ? 'Marketplace_RequestInstallTitle' : 'Marketplace_RequestTrialTitle',
      plugin?.displayName,
    ) }}</h2>
    <p class="requestPlugin__intro">{{ translate(
      isInstallRequest ? 'Marketplace_RequestInstallIntro' : 'Marketplace_RequestTrialIntro',
    ) }}</p>
    <div class="requestPlugin__message">
      <label class="requestPlugin__label" for="requestPluginMessage">{{ translate('Marketplace_RequestMessageLabel') }}</label>
      <textarea
        id="requestPluginMessage"
        class="materialize-textarea requestPlugin__messageInput"
        maxlength="1000"
        :placeholder="translate('Marketplace_RequestMessagePlaceholder')"
        v-model="message"
      />
    </div>
    <input role="no" type="button" :value="translate('General_Cancel')"/>
    <!-- a filled button rather than a footer link, which leaves closing the dialog to us -->
    <input role="validation" type="button" :value="translate('Marketplace_SendRequest')"/>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';

import {
  AjaxHelper,
  Matomo,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { PluginCard } from '../types';
import manageRequestsUrl from '../ManageRequests/manageRequestsUrl';

export default defineComponent({
  props: {
    modelValue: {
      type: Object as PropType<PluginCard | null>,
      default: () => null,
    },
  },
  emits: ['update:modelValue', 'trialRequested'],
  data() {
    return {
      message: '',
    };
  },
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }

      this.message = '';

      Matomo.helper.modalConfirm(
        this.$refs.confirm as HTMLElement,
        {
          validation: () => {
            $(this.$refs.confirm as HTMLElement).closest('.modal').modal('close');
            this.requestTrial(newValue);
          },
        },
        {
          onCloseEnd: () => {
            this.$emit('update:modelValue', null);
          },
          // the message box rather than the footer's last button, so typing does not end up there
          focusSelector: '#requestPluginMessage',
          classes: 'requestPluginModal',
        },
      );
    },
  },
  computed: {
    plugin(): PluginCard {
      return this.modelValue as PluginCard;
    },
    /** The card offers an install to request rather than a trial. */
    isInstallRequest(): boolean {
      return !!this.plugin?.canInstallBeRequested;
    },
  },
  methods: {
    requestTrial(plugin: PluginCard) {
      AjaxHelper.post(
        {
          module: 'API',
          method: this.isInstallRequest ? 'Marketplace.requestInstall' : 'Marketplace.requestTrial',
        },
        { pluginName: plugin.name, message: this.message },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate('Marketplace_PluginRequestSent', `<a href="${manageRequestsUrl()}">`, '</a>'),
          context: 'success',
          id: 'requestTrialSuccess',
          placeat: '#notificationContainer',
          type: 'transient',
        });

        NotificationsStore.scrollToNotification(notificationInstanceId);

        this.$emit('trialRequested');
      });
    },
  },
});
</script>
