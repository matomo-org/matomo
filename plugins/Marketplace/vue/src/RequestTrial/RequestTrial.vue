<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="ui-confirm" ref="confirm">
    <h2>{{ translate('Marketplace_RequestTrialConfirmTitle', plugin?.displayName) }}</h2>
    <p>{{ translate('Marketplace_RequestTrialConfirmEmailWarning') }}</p>
    <input role="yes" type="button" :value="translate('General_Yes')"/>
    <input role="no" type="button" :value="translate('General_No')"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import { AjaxHelper, NotificationsStore, translate } from 'CoreHome';
import Matomo from '../../../../CoreHome/vue/src/Matomo/Matomo';
import { PluginDetails } from '../types';

export default defineComponent({
  props: {
    modelValue: {
      type: Object,
      default: () => ({}),
    },
  },
  emits: ['update:modelValue', 'trialRequested'],
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }

      Matomo.helper.modalConfirm(
        this.$refs.confirm as HTMLElement,
        {
          yes: () => {
            this.requestTrial();
          },
        },
        {
          onCloseEnd: () => {
            this.$emit('update:modelValue', null);
          },
        },
      );
    },
  },
  computed: {
    plugin(): PluginDetails {
      return this.modelValue as PluginDetails;
    },
  },
  methods: {
    requestTrial() {
      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.requestTrial',
        },
        { pluginName: this.plugin.name, pluginDisplayName: this.plugin.displayName },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate(
            'Marketplace_RequestTrialSubmitted',
            this.plugin.displayName,
          ),
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
