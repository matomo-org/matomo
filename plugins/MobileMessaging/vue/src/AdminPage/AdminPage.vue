<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="manageMobileMessagingSettings">
    <ContentBlock
      v-if="isSuperUser"
      :content-title="translate('MobileMessaging_SettingsMenu')"
    >
      <DelegateMobileMessagingSettings
        :delegate-management-options="delegateManagementOptions"
        :delegated-management="delegatedManagement"
      />
    </ContentBlock>

    <ContentBlock
      v-if="accountManagedByCurrentUser"
      :content-title="translate('MobileMessaging_Settings_SMSProvider')"
      feature="true"
    >
      <p v-if="isSuperUser && delegatedManagement">
        {{ translate('MobileMessaging_Settings_DelegatedSmsProviderOnlyAppliesToYou') }}
      </p>

      <ManageSmsProvider
        :credential-supplied="credentialSupplied"
        :credential-error="credentialError"
        :provider="provider"
        :credit-left="creditLeft"
        :sms-provider-options="smsProviderOptions"
        :sms-providers="smsProviders"
      />
    </ContentBlock>

    <ContentBlock :content-title="translate('MobileMessaging_PhoneNumbers')">
      <p v-if="!credentialSupplied">
        {{ accountManagedByCurrentUser
          ? translate('MobileMessaging_Settings_CredentialNotProvided')
          : translate('MobileMessaging_Settings_CredentialNotProvidedByAdmin') }}
      </p>
      <ManageMobilePhoneNumbers
        v-else
        :is-super-user="isSuperUser"
        :default-calling-code="defaultCallingCode"
        :countries="countries"
        :str-help-add-phone="strHelpAddPhone"
        :phone-numbers="phoneNumbers"
      />
    </ContentBlock>

    <div class='ui-confirm' id='confirmDeleteAccount'>
      <h2>{{ translate('MobileMessaging_Settings_DeleteAccountConfirm') }}</h2>
      <input role='yes' type='button' :value="translate('General_Yes')"/>
      <input role='no' type='button' :value="translate('General_No')"/>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock } from 'CoreHome';
import DelegateMobileMessagingSettings
  from '../DelegateMobileMessagingSettings/DelegateMobileMessagingSettings.vue';
import ManageMobilePhoneNumbers from '../ManageMobilePhoneNumbers/ManageMobilePhoneNumbers.vue';
import ManageSmsProvider from '../ManageSmsProvider/ManageSmsProvider.vue';

export default defineComponent({
  props: {
    delegateManagementOptions: {
      type: Array,
      required: true,
    },
    delegatedManagement: [Number, Boolean],
    isSuperUser: Boolean,
    defaultCallingCode: String,
    countries: {
      type: Array,
      required: true,
    },
    strHelpAddPhone: {
      type: String,
      required: true,
    },
    phoneNumbers: Object,
    accountManagedByCurrentUser: Boolean,
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
    ContentBlock,
    DelegateMobileMessagingSettings,
    ManageMobilePhoneNumbers,
    ManageSmsProvider,
  },
});
</script>
