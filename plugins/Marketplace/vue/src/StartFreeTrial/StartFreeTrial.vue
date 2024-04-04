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
          <h2>{{ translate('Marketplace_TrialStartNoLicenseTitle') }}</h2>
          <p>{{ translate('Marketplace_TrialStartNoLicenseText') }}</p>
          <Field
              uicontrol="text"
              name="email"
              v-model="createAccountEmail"
              :full-width="true"
              :title="translate('UsersManager_Email')"
          />

          <p class="trial-start-legal-hint"
             v-html="$sanitize(trialStartNoLicenseLegalHintText)"
          />

          <p>
            <button
                class="btn"
                :disabled="!createAccountEmail"
                @click="createAccountAndStartFreeTrial()"
            >{{ translate('Marketplace_TrialStartNoLicenseCreateAccount' )}}</button>
          </p>
          <p v-html="$sanitize(trialStartNoLicenseAddHereText)" />
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  AjaxHelper,
  externalLink,
  MatomoUrl,
  NotificationsStore,
  translate,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

const { $ } = window;

interface StartFreeTrialState {
  createAccountEmail: string;
  trialStartError: string | null;
  trialStartInProgress: boolean;
  trialStartSuccessNotificationMessage: string;
  trialStartSuccessNotificationTitle: string;
}

export default defineComponent({
  components: { Field },
  props: {
    modelValue: {
      type: String,
      required: true,
    },
    currentUserEmail: String,
    isValidConsumer: Boolean,
  },
  data(): StartFreeTrialState {
    return {
      createAccountEmail: this.currentUserEmail || '',
      trialStartError: null,
      trialStartInProgress: false,
      trialStartSuccessNotificationMessage: '',
      trialStartSuccessNotificationTitle: '',
    };
  },
  emits: ['update:modelValue', 'trialStarted'],
  watch: {
    modelValue(newValue) {
      if (!newValue) {
        return;
      }

      if (this.isValidConsumer) {
        this.trialStartSuccessNotificationMessage = translate(
          'CorePluginsAdmin_PluginFreeTrialStarted',
          '<strong>',
          '</strong>',
          newValue,
        );

        this.startFreeTrial();
      } else {
        this.trialStartSuccessNotificationTitle = translate(
          'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle',
        );

        this.trialStartSuccessNotificationMessage = translate(
          'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage',
          newValue,
        );

        this.showLicenseDialog();
      }
    },
  },
  computed: {
    trialStartNoLicenseAddHereText() {
      const link = `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey',
      })}`;

      return translate(
        'Marketplace_TrialStartNoLicenseAddHere',
        `<a href="${link}" target="_blank" rel="noreferrer noopener">`,
        '</a>',
      );
    },
    trialStartNoLicenseLegalHintText() {
      return translate(
        'Marketplace_TrialStartNoLicenseLegalHint',
        externalLink('https://shop.matomo.org/terms-conditions/'),
        '</a>',
        externalLink('https://matomo.org/privacy-policy/'),
        '</a>',
      );
    },
  },
  methods: {
    createAccountAndStartFreeTrial() {
      if (!this.createAccountEmail) {
        return;
      }

      this.showLoadingModal();

      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.createAccount',
        },
        {
          email: this.createAccountEmail,
        },
        {
          createErrorNotification: false,
        },
      ).then(() => {
        this.startFreeTrial();
      }).catch((error) => {
        this.showErrorModal(error.message);

        this.trialStartInProgress = false;

        this.$emit('update:modelValue', '');
      });
    },
    showLicenseDialog() {
      $('#startFreeTrial').modal({
        dismissible: true,
        onCloseEnd: () => {
          if (this.trialStartInProgress) {
            return;
          }

          this.$emit('update:modelValue', '');
        },
      }).modal('open');
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

      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.startFreeTrial',
        },
        {
          pluginName: this.modelValue,
        },
        {
          createErrorNotification: false,
        },
      ).then(() => {
        const notificationInstanceId = NotificationsStore.show({
          message: this.trialStartSuccessNotificationMessage,
          title: this.trialStartSuccessNotificationTitle,
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
