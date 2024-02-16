<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="modal" id="trialRequiresLicense">
    <p class="btn-close modal-close"><i class="icon-close"></i></p>
    <div class="modal-content">
      <div class="modal-text">
        <h2>Start your free trial today</h2>
        <p>To unlock the full potential of our premium plugins,
          <a
            :href="linkTo({'module':'Marketplace', 'action':'manageLicenseKey'})"
          >add your license key</a>.</p>
        <p>
          <strong>Don't have a license key yet?</strong>
          Visit our <a href="https://shop.matomo.org/my-account/"
                       rel="noopener noreferrer" target="_blank">online marketplace</a>,
          create an account and start a free trial to get your license key.
          Once you have a license key, you will be able to start any free trials from here.</p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  MatomoUrl,
  NotificationsStore,
  translate,
} from 'CoreHome';

const { $ } = window;

export default defineComponent({
  props: {
    modelValue: {
      type: String,
      required: true,
    },
    isValidConsumer: Boolean,
  },
  emits: ['update:modelValue', 'trialStarted'],
  watch: {
    modelValue(newValue) {
      if (newValue) {
        if (this.isValidConsumer) {
          this.startFreeTrial();
        } else {
          this.showLicenseDialog();
        }
      }
    },
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
    startFreeTrial() {
      const pluginName = this.modelValue;

      window.Piwik_Popover.showLoading('');

      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.startFreeTrial',
        },
        { pluginName },
      ).then(() => {
        window.Piwik_Popover.close();

        const notificationInstanceId = NotificationsStore.show({
          message: translate(
            'CorePluginsAdmin_PluginFreeTrialStarted',
            '<strong>',
            '</strong>',
            pluginName,
          ),
          context: 'success',
          id: 'startTrialSuccess',
          type: 'transient',
        });
        NotificationsStore.scrollToNotification(notificationInstanceId);
        this.$emit('trialStarted');
      }).catch((error) => {
        window.Piwik_Popover.showError('', error.message);
      }).finally(() => this.$emit('update:modelValue', ''));
    },
    showLicenseDialog() {
      $('#trialRequiresLicense').modal({
        dismissible: true,
        onCloseEnd: () => {
          this.$emit('update:modelValue', '');
        },
      }).modal('open');
    },
  },
});
</script>
