<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root"/>
</template>

<script lang="ts">
import {
  defineComponent,
  ref,
  watch,
  onMounted,
} from 'vue';
import Matomo from '../Matomo/Matomo';
import { parseDate } from '../Periods';

const DEFAULT_STEP_MONTHS = 1;

const { $ } = window;

export default defineComponent({
  props: {
    selectedDateStart: Date,
    selectedDateEnd: Date,
    highlightedDateStart: Date,
    highlightedDateEnd: Date,
    viewDate: [String, Date],
    stepMonths: Number,
    disableMonthDropdown: Boolean,
    options: Object,
  },
  emits: ['cellHover', 'cellHoverLeave', 'dateSelect'],
  setup(props, context) {
    const root = ref<HTMLElement|null>(null);

    function setDateCellColor($dateCell: JQuery, dateValue: Date): void {
      const $dateCellLink = $dateCell.children('a');

      if (props.selectedDateStart
        && props.selectedDateEnd
        && dateValue >= props.selectedDateStart
        && dateValue <= props.selectedDateEnd
      ) {
        $dateCell.addClass('ui-datepicker-current-period');
      } else {
        $dateCell.removeClass('ui-datepicker-current-period');
      }

      if (props.highlightedDateStart
        && props.highlightedDateEnd
        && dateValue >= props.highlightedDateStart
        && dateValue <= props.highlightedDateEnd
      ) {
        // other-month cells don't have links, so the <td> must have the ui-state-hover class
        const elementToAddClassTo = $dateCellLink.length ? $dateCellLink : $dateCell;
        elementToAddClassTo.addClass('ui-state-hover');
      } else {
        $dateCell.removeClass('ui-state-hover');
        $dateCellLink.removeClass('ui-state-hover');
      }
    }

    function getCellDate($dateCell: JQuery, month: number, year: number): Date {
      if ($dateCell.hasClass('ui-datepicker-other-month')) {
        return getOtherMonthDate($dateCell, month, year); // eslint-disable-line
      }

      const day = parseInt($dateCell.children('a,span').text(), 10);

      return new Date(year, month, day);
    }

    function getOtherMonthDate($dateCell: JQuery, month: number, year: number) {
      let date;

      const $row = $dateCell.parent();
      const $rowCells = $row.children('td');

      // if in the first row, the date cell is before the current month
      if ($row.is(':first-child')) {
        const $firstDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').first();

        date = getCellDate($firstDateInMonth, month, year);
        date.setDate($rowCells.index($dateCell) - $rowCells.index($firstDateInMonth) + 1);
        return date;
      }

      // the date cell is after the current month
      const $lastDateInMonth = $row.children('td:not(.ui-datepicker-other-month)').last();

      date = getCellDate($lastDateInMonth, month, year);
      date.setDate(date.getDate() + $rowCells.index($dateCell) - $rowCells.index($lastDateInMonth));
      return date;
    }

    function getMonthYearDisplayed(): number[] {
      const element = $(root.value!) as JQuery;

      const $firstCellWithMonth = element.find('td[data-month]');
      const month = parseInt($firstCellWithMonth.attr('data-month')!, 10);
      const year = parseInt($firstCellWithMonth.attr('data-year')!, 10);

      return [month, year];
    }

    function setDatePickerCellColors() {
      const element = $(root.value!);

      const $calendarTable = element.find('.ui-datepicker-calendar');

      const monthYear = getMonthYearDisplayed();

      // highlight the rest of the cells by first getting the date for the first cell
      // in the calendar, then just incrementing by one for the rest of the cells.
      const $cells = $calendarTable.find('td');
      const $firstDateCell = $cells.first();
      const currentDate = getCellDate($firstDateCell, monthYear[0], monthYear[1]);

      $cells.each(function setCellColor() {
        setDateCellColor($(this), currentDate);

        currentDate.setDate(currentDate.getDate() + 1);
      });
    }

    function viewDateChanged(): boolean {
      if (!props.viewDate) {
        return false;
      }

      let date: Date;
      if (typeof props.viewDate === 'string') {
        try {
          date = parseDate(props.viewDate);
        } catch (e) {
          return false;
        }
      } else {
        date = props.viewDate as Date;
      }

      const element = $(root.value!);

      // only change the datepicker date if the date is outside of the current month/year.
      // this avoids a re-render in other cases.
      const monthYear = getMonthYearDisplayed();
      if (monthYear[0] !== date.getMonth() || monthYear[1] !== date.getFullYear()) {
        element.datepicker('setDate', date);
        return true;
      }

      return false;
    }

    // remove the ui-state-active class & click handlers for every cell. we bypass
    // the datepicker's date selection logic for smoother browser rendering.
    function onJqueryUiRenderedPicker(): void {
      const element = $(root.value!);

      element.find('td[data-event]').off('click');
      element.find('.ui-state-active').removeClass('ui-state-active');
      element.find('.ui-datepicker-current-day').removeClass('ui-datepicker-current-day');

      // add href to left/right nav in calendar so they can be accessed via keyboard
      element.find('.ui-datepicker-prev,.ui-datepicker-next').attr('href', '');
    }

    function stepMonthsChanged(): boolean {
      const element = $(root.value!);

      const stepMonths = props.stepMonths || DEFAULT_STEP_MONTHS;
      if (element.datepicker('option', 'stepMonths') === stepMonths) {
        return false;
      }

      // setting stepMonths will change the month in view back to the selected date. to avoid
      // we set the selected date to the month in view.
      const currentMonth = $('.ui-datepicker-month', element).val() as number;
      const currentYear = $('.ui-datepicker-year', element).val() as number;

      element
        .datepicker('option', 'stepMonths', stepMonths)
        .datepicker('setDate', new Date(currentYear, currentMonth));

      onJqueryUiRenderedPicker();

      return true;
    }

    function enableDisableMonthDropdown(): void {
      const element = $(root.value!);
      const monthPicker = element.find('.ui-datepicker-month')[0] as HTMLInputElement;
      if (monthPicker) {
        monthPicker.disabled = props.disableMonthDropdown;
      }
    }

    function handleOtherMonthClick(this: HTMLElement) {
      if (!$(this).hasClass('ui-state-hover')) {
        return;
      }

      const $row = $(this).parent();
      const $tbody = $row.parent();

      if ($row.is(':first-child')) {
        // click on first of the month
        $tbody.find('a').first().click();
      } else {
        // click on last of month
        $tbody.find('a').last().click();
      }
    }

    function onCalendarViewChange() {
      // clicking left/right re-enables the month dropdown, so we disable it again
      enableDisableMonthDropdown();

      setDatePickerCellColors();
    }

    // on a prop change (NOTE: we can't watch just `props`, since then newProps and oldProps will
    // have the same values (since it is a proxy object). Using a copy doesn't quite work, the
    // object it returns will always be different, BUT, since we check what changes it works
    // for our purposes. The only downside is that it runs on every tick basically, but since
    // that is within the context of the date picker component, it's bearable.
    watch(() => ({ ...props }), (newProps: typeof props, oldProps: typeof props) => {
      let redraw = false;

      [
        (x: typeof props): Date|undefined => x.selectedDateStart,
        (x: typeof props): Date|undefined => x.selectedDateEnd,
        (x: typeof props): Date|undefined => x.highlightedDateStart,
        (x: typeof props): Date|undefined => x.highlightedDateEnd,
      ].forEach((selector) => {
        if (redraw) {
          return;
        }

        const newProp = selector(newProps);
        const oldProp = selector(oldProps);

        if (!newProp && oldProp) {
          redraw = true;
        }

        if (newProp && !oldProp) {
          redraw = true;
        }

        if (newProp
          && oldProp
          && newProp.getTime() !== oldProp.getTime()
        ) {
          redraw = true;
        }
      });

      if (newProps.viewDate !== oldProps.viewDate && viewDateChanged()) {
        redraw = true;
      }

      if (newProps.stepMonths !== oldProps.stepMonths) {
        stepMonthsChanged();
      }

      if (newProps.disableMonthDropdown !== oldProps.disableMonthDropdown) {
        enableDisableMonthDropdown();
      }

      // redraw when selected/highlighted dates change
      if (redraw) {
        setDatePickerCellColors();
      }
    });

    onMounted(() => {
      const element = $(root.value!);

      const customOptions = props.options || {};
      const datePickerOptions = {
        ...Matomo.getBaseDatePickerOptions(),
        ...customOptions,
        onChangeMonthYear: () => {
          // datepicker renders the HTML after this hook is called, so we use setTimeout
          // to run some code after the render.
          setTimeout(() => {
            onJqueryUiRenderedPicker();
          });
        },
      };
      element.datepicker(datePickerOptions);

      element.on('mouseover', 'tbody td a', (event) => {
        // this event is triggered when a user clicks a date as well. in that case,
        // the originalEvent is null. we don't need to redraw again for that, so
        // we ignore events like that.
        if (event.originalEvent) {
          setDatePickerCellColors();
        }
      });

      // on hover cell, execute scope.cellHover()
      element.on('mouseenter', 'tbody td', function onMouseEnter() {
        const monthYear = getMonthYearDisplayed();

        const $dateCell = $(this);
        const dateValue = getCellDate($dateCell, monthYear[0], monthYear[1]);
        context.emit('cellHover', { date: dateValue, $cell: $dateCell });
      });

      // overrides jquery UI handler that unhighlights a cell when the mouse leaves it
      element.on('mouseout', 'tbody td a', () => {
        setDatePickerCellColors();
      });

      // call scope.cellHoverLeave() when mouse leaves table body (can't do event on tbody, for
      // some reason that fails, so we do two events, one on the table & one on thead)
      element
        .on('mouseleave', 'table', () => context.emit('cellHoverLeave'))
        .on('mouseenter', 'thead', () => context.emit('cellHoverLeave'));

      // make sure whitespace is clickable when the period makes it appropriate
      element.on('click', 'tbody td.ui-datepicker-other-month', handleOtherMonthClick);

      // NOTE: using a selector w/ .on() doesn't seem to work for some reason...
      element.on('click', (e) => {
        e.preventDefault();

        const $target = $(e.target).closest('a');
        if (!$target.is('.ui-datepicker-next')
          && !$target.is('.ui-datepicker-prev')
        ) {
          return;
        }

        onCalendarViewChange();
      });

      // when a cell is clicked, invoke the onDateSelected function. this, in conjunction
      // with onJqueryUiRenderedPicker(), overrides the date picker's click behavior.
      element.on('click', 'td[data-month]', (event) => {
        const $cell = $(event.target).closest('td') as JQuery;
        const month = parseInt($cell.attr('data-month')!, 10);
        const year = parseInt($cell.attr('data-year')!, 10);
        const day = parseInt($cell.children('a,span').text(), 10);
        context.emit('dateSelect', { date: new Date(year, month, day) });
      });

      const renderPostProcessed = stepMonthsChanged();

      viewDateChanged();
      enableDisableMonthDropdown();

      if (!renderPostProcessed) {
        onJqueryUiRenderedPicker();
      }

      setDatePickerCellColors();
    });

    return {
      root,
    };
  },
});
</script>
