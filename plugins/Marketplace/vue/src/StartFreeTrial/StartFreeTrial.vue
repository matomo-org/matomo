<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="modal" id="startFreeTrial">
    <p v-if="!trialStartInProgress" class="btn-close modal-close"><i class="icon-close"></i></p>

    <template v-if="trialStartInProgress">
      <div class="modal-content trial-start-in-progress">
        <div class="modal-text">
          <div class="preloader-wrapper active">
            <div class="spinner-layer spinner-blue-only">
              <div class="circle-clipper left">
                <div class="circle"></div>
              </div>
              <div class="gap-patch">
                <div class="circle"></div>
              </div>
              <div class="circle-clipper right">
                <div class="circle"></div>
              </div>
            </div>
          </div>
          <h2>{{ translate('Marketplace_TrialStartInProgressTitle') }}</h2>
          <p>{{ translate('Marketplace_TrialStartInProgressText') }}</p>
        </div>
      </div>
    </template>

    <template v-else-if="trialStartError">
      <div class="modal-content trial-start-error">
        <div class="modal-text">
          <h2>{{ translate('Marketplace_TrialStartErrorTitle') }}</h2>
          <p>{{ trialStartError }}</p>
          <p>{{ translate('Marketplace_TrialStartErrorSupport') }}</p>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="modal-content trial-start-no-license">
        <div class="modal-text">
          <h2>Start your free trial today</h2>
          <p>To unlock the full potential of our premium plugins,
            <a
              :href="linkTo({'module':'Marketplace', 'action':'manageLicenseKey'})"
            >add your license key</a>.</p>
          <p>
            <strong>Don't have a license key yet?</strong>
            Visit our <a :href="externalRawLink('https://shop.matomo.org/my-account/')"
                         rel="noopener noreferrer" target="_blank">online marketplace</a>,
            create an account and start a free trial to get your license key.
            Once you have a license key, you will be able to start any free trials from here.</p>
        </div>
      </div>
    </template>
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

interface StartFreeTrialState {
  trialStartError: string | null;
  trialStartInProgress: boolean;
}

export default defineComponent({
  props: {
    modelValue: {
      type: String,
      required: true,
    },
    isValidConsumer: Boolean,
  },
  data(): StartFreeTrialState {
    return {
      trialStartError: null,
      trialStartInProgress: false,
    };
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
    showErrorModal(error: string) {
      if (this.trialStartError) {
        return;
      }

      this.trialStartError = error;

      $('#startFreeTrial').modal({
        dismissible: true,
        onCloseEnd: () => {
          this.trialStartError = null;
        },
      }).modal('open');
    },
    showLicenseDialog() {
      $('#startFreeTrial').modal({
        dismissible: true,
        onCloseEnd: () => {
          this.$emit('update:modelValue', '');
        },
      }).modal('open');
    },
    showLoadingModal() {
      if (this.trialStartInProgress) {
        return;
      }

      this.trialStartInProgress = true;

      $('#startFreeTrial').modal({
        dismissible: false,
        onCloseEnd: () => {
          this.trialStartInProgress = false;
        },
      }).modal('open');
    },
    startFreeTrial() {
      this.showLoadingModal();

      const pluginName = this.modelValue;

      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.startFreeTrial',
        },
        { pluginName },
        {
          createErrorNotification: false,
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: translate(
            'CorePluginsAdmin_PluginFreeTrialStarted',
            '<strong>',
            '</strong>',
            pluginName,
          ),
          context: 'success',
          id: 'startTrialSuccess',
          placeat: '#notificationContainer',
          type: 'transient',
        });

        NotificationsStore.scrollToNotification(notificationInstanceId);

        $('#startFreeTrial').modal('close');

        this.$emit('trialStarted');
      }).catch((error) => {
        this.showErrorModal(error.message);
      }).finally(() => {
        this.trialStartInProgress = false;

        this.$emit('update:modelValue', '');
      });
    },
  },
});
</script>
