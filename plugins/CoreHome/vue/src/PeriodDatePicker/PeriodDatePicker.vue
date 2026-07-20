<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <DatePicker
    :selected-date-start="selectedDates[0]"
    :selected-date-end="selectedDates[1]"
    :persistent-highlighted-date-start="committedBetweenHighlightDates[0]"
    :persistent-highlighted-date-end="committedBetweenHighlightDates[1]"
    :highlighted-date-start="highlightedDates ? highlightedDates[0] : null"
    :highlighted-date-end="highlightedDates ? highlightedDates[1] : null"
    :view-date="viewDate"
    :step-months="period === 'year' ? 12 : 1"
    :disable-month-dropdown="period === 'year'"
    :disabled="disabled"
    @cell-hover="onHoverNormalCell($event.date, $event.$cell)"
    @cell-hover-leave="onHoverLeaveNormalCells()"
    @date-select="onDateSelected($event.date)"
  >
  </DatePicker>
</template>

<script lang="ts">
import { defineComponent, watch, ref } from 'vue';
import DatePicker from '../DatePicker/DatePicker.vue';
import { Periods, parseDate } from '../Periods';
import {
  getSiteMaxAllowedDate,
  getSiteMinAllowedDate,
} from '../PeriodSelector/PeriodSelector.types';

export default defineComponent({
  props: {
    period: {
      type: String,
      required: true,
    },
    date: [String, Date],
    disabled: Boolean,
  },
  components: {
    DatePicker,
  },
  emits: ['select'],
  setup(props, context) {
    const viewDate = ref<string|Date|undefined|null>(props.date);
    const selectedDates = ref<(Date|null)[]>([null, null]);
    const committedBetweenHighlightDates = ref<(Date|null)[]>([null, null]);
    const highlightedDates = ref<(Date|null)[]|null>(null);
    const piwikMinDate = getSiteMinAllowedDate();
    const piwikMaxDate = getSiteMaxAllowedDate();

    function getBoundedDateRange(date: string|Date) {
      const dates = Periods.get(props.period).parse(date).getDateRange();

      // make sure highlighted date range is within min/max date range
      dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;

      return dates;
    }

    function getExclusiveBetweenRange(
      startDate: Date|null,
      endDate: Date|null,
    ): [Date|null, Date|null] {
      if (!startDate || !endDate || startDate.getTime() >= endDate.getTime()) {
        return [null, null];
      }

      const betweenStart = new Date(startDate);
      betweenStart.setDate(betweenStart.getDate() + 1);

      const betweenEnd = new Date(endDate);
      betweenEnd.setDate(betweenEnd.getDate() - 1);

      if (betweenStart.getTime() > betweenEnd.getTime()) {
        return [null, null];
      }

      return [betweenStart, betweenEnd];
    }

    function refreshCommittedBetweenHighlightFromDate(date?: string|Date|null) {
      if (!date) {
        committedBetweenHighlightDates.value = [null, null];
        return;
      }

      const boundedDateRange = getBoundedDateRange(date);
      committedBetweenHighlightDates.value = getExclusiveBetweenRange(
        boundedDateRange[0],
        boundedDateRange[1],
      );
    }

    function onHoverNormalCell(cellDate: Date, $cell: JQuery) {
      const isOutOfMinMaxDateRange = cellDate < piwikMinDate || cellDate > piwikMaxDate;

      // don't highlight anything if the period is month or day, and we're hovering over calendar
      // whitespace. since there are no dates, it's doesn't make sense what you're selecting.
      const shouldNotHighlightFromWhitespace = $cell.hasClass('ui-datepicker-other-month')
        && (props.period === 'month' || props.period === 'day');

      if (isOutOfMinMaxDateRange
        || shouldNotHighlightFromWhitespace
      ) {
        highlightedDates.value = [null, null];
        return;
      }

      // Keep hover preview inclusive (start/end + in-between) for parity with historical UX.
      highlightedDates.value = getBoundedDateRange(cellDate);
    }

    function onHoverLeaveNormalCells() {
      highlightedDates.value = null;
    }

    function onDateSelected(date: Date) {
      context.emit('select', { date });
    }

    function onChanges() {
      if (!props.period || !props.date) {
        selectedDates.value = [null, null];
        committedBetweenHighlightDates.value = [null, null];
        highlightedDates.value = null;
        viewDate.value = null;
        return;
      }

      selectedDates.value = getBoundedDateRange(props.date);
      refreshCommittedBetweenHighlightFromDate(props.date);
      highlightedDates.value = null;
      viewDate.value = parseDate(props.date);
    }

    watch(props, onChanges);

    onChanges();

    return {
      selectedDates,
      committedBetweenHighlightDates,
      highlightedDates,
      viewDate,
      onHoverNormalCell,
      onHoverLeaveNormalCells,
      onDateSelected,
    };
  },
});
</script>
