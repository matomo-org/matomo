<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<todo>
- get to build
- test in UI
- check uses:
  ./plugins/TwoFactorAuth/templates/_setupTwoFactorAuth.twig
  ./plugins/TwoFactorAuth/angularjs/setuptwofactor/setuptwofactor.controller.js
- create PR
</todo>

<template>
  <div class="setupTwoFactorAuthentication">
    <div class="alert alert-warning" v-if="isAlreadyUsing2fa">
      {{ translate('TwoFactorAuth_WarningChangingConfiguredDevice') }}
    </div>
    <p>
      {{ translate('TwoFactorAuth_SetupIntroFollowSteps') }}
    </p>
    <h2>
      {{ translate('TwoFactorAuth_StepX', 1) }} - {{ translate('TwoFactorAuth_RecoveryCodes') }}
    </h2>

    <p>{{ translate('TwoFactorAuth_RecoveryCodesExplanation') }}<br /><br /></p>
    <div class="alert alert-warning">{{ translate('TwoFactorAuth_RecoveryCodesSecurity') }}</div>

    <ul v-select-on-focus="{}" class="twoFactorRecoveryCodes browser-default" v-if="codes?.length">
      <li v-for="(code, index) in codes" :key="index">
        {{ code.toUpperCase().split('', 4).join('-') }}
      </li>
    </ul>
    <div class="alert alert-danger" v-else>
      {{ translate('TwoFactorAuth_RecoveryCodesAllUsed') }}
    </div>

    <p>
      <br />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="downloadRecoveryCodes(); this.hasDownloadedRecoveryCode = true;"
        :value="translate('General_Download')"
      />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="print(); this.hasDownloadedRecoveryCode = true;"
        :value="translate('General_Print')"
      />
      <input
        type="button"
        class="btn backupRecoveryCode"
        @click="copyRecoveryCodesToClipboard(); this.hasDownloadedRecoveryCode = true;"
        value="translate('General_Copy')"
      />
    </p>

    <div
      class="alert alert-info backupRecoveryCodesAlert"
      v-show="step === 1"
    >
      {{ translate('TwoFactorAuth_SetupBackupRecoveryCodes') }}
    </div>
    <p>
      <button
        class="btn goToStep2"
        v-show="step === 1"
        @click="nextStep()"
        :disabled="!hasDownloadedRecoveryCode"
      >{{ translate('General_Next') }}</button>
    </p>
    <a
      name="twoFactorStep2"
      id="twoFactorStep2"
      style="opacity: 0"
    />
    <div v-show="step >= 2">
      <h2>
        {{ translate('TwoFactorAuth_StepX', 2) }} -
        {{ translate('TwoFactorAuth_SetupAuthenticatorOnDevice') }}
      </h2>
      <p>{{ translate('TwoFactorAuth_SetupAuthenticatorOnDeviceStep1') }} <a
          rel="noreferrer noopener"
          href="https://github.com/andOTP/andOTP#downloads"
        >andOTP</a>, <a
          rel="noreferrer noopener"
          href="https://authy.com/guides/github/"
        >Authy</a>, <a
          rel="noreferrer noopener"
          href="https://support.1password.com/one-time-passwords/"
        >1Password</a>, <a
          rel="noreferrer noopener"
          href="https://helpdesk.lastpass.com/multifactor-authentication-options/lastpass-authenticator/"
        >LastPass Authenticator</a>, {{ translate('General_Or') }} <a
          rel="noreferrer noopener"
          href="https://support.google.com/accounts/answer/1066447"
        >Google Authenticator</a>.
      </p>
      <p>
        <span v-html="$sanitize(setupAuthenticatorOnDeviceStep2)"></span>
        <br />
        <span
          ref="qrcode"
          title
        />
        <br />
        <button
          class="btn goToStep3"
          v-show="step === 2"
          @click="nextStep()"
        >{{ translate('General_Next') }}</button>
      </p>
    </div>
    <a
      name="twoFactorStep3"
      id="twoFactorStep3"
      style="opacity: 0"
    />
    <div v-show="step >= 3">
      <h2>{{ translate('TwoFactorAuth_StepX', 3) }} - {{ translate('TwoFactorAuth_ConfirmSetup') }}
      </h2>
      <p>{{ translate('TwoFactorAuth_VerifyAuthCodeIntro') }}</p>
      <div class="message_container" v-if="accessErrorString">
        <div>
          <Notification
            :noclear="true"
            context="error"
          >
            <strong>
              {{ translate('General_Error') }}
            </strong>: <span v-html="$sanitize(accessErrorString)"/><br />
          </Notification>
        </div>
      </div>
      <form
        method="post"
        class="setupConfirmAuthCodeForm"
        autocorrect="off"
        autocapitalize="none"
        autocomplete="off"
        :action="linkTo({'module': 'TwoFactorAuth', 'action': submitAction})"
      >
        <div>
          <Field
            uicontrol="text"
            name="authCode"
            :title="translate('TwoFactorAuth_AuthenticationCode')"
            v-model="authCode"
            :maxlength="6"
            :placeholder="123456"
            :inline-help="translate('TwoFactorAuth_VerifyAuthCodeHelp')"
          >
          </Field>
        </div>
        <input
          type="hidden"
          name="authCodeNonce"
          :value="authCodeNonce"
        />
        <input
          type="submit"
          class="btn confirmAuthCode"
          :disabled="authCode.length !== 6"
          :value="translate('General_Confirm')"
        />
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  Notification,
  SelectOnFocus,
  MatomoUrl,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import '../types';

