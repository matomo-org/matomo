<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div id="calendarRangeFrom">
      <h6>
        {{ translate('General_DateRangeFrom') }}
        <input
          type="text"
          id="inputCalendarFrom"
          name="inputCalendarFrom"
          class="browser-default"
          v-model="startDateText"
          @keydown="onRangeInputChanged('from', $event)"
          @keyup="handleEnterPress($event)"
        />
      </h6>
      <DatePicker
        id="calendarFrom"
        :view-date="startDate"
        :selected-date-start="fromPickerSelectedDates[0]"
        :selected-date-end="fromPickerSelectedDates[1]"
        :highlighted-date-start="fromPickerHighlightedDates[0]"
        :highlighted-date-end="fromPickerHighlightedDates[1]"
        @date-select="setStartRangeDate($event.date)"
        @cell-hover="fromPickerHighlightedDates = getNewHighlightedDates($event.date, $event.$cell)"
        @cell-hover-leave="fromPickerHighlightedDates = [null, null]"
      >
      </DatePicker>
    </div>
    <div id="calendarRangeTo">
      <h6>
        {{ translate('General_DateRangeTo') }}
        <input
          type="text"
          id="inputCalendarTo"
          name="inputCalendarTo"
          class="browser-default"
          v-model="endDateText"
          @keydown="onRangeInputChanged('to', $event)"
          @keyup="handleEnterPress($event)"
        />
      </h6>
      <DatePicker
        id="calendarTo"
        :view-date="endDate"
        :selected-date-start="toPickerSelectedDates[0]"
        :selected-date-end="toPickerSelectedDates[1]"
        :highlighted-date-start="toPickerHighlightedDates[0]"
        :highlighted-date-end="toPickerHighlightedDates[1]"
        @date-select="setEndRangeDate($event.date)"
        @cell-hover="toPickerHighlightedDates = getNewHighlightedDates($event.date, $event.$cell)"
        @cell-hover-leave="toPickerHighlightedDates = [null, null]"
      >
      </DatePicker>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import DatePicker from '../DatePicker/DatePicker.vue';
import { parseDate, format } from '../Periods/utilities';
import ChangeEvent = JQuery.ChangeEvent;

const DATE_FORMAT = 'YYYY-MM-DD';

interface DateRangePickerState {
  fromPickerSelectedDates: (Date|null)[];
  toPickerSelectedDates: (Date|null)[];
  fromPickerHighlightedDates: (Date|null)[];
  toPickerHighlightedDates: (Date|null)[];
  startDateText?: string;
  endDateText?: string;
  startDateInvalid: boolean;
  endDateInvalid: boolean;
}

export default defineComponent({
  props: {
    startDate: String,
    endDate: String,
  },
  components: {
    DatePicker,
  },
  data(): DateRangePickerState {
    let startDate = null;
    try {
      if (this.startDate) {
        startDate = parseDate(this.startDate);
      }
    } catch (e) {
      // ignore
    }

    let endDate = null;
    try {
      if (this.endDate) {
        endDate = parseDate(this.endDate);
      }
    } catch (e) {
      // ignore
    }

    return {
      fromPickerSelectedDates: [startDate, startDate],
      toPickerSelectedDates: [endDate, endDate],
      fromPickerHighlightedDates: [null, null],
      toPickerHighlightedDates: [null, null],
      startDateText: this.startDate,
      endDateText: this.endDate,
      startDateInvalid: false,
      endDateInvalid: false,
    };
  },
  emits: ['rangeChange', 'submit'],
  watch: {
    startDate() {
      this.startDateText = this.startDate;
      this.setStartRangeDateFromStr(this.startDate);
    },
    endDate() {
      this.endDateText = this.endDate;
      this.setEndRangeDateFromStr(this.endDate);
    },
  },
  mounted() {
    this.rangeChanged(); // emit with initial range pair
  },
  methods: {
    setStartRangeDate(date: Date) {
      this.fromPickerSelectedDates = [date, date];

      this.rangeChanged();
    },
    setEndRangeDate(date: Date) {
      this.toPickerSelectedDates = [date, date];

      this.rangeChanged();
    },
    onRangeInputChanged(source: string, event: ChangeEvent) {
      setTimeout(() => {
        if (source === 'from') {
          this.setStartRangeDateFromStr(event.target.value);
        } else {
          this.setEndRangeDateFromStr(event.target.value);
        }
      });
    },
    getNewHighlightedDates(date: Date, $cell: JQuery) {
      if ($cell.hasClass('ui-datepicker-unselectable')) {
        return null;
      }

      return [date, date];
    },
    handleEnterPress($event: KeyboardEvent) {
      if ($event.keyCode !== 13) {
        return;
      }

      this.$emit('submit', {
        start: this.startDate,
        end: this.endDate,
      });
    },
    setStartRangeDateFromStr(dateStr?: string) {
      this.startDateInvalid = true;

      let startDateParsed: Date|null = null;
      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          startDateParsed = parseDate(dateStr);
        }
      } catch (e) {
        // ignore
      }

      if (startDateParsed) {
        this.fromPickerSelectedDates = [startDateParsed, startDateParsed];
        this.startDateInvalid = false;

        this.rangeChanged();
      }
    },
    setEndRangeDateFromStr(dateStr?: string) {
      this.endDateInvalid = true;

      let endDateParsed: Date|null = null;
      try {
        if (dateStr && dateStr.length === DATE_FORMAT.length) {
          endDateParsed = parseDate(dateStr);
        }
      } catch (e) {
        // ignore
      }

      if (endDateParsed) {
        this.toPickerSelectedDates = [endDateParsed, endDateParsed];
        this.endDateInvalid = false;

        this.rangeChanged();
      }
    },
    rangeChanged() {
      this.$emit('rangeChange', {
        start: this.fromPickerSelectedDates[0] ? format(this.fromPickerSelectedDates[0]) : null,
        end: this.toPickerSelectedDates[0] ? format(this.toPickerSelectedDates[0]) : null,
      });
    },
  },
});
</script>
