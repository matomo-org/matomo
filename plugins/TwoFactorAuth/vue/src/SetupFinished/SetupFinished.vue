<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ContentBlock class="twoFactorSetupFinished">
    <h2 class="successMessage">
      {{ translate('TwoFactorAuth_SetupFinishedTitle') }}
    </h2>
    <h3>{{ translate('TwoFactorAuth_SetupFinishedSubtitle') }}</h3>
    <p>
      <br />
      <a class="btn" :href="userSecurityLink">{{ translate('General_Continue') }}</a>
    </p>
  </ContentBlock>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ContentBlock, MatomoUrl } from 'CoreHome';

export default defineComponent({
  components: {
    ContentBlock,
  },
  computed: {
    userSecurityLink(): string {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'UsersManager',
        action: 'userSecurity',
      })}`;
    },
  },
});
</script>
