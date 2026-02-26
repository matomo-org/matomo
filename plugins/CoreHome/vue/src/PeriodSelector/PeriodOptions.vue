<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="periodOptions"
    role="group"
    :aria-label="translate('General_ChoosePeriod')"
  >
    <p
      v-for="period in displayPeriods"
      :key="period"
    >
      <button
        type="button"
        :id="`period_id_${period}`"
        :aria-pressed="checkedPeriodId === period ? 'true' : 'false'"
        :class="{ 'selected-period-label': checkedPeriodId === period }"
        :title="period === activeDatePeriod
          ? ''
          : translate('General_DoubleClickToChangePeriod')"
        @click="handlePeriodSelected(period)"
        @dblclick="handlePeriodDoubleClick(period)"
      >
        <span>{{ getPeriodDisplayText(period) }}</span>
      </button>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { Periods } from '../Periods';
import { translate } from '../translate';

interface PeriodSelectionPayload {
  period: string;
}

export default defineComponent({
  props: {
    modelValue: {
      type: String as PropType<string|null>,
      default: null,
    },
    periods: {
      type: Array as PropType<string[]>,
      required: true,
    },
    checkedPeriodId: {
      type: String as PropType<string|null>,
      default: null,
    },
    activeDatePeriod: {
      type: String,
      required: true,
    },
  },
  emits: ['update:modelValue', 'select', 'dblclick'],
  computed: {
    displayPeriods(): string[] {
      if (!this.periods.includes('range')) {
        return this.periods;
      }

      return ['range'].concat(this.periods.filter((period) => period !== 'range'));
    },
  },
  methods: {
    translate,
    getPeriodDisplayText(periodLabel: string): string {
      const displayText = periodLabel === 'range'
        ? `${translate('General_Custom')} ${translate('General_DateRangeInPeriodList')}`
        : Periods.get(periodLabel).getDisplayText();

      return displayText.charAt(0).toUpperCase() + displayText.slice(1);
    },
    handlePeriodSelected(period: string) {
      const payload: PeriodSelectionPayload = { period };
      this.$emit('update:modelValue', period);
      this.$emit('select', payload);
    },
    handlePeriodDoubleClick(period: string) {
      const payload: PeriodSelectionPayload = { period };
      this.$emit('dblclick', payload);
    },
  },
});
</script>
