<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="modal" id="startFreeTrial">
    <p v-if="!trialStartInProgress" class="btn-close modal-close"><i class="icon-close"></i></p>

    <template v-if="trialStartInProgress">
      <div class="modal-content trial-start-in-progress">
        <div class="Piwik_Popover_Loading">
          <div class="Piwik_Popover_Loading_Name">
            <h2>{{ translate('Marketplace_TrialStartInProgressTitle') }}</h2>
            <p>{{ translate('Marketplace_TrialStartInProgressText') }}</p>
          </div>
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

          <div class="alert alert-danger"
               v-if="createAccountError"
               v-html="$sanitize(createAccountError)"
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
          <p class="add-existing-license" v-html="$sanitize(trialStartNoLicenseAddHereText)" />
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
import Matomo from '../../../../CoreHome/vue/src/Matomo/Matomo';
import KeyPressEvent = JQuery.KeyPressEvent;
import ModalOptions = M.ModalOptions;
import { PluginDetails } from '../types';

const { $ } = window;

interface StartFreeTrialState {
  createAccountEmail: string;
  createAccountError: string | null;
  trialStartError: string | null;
  trialStartInProgress: boolean;
  trialStartSuccessNotificationMessage: string;
  trialStartSuccessNotificationTitle: string;
  loadingModalCloseCallback: undefined | (() => void);
}

export default defineComponent({
  components: { Field },
  props: {
    modelValue: {
      type: Object,
      default: () => ({}),
    },
    currentUserEmail: String,
    isValidConsumer: Boolean,
  },
  data(): StartFreeTrialState {
    return {
      createAccountEmail: this.currentUserEmail || '',
      createAccountError: null,
      trialStartError: null,
      loadingModalCloseCallback: undefined,
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
          this.plugin.displayName,
        );

        this.startFreeTrial();
      } else {
        this.trialStartSuccessNotificationTitle = translate(
          'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedTitle',
        );

        this.trialStartSuccessNotificationMessage = translate(
          'CorePluginsAdmin_PluginFreeTrialStartedAccountCreatedMessage',
          this.plugin.displayName,
        );

        this.showLicenseDialog(false);
      }
    },
  },
  computed: {
    plugin(): PluginDetails {
      return this.modelValue as PluginDetails;
    },
    trialStartNoLicenseAddHereText() {
      const link = `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'manageLicenseKey',
      })}`;

      return translate(
        'Marketplace_TrialStartNoLicenseAddHere',
        `<a href="${link}">`,
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
    closeModal() {
      $('#startFreeTrial').modal('close');
    },
    createAccountAndStartFreeTrial() {
      if (!this.createAccountEmail) {
        return;
      }

      this.showLoadingModal(true);

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
        if (error.message.startsWith('Marketplace_CreateAccountError')) {
          this.showErrorModal(translate(error.message));

          this.trialStartInProgress = false;

          this.$emit('update:modelValue', null);
        } else {
          this.createAccountError = error.message;

          this.trialStartInProgress = false;

          this.showLicenseDialog(true);
        }
      });
    },
    showLicenseDialog(immediateTransition: boolean) {
      const onEnter = (event: KeyPressEvent) => {
        const keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode === 13) {
          this.closeModal();
          this.createAccountAndStartFreeTrial();
        }
      };

      const modalOptions: ModalOptions = {
        dismissible: true,
        onOpenEnd: () => {
          const emailField = '.modal.open #email';
          $(emailField).focus();
          $(emailField).off('keypress').keypress(onEnter);
        },
        onCloseEnd: () => {
          this.createAccountError = null;

          if (this.trialStartInProgress) {
            return;
          }

          this.$emit('update:modelValue', null);
        },
      } as unknown as ModalOptions;

      if (immediateTransition) {
        modalOptions.inDuration = 0;
      }

      $('#startFreeTrial').modal(modalOptions).modal('open');
    },
    showErrorModal(error: string) {
      if (this.trialStartError) {
        return;
      }

      this.trialStartError = error;

      $('#startFreeTrial').modal({
        dismissible: true,
        inDuration: 0,
        onCloseEnd: () => {
          this.trialStartError = null;
        },
      }).modal('open');
    },
    showLoadingModal(immediateTransition: boolean) {
      if (this.trialStartInProgress) {
        return;
      }

      this.trialStartInProgress = true;
      this.loadingModalCloseCallback = undefined;

      $('#startFreeTrial').modal({
        dismissible: false,
        inDuration: immediateTransition ? 0 : undefined,
        onCloseEnd: () => {
          if (!this.loadingModalCloseCallback) {
            return;
          }

          this.loadingModalCloseCallback();

          this.loadingModalCloseCallback = undefined;
        },
      }).modal('open');
    },
    startFreeTrial() {
      this.showLoadingModal(false);

      AjaxHelper.post(
        {
          module: 'API',
          method: 'Marketplace.startFreeTrial',
        },
        {
          pluginName: this.plugin.name,
        },
        {
          createErrorNotification: false,
        },
      ).then(() => {
        this.loadingModalCloseCallback = this.startFreeTrialSuccess;

        this.closeModal();
      }).catch((error) => {
        this.showErrorModal(Matomo.helper.htmlDecode(error.message));

        this.trialStartInProgress = false;
      }).finally(() => {
        this.$emit('update:modelValue', null);
      });
    },
    startFreeTrialSuccess() {
      const notificationInstanceId = NotificationsStore.show({
        message: this.trialStartSuccessNotificationMessage,
        title: this.trialStartSuccessNotificationTitle,
        context: 'success',
        id: 'startTrialSuccess',
        placeat: '#notificationContainer',
        type: 'transient',
      });

      NotificationsStore.scrollToNotification(notificationInstanceId);

      this.trialStartInProgress = false;

      this.$emit('trialStarted');
    },
  },
});
</script>
