<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="loading" class="loadingPiwik">
    <MatomoLoader />
    <span>{{ loadingMessage }}</span>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';
import MatomoLoader from '../MatomoLoader/MatomoLoader';

export default defineComponent({
  components: { MatomoLoader },
  props: {
    loading: {
      type: Boolean,
      required: true,
      default: false,
    },
    loadingMessage: {
      type: String,
      required: false,
      default: translate('General_LoadingData'),
    },
  },
});
</script>
