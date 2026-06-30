<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <svg
    v-if="direction === 'up'"
    viewBox="0 0 16 16"
  >
    <path d="M3.77344 11L8.27344 5L12.7734 11H3.77344Z" fill="currentColor" />
  </svg>
  <svg
    v-else-if="direction === 'down'"
    viewBox="0 0 16 16"
  >
    <path d="M3.77344 6L8.27344 12L12.7734 6H3.77344Z" fill="currentColor" />
  </svg>
  <svg
    v-else
    viewBox="0 0 16 16"
  >
    <rect x="3" y="7" width="10" height="2" fill="currentColor" />
  </svg>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'EvolutionTrendIcon',
  props: {
    direction: {
      type: String,
      required: true,
      validator: (value: string) => ['up', 'down', 'neutral'].indexOf(value) !== -1,
    },
  },
});
</script>
