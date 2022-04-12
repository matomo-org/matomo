<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <ActivityIndicator :loading="isDeletingAccount" />
    <div id="ajaxErrorManageSmsProviderSettings" />
    <p v-if="credentialSupplied">
      <span v-if="credentialError">
        {{ translate('MobileMessaging_Settings_CredentialInvalid', provider) }}<br />
        {{ credentialError }}
      </span>
      <span v-else>
        {{ translate('MobileMessaging_Settings_CredentialProvided', provider) }}
        {{ creditLeft }}
      </span>
      <br />
      <span
        v-html="$sanitize(updateOrDeleteAccountText)"
        @click="onUpdateOrDeleteClick($event)"
      />
    </p>
    <p v-else>{{ translate('MobileMessaging_Settings_PleaseSignUp') }}</p>
    <div
      id="accountForm"
      v-show="!credentialSupplied || showAccountForm"
      v-form
    >
      <div>
        <Field
          uicontrol="select"
          name="smsProviders"
          v-model="smsProvider"
          :title="translate('MobileMessaging_Settings_SMSProvider')"
          :options="smsProviderOptions"
          :value="provider"
        >
        </Field>
      </div>
      <SmsProviderCredentials
        :provider="smsProvider"
        v-model="credentials"
        :model-value="credentials"
        @update:model-value="credentials = $event;"
      />
      <SaveButton
        id="apiAccountSubmit"
        :disabled="!isUpdateAccountPossible"
        :saving="isUpdatingAccount"
        @confirm="updateAccount()"
      />
      <div
        class="providerDescription"
        v-html="$sanitize(currentProviderDescription)"
      >
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  ActivityIndicator,
} from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';
import SmsProviderCredentials from '../SmsProviderCredentials/SmsProviderCredentials';

interface ManageSmsProviderState {
  isDeletingAccount: boolean;
  isUpdatingAccount: boolean;
  showAccountForm: boolean;
  credentials: Record<string, unknown>|null;
  smsProvider?: string;
}

export default defineComponent({
  props: {
    credentialSupplied: Boolean,
    credentialError: String,
    provider: String,
    creditLeft: [Number, String],
    smsProviderOptions: {
      type: Object,
      required: true,
    },
    smsProviders: {
      type: Object,
      required: true,
    },
  },
  components: {
    ActivityIndicator,
    Field,
    SaveButton,
    SmsProviderCredentials,
  },
  directives: {
    Form,
  },
  data(): ManageSmsProviderState {
    return {
      isDeletingAccount: false,
      isUpdatingAccount: false,
      showAccountForm: false,
      credentials: null,
      smsProvider: this.provider,
    };
  },
  methods: {
    deleteApiAccount() {
      this.isDeletingAccount = true;
      AjaxHelper.fetch(
        {
          method: 'MobileMessaging.deleteSMSAPICredential',
        },
        {
          errorElement: '#ajaxErrorManageSmsProviderSettings',
        },
      ).then(() => {
        Matomo.helper.redirect();
      }).finally(() => {
        this.isDeletingAccount = false;
      });
    },
    showUpdateAccount() {
      this.showAccountForm = true;
    },
    updateAccount() {
      if (this.isUpdateAccountPossible) {
        this.isUpdatingAccount = true;
        AjaxHelper.post(
          {
            method: 'MobileMessaging.setSMSAPICredential',
          },
          {
            provider: this.smsProvider,
            credentials: this.credentials,
          },
          {
            errorElement: '#ajaxErrorManageSmsProviderSettings',
          },
        ).then(() => {
          Matomo.helper.redirect();
        }).finally(() => {
          this.isUpdatingAccount = false;
        });
      }
    },
    deleteAccount() {
      Matomo.helper.modalConfirm('#confirmDeleteAccount', {
        yes: () => {
          this.isDeletingAccount = true;

          AjaxHelper.fetch(
            {
              method: 'MobileMessaging.deleteSMSAPICredential',
            },
            {
              errorElement: '#ajaxErrorManageSmsProviderSettings',
            },
          ).then(() => {
            this.isDeletingAccount = false;
            Matomo.helper.redirect();
          }).finally(() => {
            this.isDeletingAccount = false;
          });
        },
      });
    },
    onUpdateOrDeleteClick(event: Event) {
      const target = event.target as HTMLElement;
      if (target.id === 'displayAccountForm') {
        this.showUpdateAccount();
      } else if (target.id === 'deleteAccount') {
        this.deleteAccount();
      }
    },
  },
  computed: {
    isUpdateAccountPossible() {
      // possible if smsProvider is set and all credential field values are set to something
      return !!this.smsProvider
        && this.credentials !== null
        && Object.values(this.credentials as Record<string, string>).every((v) => !!v);
    },
    updateOrDeleteAccountText() {
      return translate(
        'MobileMessaging_Settings_UpdateOrDeleteAccount',
        '<a id="displayAccountForm">',
        '</a>',
        '<a id="deleteAccount">',
        '</a>',
      );
    },
    currentProviderDescription() {
      if (!this.smsProvider || !this.smsProviders) {
        return '';
      }

      return this.smsProviders[this.smsProvider];
    },
  },
});
</script>
