<!--
  Matomo - free/libre analytics platform

  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-show="loading" class="loadingPiwik">
    <img src="plugins/Morpheus/images/loading-blue.gif" alt=""/>
    <span>{{ loadingMessage }}</span>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { translate } from '../translate';

export default defineComponent({
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
