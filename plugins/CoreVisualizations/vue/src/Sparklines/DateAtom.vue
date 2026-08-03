<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="dateAtom"
    :title="label"
  >{{ label }}</div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * A compared date shown above its value column in a date-comparison sparkline card. The label is
 * the backend's already-localised pretty period string (eg "Monday, May 4, 2026"); this atom only
 * styles it. Kept a separate component so a series-colour indicator can be added later without
 * touching DateComparison.
 */
export default defineComponent({
  name: 'DateAtom',
  props: {
    label: {
      type: String,
      required: true,
    },
  },
});
</script>
