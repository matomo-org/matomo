<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
    <p>{{ translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1') }} <a
        target="_blank"
        rel="noreferrer noopener"
        href="https://github.com/andOTP/andOTP#downloads"
      >andOTP</a>, <a
        target="_blank"
        rel="noreferrer noopener"
        href="https://authy.com/guides/github/"
      >Authy</a>, <a
        target="_blank"
        rel="noreferrer noopener"
        href="https://support.1password.com/one-time-passwords/"
      >1Password</a>, <a
        target="_blank"
        rel="noreferrer noopener"
        href="https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
      >LastPass Authenticator</a>, {{ translate('General_Or') }} <a
        target="_blank"
        rel="noreferrer noopener"
        href="https://support.google.com/accounts/answer/1066447"
      >Google Authenticator</a>.
    </p>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({});
</script>
