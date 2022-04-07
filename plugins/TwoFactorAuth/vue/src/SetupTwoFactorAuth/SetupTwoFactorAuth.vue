<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="setupTwoFactorAuthentication" ref="root">
    <div class="alert alert-warning" v-if="isAlreadyUsing2fa">
      {{ translate('TwoFactorAuth_WarningChangingConfiguredDevice') }}
    </div>
    <p>
      {{ translate('TwoFactorAuth_SetupIntroFollowSteps') }}
    </p>
    <h2>
      {{ translate('TwoFactorAuth_StepX', 1) }} - {{ translate('TwoFactorAuth_RecoveryCodes') }}
    </h2>

    <ShowRecoveryCodes
      :codes="codes"
      @downloaded="this.hasDownloadedRecoveryCode = true"
    />

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
      <p><span v-html="$sanitize(setupAuthenticatorOnDeviceStep2)"></span></p>
      <p>
        <br />
        <span
          id="qrcode"
          ref="qrcode"
          title
        />
      </p>
      <p>
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
            :placeholder="'123456'"
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
import ShowRecoveryCodes from '../ShowRecoveryCodes/ShowRecoveryCodes.vue';

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
    ShowRecoveryCodes,
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

      $(this.$refs.root as HTMLElement).on('click', '.setupStep2Link', (e) => {
        e.preventDefault();
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
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
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
  },
});
</script>
