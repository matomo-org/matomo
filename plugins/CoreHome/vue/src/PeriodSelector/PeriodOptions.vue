<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="periodOptions"
    role="radiogroup"
    :aria-label="translate('General_ChoosePeriod')"
  >
    <p
      v-for="period in displayPeriods"
      :key="period"
    >
      <label
        class="period-option-label"
        :class="{ 'selected-period-label': checkedPeriodId === period }"
        :title="period === activeDatePeriod
          ? ''
          : translate('General_DoubleClickToChangePeriod')"
        @dblclick="handlePeriodDoubleClick(period)"
      >
        <input
          class="period-option-input"
          type="radio"
          :name="periodInputName"
          :id="`period_id_${period}`"
          :checked="checkedPeriodId === period"
          @change="handlePeriodSelected(period)"
          @keydown.enter.prevent="handlePeriodEnter(period)"
        />
        <span class="period-option-text">{{ getPeriodDisplayText(period) }}</span>
      </label>
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

let nextPeriodOptionsGroupId = 0;

export default defineComponent({
  name: 'PeriodOptions',
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
  data() {
    const periodInputName = `period-${nextPeriodOptionsGroupId}`;
    nextPeriodOptionsGroupId += 1;

    return {
      periodInputName,
    };
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
    handlePeriodEnter(period: string) {
      this.handlePeriodSelected(period);
    },
    handlePeriodDoubleClick(period: string) {
      const payload: PeriodSelectionPayload = { period };
      this.$emit('dblclick', payload);
    },
  },
});
</script>
