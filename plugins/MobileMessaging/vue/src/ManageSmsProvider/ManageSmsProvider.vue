<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

// TODO
<todo>
- conversion check (mistakes get fixed in quickmigrate)
- property types
- state types
- look over template
- look over component code
- get to build
- REMOVE DUPLICATE CODE IN TEMPLATE
- test in UI
- check uses:
  ./plugins/MobileMessaging/templates/macros.twig
  ./plugins/MobileMessaging/angularjs/manage-sms-provider.controller.js
- create PR
</todo>

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
      <span v-html="updateOrDeleteAccountText"></span>
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
      <div
        sms-provider-credentials
        provider="manageProvider.smsProvider"
        value="{}"
        ng-init="manageProvider.isUpdateAccountPossible()"
        :model-value="credentials"
        @update:model-value="credentials = $event; isUpdateAccountPossible()"
      />
      <SaveButton
        id="apiAccountSubmit"
        :disabled="!canBeUpdated"
        :saving="isUpdatingAccount"
        @confirm="updateAccount()"
      />
      {% for smsProvider, description in smsProviders %}
      <div
        class="providerDescription"
        v-show="`manageProvider.smsProvider == "
        ${smsProvider}"`"
        :id="smsProvider"
      >
        {{ raw(description) }}
      </div>
      {% endfor %}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  AjaxHelper,
  Matomo,
  ActivityIndicator
} from 'CoreHome';
import { Form, Field, SaveButton } from 'CorePluginsAdmin';


interface ManageSmsProviderState {
  isDeletingAccount: boolean;
  isUpdatingAccount: boolean;
  showAccountForm: boolean;
  isUpdateAccountPossible: boolean;
  credentials: string;
}

export default defineComponent({
  props: {
    credentialSupplied: {
      type: null, // TODO
      required: true,
    },
    credentialError: {
      type: null, // TODO
      required: true,
    },
    provider: {
      type: null, // TODO
      required: true,
    },
    creditLeft: {
      type: null, // TODO
      required: true,
    },
    smsProviderOptions: {
      type: null, // TODO
      required: true,
    },
    smsProviders: {
      type: null, // TODO
      required: true,
    },
    smsProvider: {
      type: null, // TODO
      required: true,
    },
    description: {
      type: null, // TODO
      required: true,
    },
  },
  components: {
    ActivityIndicator,
    Field,
    SaveButton,
  },
  directives: {
    Form,
  },
  data(): ManageSmsProviderState {
    return {
      isDeletingAccount: false,
      isUpdatingAccount: false,
      showAccountForm: false,
      isUpdateAccountPossible: false,
      credentials: '{}',
    };
  },
  methods: {
    // TODO
    deleteApiAccount() {
      this.isDeletingAccount = true;
      AjaxHelper.fetch({
        method: 'MobileMessaging.deleteSMSAPICredential'
      }, {
        placeat: '#ajaxErrorManageSmsProviderSettings'
      }).then(() => {
        this.isDeletingAccount = false;
        Matomo.helper.redirect();
      }, () => {
        this.isDeletingAccount = false;
      });
    },
    // TODO
    showUpdateAccount() {
      this.showAccountForm = true;
    },
    // TODO
    updateAccount() {
      if (this.isUpdateAccountPossible()) {
        this.isUpdatingAccount = true;
        AjaxHelper.post({
          method: 'MobileMessaging.setSMSAPICredential'
        }, {
          provider: this.smsProvider,
          credentials: angular.fromJson(this.credentials)
        }, {
          placeat: '#ajaxErrorManageSmsProviderSettings'
        }).then(() => {
          this.isUpdatingAccount = false;
          Matomo.helper.redirect();
        }, () => {
          this.isUpdatingAccount = false;
        });
      }
    },
    // TODO
    deleteAccount() {
      piwikHelper.modalConfirm('#confirmDeleteAccount', {
        yes: deleteApiAccount
      });
    },
  },
  computed: {
    // TODO
    isUpdateAccountPossible() {
      const this = this;
      this.canBeUpdated = !!this.smsProvider;
      const credentials = angular.fromJson(this.credentials);
      angular.forEach(this.credentials, (value, key) => {
        if (value == '') {
          this.canBeUpdated = false;
        }
      });
      return this.canBeUpdated;
    },
    updateOrDeleteAccountText() {
      return translate(
        'MobileMessaging_Settings_UpdateOrDeleteAccount',
        '<a id="displayAccountForm" @click="showUpdateAccount()">',
        '</a>',
        '<a id="deleteAccount" @click="deleteAccount()">',
        '</a>',
      );
    },
  },
});
</script>
