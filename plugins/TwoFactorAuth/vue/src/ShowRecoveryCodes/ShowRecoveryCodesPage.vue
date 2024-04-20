<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock :content-title="contentTitle">
    <ShowRecoveryCodes :codes="codes" />

    <h2>{{ translate('TwoFactorAuth_GenerateNewRecoveryCodes') }}</h2>
    <p>{{ translate('TwoFactorAuth_GenerateNewRecoveryCodesInfo') }}<br /><br /></p>

    <div class="alert alert-success" v-if="regenerateSuccess">
      {{ translate('TwoFactorAuth_RecoveryCodesRegenerated') }}
    </div>

    <div class="alert alert-danger" v-if="regenerateError">
      {{ translate('General_ExceptionSecurityCheckFailed') }}
    </div>

    <form method="post" :action="showRecoveryCodesLink">
      <input type="hidden" name="regenerateNonce" :value="regenerateNonce" />
      <input
        type="submit"
        class="btn"
        :value="translate('TwoFactorAuth_GenerateNewRecoveryCodes')"
      />
    </form>

  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, translate, MatomoUrl } from 'CoreHome';
import ShowRecoveryCodes from './ShowRecoveryCodes.vue';

export default defineComponent({
  props: {
    codes: Array,
    regenerateSuccess: Boolean,
    regenerateError: Boolean,
    regenerateNonce: {
      type: String,
      required: true,
    },
  },
  components: {
    ContentBlock,
    ShowRecoveryCodes,
  },
  computed: {
    contentTitle() {
      const part1 = translate('TwoFactorAuth_TwoFactorAuthentication');
      const part2 = translate('TwoFactorAuth_RecoveryCodes');
      return `${part1} - ${part2}`;
    },
    showRecoveryCodesLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'TwoFactorAuth',
        action: 'showRecoveryCodes',
      })}`;
    },
  },
});
</script>
