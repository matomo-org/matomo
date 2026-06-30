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
      :title="documentation || displayTitle"
      v-tooltips="{ duration: 200, delay: 200 }"
    >{{ displayTitle }}</div>
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
    // Optional metric documentation; when set it is shown as the title tooltip (otherwise the
    // tooltip falls back to the full title so a clipped title stays recoverable on hover).
    documentation: String,
  },
  computed: {
    // Capitalise the first letter once (e.g. "bounce rate" -> "Bounce rate") and reuse it for both
    // the visible title and the tooltip, so the transformation lives in a single place rather than
    // being split between a CSS ::first-letter rule (display) and the raw prop (tooltip).
    displayTitle(): string {
      return this.title ? this.title.charAt(0).toUpperCase() + this.title.slice(1) : this.title;
    },
    hasSecondary(): boolean {
      return this.secondaryValue !== undefined
        && this.secondaryValue !== null
        && this.secondaryValue !== '';
    },
  },
});
</script>
