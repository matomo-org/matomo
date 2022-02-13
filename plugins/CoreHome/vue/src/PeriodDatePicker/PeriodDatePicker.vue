<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <DatePicker
    :selected-date-start="selectedDates[0]"
    :selected-date-end="selectedDates[1]"
    :highlighted-date-start="highlightedDates[0]"
    :highlighted-date-end="highlightedDates[1]"
    :view-date="viewDate"
    :step-months="period === 'year' ? 12 : 1"
    :disable-month-dropdown="period === 'year'"
    @cell-hover="onHoverNormalCell($event.date, $event.$cell)"
    @cell-hover-leave="onHoverLeaveNormalCells()"
    @date-select="onDateSelected($event.date)"
  >
  </DatePicker>
</template>

<script lang="ts">
import { defineComponent, watch, ref } from 'vue';
import DatePicker from '../DatePicker/DatePicker.vue';
import Matomo from '../Matomo/Matomo';
import { Periods, parseDate } from '../Periods';

const piwikMinDate = new Date(Matomo.minDateYear, Matomo.minDateMonth - 1, Matomo.minDateDay);
const piwikMaxDate = new Date(Matomo.maxDateYear, Matomo.maxDateMonth - 1, Matomo.maxDateDay);

export default defineComponent({
  props: {
    period: {
      type: String,
      required: true,
    },
    date: [String, Date],
  },
  components: {
    DatePicker,
  },
  emits: ['select'],
  setup(props, context) {
    const viewDate = ref<string|Date|undefined|null>(props.date);
    const selectedDates = ref<(Date|null)[]>([null, null]);
    const highlightedDates = ref<(Date|null)[]>([null, null]);

    function getBoundedDateRange(date: string|Date) {
      const dates = Periods.get(props.period).parse(date).getDateRange();

      // make sure highlighted date range is within min/max date range
      dates[0] = piwikMinDate < dates[0] ? dates[0] : piwikMinDate;
      dates[1] = piwikMaxDate > dates[1] ? dates[1] : piwikMaxDate;

      return dates;
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

      highlightedDates.value = getBoundedDateRange(cellDate);
    }

    function onHoverLeaveNormalCells() {
      highlightedDates.value = [null, null];
    }

    function onDateSelected(date: Date) {
      context.emit('select', { date });
    }

    function onChanges() {
      if (!props.period || !props.date) {
        selectedDates.value = [null, null];
        viewDate.value = null;
        return;
      }

      selectedDates.value = getBoundedDateRange(props.date);
      viewDate.value = parseDate(props.date);
    }

    watch(props, onChanges);

    onChanges();

    return {
      selectedDates,
      highlightedDates,
      viewDate,
      onHoverNormalCell,
      onHoverLeaveNormalCells,
      onDateSelected,
    };
  },
});
</script>
