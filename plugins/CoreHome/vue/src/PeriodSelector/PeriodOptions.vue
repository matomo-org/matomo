<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="periodOptions">
    <p
      v-for="period in periods"
      :key="period"
    >
      <label
        :class="{ 'selected-period-label': checkedPeriodId === period }"
        :title="period === activeDatePeriod
          ? ''
          : translate('General_DoubleClickToChangePeriod')"
        @dblclick="handlePeriodDoubleClick(period)"
      >
        <input
          type="radio"
          name="period"
          :id="`period_id_${period}`"
          :checked="checkedPeriodId === period"
          @change="handlePeriodSelected(period)"
          @dblclick="handlePeriodDoubleClick(period)"
        />
        <span>{{ getPeriodDisplayText(period) }}</span>
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
  methods: {
    translate,
    getPeriodDisplayText(periodLabel: string): string {
      return Periods.get(periodLabel).getDisplayText();
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
