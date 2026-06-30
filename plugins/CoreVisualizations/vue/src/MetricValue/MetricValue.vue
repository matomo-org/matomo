<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="metricValue">
    <div
      class="metricValue__title"
      :class="{ 'metricValue__title--documented': !!documentation }"
      :title="documentation || null"
      v-tooltips="{ duration: 200, delay: 200 }"
    >{{ title }}</div>
    <div class="metricValue__primary">
      <span class="metricValue__number">{{ value }}</span>
      <slot name="evolution" />
    </div>
    <div
      v-if="hasSecondary"
      class="metricValue__secondary"
    >
      <span class="metricValue__secondaryValue">{{ secondaryValue }}</span>
      <span
        v-if="secondaryLabel"
        class="metricValue__secondaryLabel"
      >{{ secondaryLabel }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Tooltips } from 'CoreHome';

export default defineComponent({
  name: 'MetricValue',
  directives: {
    Tooltips,
  },
  props: {
    title: {
      type: String,
      required: true,
    },
    // Pre-formatted value (e.g. "9,527" or "4min 22s"); rendered verbatim, no formatting here.
    value: {
      type: [String, Number],
      required: true,
    },
    // Optional secondary line. Value and label are kept separate so they can be
    // styled independently (e.g. "9,527" darker, "unique visitors" grey). Matomo
    // hands these out separately as metric.value + metric.description.
    secondaryValue: [String, Number],
    secondaryLabel: String,
    // Optional documentation shown as a tooltip on the title.
    documentation: String,
  },
  computed: {
    hasSecondary(): boolean {
      return this.secondaryValue !== undefined
        && this.secondaryValue !== null
        && this.secondaryValue !== '';
    },
  },
});
</script>
