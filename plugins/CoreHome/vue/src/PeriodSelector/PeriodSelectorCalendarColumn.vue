<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="period-selector-calendar-column">
    <div
      :class="{ 'calendar-disabled': uiSelection.type === 'preset' }"
      :aria-disabled="uiSelection.type === 'preset' ? 'true' : 'false'"
      @click.capture="$emit('range-preset-date-cell-click-capture', $event)"
    >
      <DateRangePicker
        v-show="calendarViewport === 'range'"
        class="period-range"
        :start-date="displayRangeStartDate"
        :end-date="displayRangeEndDate"
        :disabled="uiSelection.type === 'preset'"
        @range-change="$emit('range-change', $event)"
        @submit="$emit('apply-click')"
      />
    </div>
    <div
      class="period-date"
      :class="{ 'calendar-disabled': uiSelection.type === 'preset' }"
      :aria-disabled="uiSelection.type === 'preset' ? 'true' : 'false'"
      v-show="calendarViewport === 'single'"
    >
      <PeriodDatePicker
        id="datepicker"
        :period="singleCalendarPeriod"
        :date="singleCalendarSelectedDate"
        :disabled="uiSelection.type === 'preset'"
        @select="$emit('single-date-select', $event.date)"
      />
    </div>
    <PeriodSelectorCompareControls
      :is-comparison-enabled="isComparisonEnabled"
      :is-comparing="isComparing"
      :compare-period-type="comparePeriodType"
      :compare-start-date="compareStartDate"
      :compare-end-date="compareEndDate"
      :compare-period-dropdown-options="comparePeriodDropdownOptions"
      @update:isComparing="$emit('update:isComparing', $event)"
      @update:comparePeriodType="$emit('update:comparePeriodType', $event)"
      @update:compareStartDate="$emit('update:compareStartDate', $event)"
      @update:compareEndDate="$emit('update:compareEndDate', $event)"
    />
    <div class="apply-button-container">
      <input
        type="submit"
        id="calendarApply"
        class="btn"
        @click="$emit('apply-click')"
        :disabled="!isApplyEnabled"
        :value="translate('General_Apply')"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import DateRangePicker from '../DateRangePicker/DateRangePicker.vue';
import PeriodDatePicker from '../PeriodDatePicker/PeriodDatePicker.vue';
import { translate } from '../translate';
import PeriodSelectorCompareControls from './PeriodSelectorCompareControls.vue';

export default defineComponent({
  name: 'PeriodSelectorCalendarColumn',
  components: {
    DateRangePicker,
    PeriodDatePicker,
    PeriodSelectorCompareControls,
  },
  props: {
    uiSelection: {
      type: Object as PropType<{ type: string; id: string }>,
      required: true,
    },
    calendarViewport: {
      type: String as PropType<'single' | 'range'>,
      required: true,
    },
    displayRangeStartDate: {
      type: String as PropType<string|null>,
      default: null,
    },
    displayRangeEndDate: {
      type: String as PropType<string|null>,
      default: null,
    },
    singleCalendarPeriod: {
      type: String as PropType<'day' | 'week' | 'month' | 'year'>,
      required: true,
    },
    singleCalendarSelectedDate: {
      type: Date as PropType<Date|null>,
      default: null,
    },
    isComparisonEnabled: {
      type: Boolean,
      required: true,
    },
    isComparing: {
      type: Boolean as PropType<boolean|null>,
      default: null,
    },
    comparePeriodType: {
      type: String,
      required: true,
    },
    compareStartDate: {
      type: String,
      required: true,
    },
    compareEndDate: {
      type: String,
      required: true,
    },
    comparePeriodDropdownOptions: {
      type: Array as PropType<Array<{ key: string; value: string }>>,
      required: true,
    },
    isApplyEnabled: {
      type: Boolean,
      required: true,
    },
  },
  emits: [
    'range-change',
    'single-date-select',
    'apply-click',
    'range-preset-date-cell-click-capture',
    'update:isComparing',
    'update:comparePeriodType',
    'update:compareStartDate',
    'update:compareEndDate',
  ],
  methods: {
    translate,
  },
});
</script>
