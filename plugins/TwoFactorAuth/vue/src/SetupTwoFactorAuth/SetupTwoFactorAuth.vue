<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="standalone
      ? translate('TwoFactorAuth_RequiredToSetUpTwoFactorAuthentication')
      : translate('TwoFactorAuth_SetUpTwoFactorAuthentication')"
  >
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
        <InstallOTPApp />
        <p v-html="$sanitize(setupAuthenticatorOnDeviceStep2ShowCodes)"></p>
        <p>
          <br />
          <button
            class="btn showOtpCodes"
            v-show="step >= 2"
            @click="showQrCodeModal()"
          >{{ translate('TwoFactorAuth_ShowCodes') }}</button>
        </p>
      </div>
      <a
        name="twoFactorStep3"
        id="twoFactorStep3"
        style="opacity: 0"
      />
      <div v-show="step >= 3">
        <h2>
          {{ translate('TwoFactorAuth_StepX', 3) }} - {{ translate('TwoFactorAuth_ConfirmSetup') }}
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

      <MatomoDialog
        v-model="qrCodeDialogVisible"
        @validation="closeQrCodeModal(); nextStep()"
        :options="{ focusSelector: '.modal-action.btn'}"
      >
        <div
          class="ui-confirm two-fa-qr-code-dialog"
        >
          <h2>{{ translate('TwoFactorAuth_Your2FaAuthSecret') }}</h2>
          <div class="row">
            <div class="col l8 offset-l2 m10 offset-m1 s12 center-align">
              <p>{{ translate('TwoFactorAuth_ShowCodeModalInstructions1') }}</p>
              <p>
                <span
                  id="qrcode"
                  ref="qrcode"
                  title
                />
              </p>
              <p>{{ translate('TwoFactorAuth_ShowCodeModalInstructions2') }}</p>

              <div class="text-code">
                <pre v-copy-to-clipboard="{}">{{ newSecret}}</pre>
              </div>

              <p v-html="$sanitize(showCodeModalInstructions3)"></p>
            </div>
          </div>

          <div class="row">
            <div class="col l8 offset-l2 m10 offset-m1 s12">
              <h3>{{ translate('TwoFactorAuth_DontHaveOTPApp') }}</h3>
              <InstallOTPApp />
            </div>
          </div>

          <input role="validation" type="button" :value="translate('General_Continue')"/>
          <input role="no" type="button" :value="translate('General_Cancel')"/>
        </div>
      </MatomoDialog>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  Notification,
  MatomoUrl,
  ContentBlock,
  MatomoDialog,
  CopyToClipboard,
} from 'CoreHome';
import { Field } from 'CorePluginsAdmin';
import '../types';
import ShowRecoveryCodes from '../ShowRecoveryCodes/ShowRecoveryCodes.vue';
import InstallOTPApp from './InstallOTPApp.vue';

interface SetupTwoFactorAuthState {
  step: number;
  hasDownloadedRecoveryCode: boolean;
  authCode: string;
  qrCodeDialogVisible: boolean;
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
    newSecret: {
      type: String,
      required: true,
    },
    codes: Array,
    twoFaBarCodeSetupUrl: {
      type: String,
      required: true,
    },
    standalone: Boolean,
  },
  components: {
    InstallOTPApp,
    MatomoDialog,
    ShowRecoveryCodes,
    Notification,
    Field,
    ContentBlock,
  },
  directives: {
    CopyToClipboard,
  },
  data(): SetupTwoFactorAuthState {
    return {
      step: 1,
      hasDownloadedRecoveryCode: false,
      authCode: '',
      qrCodeDialogVisible: false,
    };
  },
  mounted() {
    setTimeout(() => {
      const qrcode = this.$refs.qrcode as HTMLElement;

      // eslint-disable-next-line no-new
      new QRCode(qrcode, {
        text: this.twoFaBarCodeSetupUrl,
        width: 200,
        height: 200,
      });

      $(qrcode).attr('title', ''); // do not show secret on hover

      if (this.accessErrorString) {
        // user entered something wrong
        this.step = 3;
        this.scrollToEnd();
      }
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
    showQrCodeModal() {
      this.qrCodeDialogVisible = true;
    },
    closeQrCodeModal() {
      this.qrCodeDialogVisible = false;
    },
    nextStep() {
      this.step += 1;
      if (this.step > 3) {
        this.step = 3;
      }
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
    setupAuthenticatorOnDeviceStep2ShowCodes() {
      return translate(
        'TwoFactorAuth_SetupAuthenticatorOnDeviceStep2ShowCodes',
        translate('TwoFactorAuth_ShowCodes'),
      );
    },
    showCodeModalInstructions3() {
      return translate(
        'TwoFactorAuth_ShowCodeModalInstructions3',
        translate('General_Continue'),
      );
    },
  },
});
</script>
