<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock
    :content-title="contentTitle"
    class="userSettings2FA"
  >
    <p v-html="$sanitize(twoFactorAuthIntro)"></p>

    <p v-if="isEnabled">
      <strong class="twoFaStatusEnabled">
        {{ translate('TwoFactorAuth_TwoFactorAuthenticationIsEnabled') }}
      </strong>
    </p>

    <p v-if="isEnabled">
      <span v-if="isForced">
        {{ translate('TwoFactorAuth_TwoFactorAuthenticationRequired') }}
        <br />
        <br />
        <a
          class="btn btn-link enable2FaLink"
          :href="setupTwoFactorAuthLink"
          style="margin-right:3.5px"
        >{{ translate('TwoFactorAuth_ConfigureDifferentDevice') }}</a>
      </span>
      <span v-else>
        <a
          class="btn btn-link enable2FaLink"
          :href="setupTwoFactorAuthLink"
          style="margin-right:3.5px"
        >{{ translate('TwoFactorAuth_ConfigureDifferentDevice') }}</a>
        <a :href="disableTwoFactorAuthLink" style="display:none;" id="disable2fa">disable2fa</a>
        <input
          type="button"
          class="btn btn-link disable2FaLink"
          @click="onDisable2FaLinkClick()"
          :value="translate('TwoFactorAuth_DisableTwoFA')"
          style="margin-right:3.5px"
        />
      </span>

      <a
        class="btn btn-link showRecoveryCodesLink"
        :href="showRecoveryCodesLink"
      >{{ translate('TwoFactorAuth_ShowRecoveryCodes') }}</a>
    </p>
    <p v-else>
      <strong>{{ translate('TwoFactorAuth_TwoFactorAuthenticationIsDisabled') }}</strong>
      <br />
      <br />
      <a
        class="btn btn-link enable2FaLink"
        :href="setupTwoFactorAuthLink"
      >{{ translate('TwoFactorAuth_EnableTwoFA') }}</a>
    </p>

    <div id="confirmDisable2FA" class="ui-confirm" ref="confirmDisable2FA">
      <h2>{{ translate('TwoFactorAuth_ConfirmDisableTwoFA') }}</h2>
      <input role="yes" type="button" :value="translate('General_Yes')"/>
      <input role="no" type="button" :value="translate('General_No')"/>
    </div>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentBlock,
  MatomoUrl,
  translate,
  Matomo,
  externalLink,
} from 'CoreHome';

export default defineComponent({
  props: {
    isEnabled: Boolean,
    isForced: Boolean,
    disableNonce: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
  },
  computed: {
    contentTitle() {
      const part1 = translate('TwoFactorAuth_TwoFactorAuthentication');
      const part2 = translate('TwoFactorAuth_TwoFAShort');
      return `${part1} (${part2})`;
    },
    twoFactorAuthIntro() {
      return translate(
        'TwoFactorAuth_TwoFactorAuthenticationIntro',
        externalLink('https://matomo.org/faq/general/faq_27245'),
        '</a>',
      );
    },
    setupTwoFactorAuthLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'TwoFactorAuth',
        action: 'setupTwoFactorAuth',
      })}`;
    },
    disableTwoFactorAuthLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'TwoFactorAuth',
        action: 'disableTwoFactorAuth',
        disableNonce: this.disableNonce,
      })}`;
    },
    showRecoveryCodesLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes',
      })}`;
    },
  },
  methods: {
    onDisable2FaLinkClick() {
      const nonce = this.disableNonce;
      Matomo.helper.modalConfirm(this.$refs.confirmDisable2FA as HTMLElement, {
        yes() {
          MatomoUrl.updateUrl({
            module: 'TwoFactorAuth',
            action: 'disableTwoFactorAuth',
            disableNonce: nonce,
          });
        },
      });
    },
  },
});
</script>
