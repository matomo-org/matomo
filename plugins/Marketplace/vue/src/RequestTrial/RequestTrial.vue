<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="ui-confirm" ref="confirm">
    <h2>{{ translate('Marketplace_RequestTrialConfirmTitle', modelValue) }}</h2>
    <p>{{ translate('Marketplace_RequestTrialConfirmEmailWarning') }}</p>
    <input role="yes" type="button" :value="translate('General_Yes')"/>
    <input role="no" type="button" :value="translate('General_No')"/>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

import { AjaxHelper, NotificationsStore, translate } from 'CoreHome';
import Matomo from '../../../../CoreHome/vue/src/Matomo/Matomo';

export default defineComponent({
  props: {
    modelValue: {
      type: String,
      required: true,
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
            this.requestTrial(this.modelValue);
          },
        },
        {
          onCloseEnd: () => {
            this.$emit('update:modelValue', '');
          },
        },
      );
    },
  },
  methods: {
    requestTrial(pluginName: string) {
      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.requestTrial',
        },
        { pluginName },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate(
            'Marketplace_RequestTrialSubmitted',
            pluginName,
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
