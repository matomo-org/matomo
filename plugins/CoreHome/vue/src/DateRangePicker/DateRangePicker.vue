<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="dateRangePicker">
    <div id="calendarRangeFrom">
      <h6 class="dateRangePicker-label">
        {{ translate('General_DateRangeFrom') }}
        <input
          type="text"
          id="inputCalendarFrom"
          name="inputCalendarFrom"
          class="browser-default dateRangePicker-field"
          :disabled="disabled"
          v-model="startDateText"
          @keydown="onRangeInputChanged('from', $event)"
          @keyup="handleEnterPress($event)"
        />
      </h6>
      <DatePicker
        id="calendarFrom"
        :view-date="startDate"
        :selected-date-start="fromPickerSelectedDate"
        :selected-date-end="fromPickerSelectedDate"
        :highlighted-date-start="fromPickerHoveredDate"
        :highlighted-date-end="fromPickerHoveredDate"
        :disabled="disabled"
        @date-select="setStartRangeDate($event.date)"
        @cell-hover="fromPickerHoveredDate = getNewHoveredDate($event.date, $event.$cell)"
        @cell-hover-leave="fromPickerHoveredDate = null"
      >
      </DatePicker>
    </div>
    <div id="calendarRangeTo">
      <h6 class="dateRangePicker-label">
        {{ translate('General_DateRangeTo') }}
        <input
          type="text"
          id="inputCalendarTo"
          name="inputCalendarTo"
          class="browser-default dateRangePicker-field"
          :disabled="disabled"
          v-model="endDateText"
          @keydown="onRangeInputChanged('to', $event)"
          @keyup="handleEnterPress($event)"
        />
      </h6>
      <DatePicker
        id="calendarTo"
        :view-date="endDate"
        :selected-date-start="toPickerSelectedDate"
        :selected-date-end="toPickerSelectedDate"
        :highlighted-date-start="toPickerHoveredDate"
        :highlighted-date-end="toPickerHoveredDate"
        :disabled="disabled"
        @date-select="setEndRangeDate($event.date)"
        @cell-hover="toPickerHoveredDate = getNewHoveredDate($event.date, $event.$cell)"
        @cell-hover-leave="toPickerHoveredDate = null"
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
  fromPickerSelectedDate: Date|null;
  toPickerSelectedDate: Date|null;
  fromPickerHoveredDate: Date|null;
  toPickerHoveredDate: Date|null;
  startDateText?: string;
  endDateText?: string;
  startDateInvalid: boolean;
  endDateInvalid: boolean;
}

export default defineComponent({
  name: 'DateRangePicker',
  props: {
    startDate: String,
    endDate: String,
    disabled: Boolean,
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
      fromPickerSelectedDate: startDate,
      toPickerSelectedDate: endDate,
      fromPickerHoveredDate: null,
      toPickerHoveredDate: null,
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
      this.syncStartRangeDateFromProp(this.startDate);
    },
    endDate() {
      this.endDateText = this.endDate;
      this.syncEndRangeDateFromProp(this.endDate);
    },
  },
  methods: {
    setStartRangeDate(date: Date) {
      this.fromPickerSelectedDate = date;

      this.rangeChanged();
    },
    setEndRangeDate(date: Date) {
      this.toPickerSelectedDate = date;

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
    getNewHoveredDate(date: Date, $cell: JQuery): Date|null {
      if ($cell.hasClass('ui-datepicker-unselectable')) {
        return null;
      }

      return date;
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
    syncStartRangeDateFromProp(dateStr?: string) {
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
        this.fromPickerSelectedDate = startDateParsed;
        this.startDateInvalid = false;
      }
    },
    setStartRangeDateFromStr(dateStr?: string) {
      this.syncStartRangeDateFromProp(dateStr);
      if (!this.startDateInvalid) {
        this.rangeChanged();
      }
    },
    syncEndRangeDateFromProp(dateStr?: string) {
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
        this.toPickerSelectedDate = endDateParsed;
        this.endDateInvalid = false;
      }
    },
    setEndRangeDateFromStr(dateStr?: string) {
      this.syncEndRangeDateFromProp(dateStr);
      if (!this.endDateInvalid) {
        this.rangeChanged();
      }
    },
    rangeChanged() {
      this.$emit('rangeChange', {
        start: this.fromPickerSelectedDate ? format(this.fromPickerSelectedDate) : null,
        end: this.toPickerSelectedDate ? format(this.toPickerSelectedDate) : null,
      });
    },
  },
});
</script>
