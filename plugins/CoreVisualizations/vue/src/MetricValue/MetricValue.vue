<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="metricValue">
    <div
      v-if="displayTitle"
      class="metricValue__title"
      :class="{ 'metricValue__title--documented': !!documentation }"
      :title="documentation || displayTitle"
      v-tooltips="{ duration: 200, delay: 200 }"
    >{{ displayTitle }}</div>
    <div class="metricValue__primary">
      <span
        class="metricValue__number"
        :title="displayValue?.toString()"
        v-tooltips="{ duration: 200, delay: 200 }"
      >{{ displayValue }}</span>
      <slot name="evolution" />
    </div>
    <div
      v-if="hasSecondary"
      class="metricValue__secondary"
    >
      <span class="metricValue__secondaryValue">{{ displaySecondaryValue }}</span>
      <span
        v-if="displaySecondaryLabel"
        class="metricValue__secondaryLabel"
      >{{ displaySecondaryLabel }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { NumberFormatter, Tooltips, ucfirst } from 'CoreHome';

export default defineComponent({
  name: 'MetricValue',
  directives: {
    Tooltips,
  },
  props: {
    // Optional: the date-comparison card reuses MetricValue for a value column with no title
    // (the date label is rendered separately by DateAtom). The title div is skipped when empty.
    title: {
      type: String,
      default: '',
    },
    // Metric value: a raw number is locale-formatted here; an already-formatted string
    // (e.g. "50%" or "4min 22s") is rendered as-is.
    value: {
      type: [String, Number],
      required: true,
    },
    // Optional secondary line, formatted the same way as `value`. Value and label are kept
    // separate so they can be styled independently (e.g. "9,527" darker, "unique visitors" grey).
    // Matomo hands these out separately as metric.value + metric.description.
    secondaryValue: [String, Number],
    secondaryLabel: String,
    // Optional metric documentation; when set it is shown as the title tooltip (otherwise the
    // tooltip falls back to the full title so a clipped title stays recoverable on hover).
    documentation: String,
  },
  computed: {
    displayTitle(): string {
      return ucfirst(this.title);
    },
    displayValue(): string | number | undefined {
      return this.formatValue(this.value);
    },
    displaySecondaryValue(): string | number | undefined {
      return this.formatValue(this.secondaryValue);
    },
    displaySecondaryLabel(): string {
      return this.stripValuePlaceholder(this.secondaryLabel);
    },
    hasSecondary(): boolean {
      return this.secondaryValue !== undefined
        && this.secondaryValue !== null
        && this.secondaryValue !== '';
    },
  },
  methods: {
    // Locale-format raw numbers (plain metrics); leave already-formatted strings untouched.
    formatValue(value?: string | number): string | number | undefined {
      return typeof value === 'number' ? NumberFormatter.formatNumber(value, 2) : value;
    },
    // Remove any printf `%s` value placeholder and tidy whitespace. Some sparkline secondary labels
    // embed `%s` (e.g. "%s of visits", "by %s unique visitors") — a legacy sprintf convention. The
    // redesigned card renders the value separately, so the placeholder is dropped, not filled.
    stripValuePlaceholder(label?: string): string {
      if (!label) {
        return '';
      }

      return label.replace(/%s/g, '').replace(/\s+/g, ' ').trim();
    },
  },
});
</script>
