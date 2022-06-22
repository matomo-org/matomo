<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="translate('TwoFactorAuth_TwoFactorAuthentication')">
    <div class="message_container">
      <FormErrors
        :form-errors="formData.errors"
      />

      <Notification
        v-if="accessErrorString"
        :noclear="true"
        context="error"
      >
        <strong>{{ translate('General_Error') }}</strong>:
        <span v-html="$sanitize(accessErrorString)" />
        <br/>
      </Notification>
    </div>

    <form v-bind="formData.attributes" class="loginTwoFaForm">
      <div class="row">
        <div class="col s12 input-field">
          <input
            type="hidden"
            name="form_nonce"
            id="login_form_nonce"
            :value="formNonce"
          />

          <input
            type="text"
            name="form_authcode"
            placeholder=""
            id="login_form_authcode"
            class="input"
            value=""
            size="20"
            autocorrect="off"
            autocapitalize="none"
            autocomplete="off"
            tabindex="10"
            autofocus="autofocus"
          />

          <label for="login_form_authcode">
            <i class="icon-user icon"></i>
            {{ translate('TwoFactorAuth_AuthenticationCode') }}
          </label>
        </div>
      </div>

      <div class="row actions">
        <div class="col s12">
          <input
            class="submit btn"
            id="login_form_submit"
            type="submit"
            :value="translate('TwoFactorAuth_Verify')"
            tabindex="100"
          />
        </div>
      </div>

    </form>

    <p>
      {{ translate('TwoFactorAuth_VerifyIdentifyExplanation') }}
      <span v-html="learnMoreText" />

      <br /><br />
      <strong>{{ translate('TwoFactorAuth_DontHaveYourMobileDevice') }}</strong>
      <br />
      <a href="https://matomo.org/faq/how-to/faq_27248" rel="noreferrer noopener">
        {{ translate('TwoFactorAuth_EnterRecoveryCodeInstead') }}
      </a>
      <br />
      <a :href="mailToLink" rel="noreferrer noopener">
        {{ translate('TwoFactorAuth_AskSuperUserResetAuthenticationCode') }}
      </a>
      <br />
      <a :href="logoutLink" rel="noreferrer noopener">{{ translate('General_Logout') }}</a>
    </p>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  Notification,
  translate,
  MatomoUrl,
} from 'CoreHome';
import { FormErrors } from 'Login';

export default defineComponent({
  props: {
    formData: {
      type: Object,
      required: true,
    },
    accessErrorString: String,
    formNonce: {
      type: String,
      required: true,
    },
    loginModule: {
      type: String,
      required: true,
    },
    piwikUrl: String,
    userLogin: {
      type: String,
      required: true,
    },
    contactEmail: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    Notification,
    FormErrors,
  },
  computed: {
    learnMoreText() {
      const link = 'https://matomo.org/faq/general/faq_27245';
      return translate(
        'General_LearnMore',
        `<a href="${link}" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
    mailToLink() {
      return `mailto:${this.contactEmail}?${MatomoUrl.stringify({
        subject: translate('TwoFactorAuth_NotPossibleToLogIn'),
        body: translate(
          'TwoFactorAuth_LostAuthenticationDevice',
          '\n\n',
          '\n\n',
          this.piwikUrl || '',
          '\n\n',
          this.userLogin,
          'https://matomo.org/faq/how-to/faq_27248',
        ),
      })}`;
    },
    logoutLink() {
      return `?${MatomoUrl.stringify({
        module: this.loginModule,
        action: 'logout',
      })}`;
    },
  },
});
</script>