interface SetupTwoFactorAuthState {
  step: number;
  hasDownloadedRecoveryCode: boolean;
  authCode: string;
}

const { QRCode, $ } = window;

export default defineComponent({
  props: {
    isAlreadyUsing2fa: Boolean,
    accessErrorString: String,
    submitAction: {
      type: String,
      required: true,
    },
    authCodeNonce: {
      type: String,
      required: true,
    },
    codes: Array,
  },
  components: {
    Notification,
    Field,
  },
  directives: {
    SelectOnFocus,
  },
  data(): SetupTwoFactorAuthState {
    return {
      step: 1,
      hasDownloadedRecoveryCode: false,
      authCode: '',
    };
  },
  mounted() {
    setTimeout(() => {
      const qrcode = this.$refs.qrcode as HTMLElement;

      // eslint-disable-next-line no-new
      new QRCode(qrcode, {
        text: window.twoFaBarCodeSetupUrl,
      });

      $(qrcode).attr('title', ''); // do not show secret on hover

      if (this.accessErrorString) {
        // user entered something wrong
        this.step = 3;
        this.scrollToEnd();
      }

      $(this.$refs.root as HTMLElement).on('click', '.setupStep2Link', () => {
        Matomo.helper.modalConfirm('#setupTwoFAsecretConfirm');
      });
    });
  },
  methods: {
    scrollToEnd() {
      setTimeout(() => {
        let id = '';
        if (this.step === 2) {
          id = '#twoFactorStep2';
        } else if (this.step === 3) {
          id = '#twoFactorStep3';
        }

        if (id) {
          Matomo.helper.lazyScrollTo(id, 50, true);
        }
      }, 50);
    },
    nextStep() {
      this.step += 1;
      this.scrollToEnd();
    },
    copyRecoveryCodesToClipboard() {
      const textarea = document.createElement('textarea');
      textarea.value = this.codesJson;
      textarea.setAttribute('readonly', '');
      textarea.style.position = 'absolute';
      textarea.style.left = '-9999px';
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand('copy');
      document.body.removeChild(textarea);
    },
    downloadRecoveryCodes() {
      Matomo.helper.sendContentAsDownload('analytics_recovery_codes.txt', this.codesJson);
    },
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
    print() {
      window.print();
    },
  },
  computed: {
    setupAuthenticatorOnDeviceStep2() {
      return translate(
        'TwoFactorAuth_SetupAuthenticatorOnDeviceStep2',
        '<a class="setupStep2Link">',
        '</a>',
      );
    },
    codesJson() {
      return JSON.stringify((this.codes || []).join('\n'));
    },
  },
});
</script>
