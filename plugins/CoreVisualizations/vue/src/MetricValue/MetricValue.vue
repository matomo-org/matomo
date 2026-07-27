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
      <span class="metricValue__secondaryLine">{{ displaySecondaryLine }}</span>
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
    // Optional secondary line: value and label, combined into one string for display.
    // Matomo provides these separately as metric.value + metric.description.
    secondaryValue: [String, Number],
    secondaryLabel: String,
    // Optional metric documentation; when set it is shown as the title tooltip (otherwise the
    // tooltip falls back to the full title so a clipped title stays recoverable on hover).
    documentation: String,
  },
  computed: {
    displayTitle(): string {
      return ucfirst(this.title, document.documentElement.lang);
    },
    displayValue(): string | number | undefined {
      return this.formatValue(this.value);
    },
    displaySecondaryValue(): string | number | undefined {
      return this.formatValue(this.secondaryValue);
    },
    displaySecondaryLine(): string {
      const value = this.displaySecondaryValue;
      const valueText = value === undefined || value === null ? '' : String(value);
      const label = this.secondaryLabel;
      if (!label) {
        return valueText;
      }
      // Merge the value into the label at its printf `%s` slot so word order holds across locales
      // (some put `%s` last), otherwise prepend it. The replace callback keeps a `$` in the value
      // (e.g. '$12') from acting as a regex backreference.
      if (/%(?:\d+\$)?s/.test(label)) {
        return label.replace(/%(?:\d+\$)?s/g, () => valueText);
      }

      return `${valueText} ${label}`;
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
  },
});
</script>
