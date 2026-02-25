<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="periodSelector piwikSelector"
    :class="{'periodSelector-withPrevNext': canShowMovePeriod}"
    v-expand-on-click="{
      expander: 'title',
      onExpand: onExpand,
      onClosed: onClosed,
    }"
  >
    <button
      v-if="canShowMovePeriod"
      class="move-period move-period-prev"
      @click="movePeriod(-1)"
      :disabled="isPeriodMoveDisabled(-1)"
    >
      <span class="icon-chevron-left"></span>
    </button>

    <button
      ref="title"
      id="date"
      class="title"
      tabindex="4"
      v-tooltips
      :title="translate('General_ChooseDate', currentlyViewingText)"
    >
      <span class="icon icon-calendar" />
      {{ currentlyViewingText }}
    </button>

    <div
      id="periodMore"
      class="dropdown"
      :class="selectedPeriod === 'range' ? 'dual-calendar' : 'single-calendar'"
    >
      <div class="flex">
        <div class="period-type">
          <h6>{{ translate('General_Period') }}</h6>
          <div id="otherPeriods">
            <PeriodOptions
              v-model="selectedPeriod"
              :periods="periodsFiltered"
              :checked-period-id="uiSelection.type === 'period' ? uiSelection.id : null"
              :active-date-period="periodValue"
              @select="onPeriodOptionSelected($event)"
              @dblclick="onPeriodOptionDblClick($event)"
            />
            <PresetDateRanges
              v-model="activePresetId"
              :checked-preset-id="uiSelection.type === 'preset' ? uiSelection.id : null"
              :allowed-periods="periodsFiltered"
              :min-date="piwikMinDate"
              :max-date="piwikMaxDate"
              @select="onPresetDateRangeSelected($event)"
            />
          </div>
        </div>
        <div>
          <div @click.capture="onRangePresetDateCellClickCapture($event)">
            <DateRangePicker
              v-show="calendarViewport === 'range'"
              class="period-range"
              :start-date="displayRangeStartDate"
              :end-date="displayRangeEndDate"
              @range-change="onRangeChange($event.start, $event.end)"
              @submit="onApplyClicked()"
            >
            </DateRangePicker>
          </div>
          <div
            class="period-date"
            v-show="calendarViewport === 'single'"
          >
            <PeriodDatePicker
              id="datepicker"
              :period="singleCalendarPeriod"
              :date="singleCalendarDate"
              @select="onDatePickerSelected($event.date)"
            >
            </PeriodDatePicker>
          </div>
          <div
            class="compare-checkbox"
            v-if="isComparisonEnabled"
          >
            <label>
              <input
                id="comparePeriodTo"
                type="checkbox"
                v-model="isComparing"
              />
              <span>{{ translate('General_CompareTo') }}</span>
            </label>
            <div id="comparePeriodToDropdown">
              <Field
                v-model="comparePeriodType"
                :style="{'visibility': isComparing ? 'visible' : 'hidden'}"
                :name="'comparePeriodToDropdown'"
                :uicontrol="'select'"
                :options="comparePeriodDropdownOptions"
                :full-width="true"
                :disabled="!isComparing"
              />
            </div>
          </div>
          <div
            class="compare-date-range"
            v-if="isComparing && comparePeriodType === 'custom'"
          >
            <div>
              <div id="comparePeriodStartDate">
                <div>
                  <Field
                    v-model="compareStartDate"
                    :name="'comparePeriodStartDate'"
                    :uicontrol="'text'"
                    :full-width="true"
                    :title="translate('CoreHome_StartDate')"
                    :placeholder="'YYYY-MM-DD'"
                  />
                </div>
              </div>
              <span class="compare-dates-separator" />
              <div id="comparePeriodEndDate">
                <div>
                  <Field
                    v-model="compareEndDate"
                    :name="'comparePeriodEndDate'"
                    :uicontrol="'text'"
                    :full-width="true"
                    :title="translate('CoreHome_EndDate')"
                    :placeholder="'YYYY-MM-DD'"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="apply-button-container"
      >
        <input
          type="submit"
          id="calendarApply"
          class="btn"
          @click="onApplyClicked()"
          :disabled="!isApplyEnabled()"
          :value="translate('General_Apply')"
        />
      </div>
      <div
        id="ajaxLoadingCalendar"
        v-if="isLoadingNewPage"
      >
        <ActivityIndicator
          :loading="true"
        />
        <div class="loadingSegment">
          {{ translate('SegmentEditor_LoadingSegmentedDataMayTakeSomeTime') }}
        </div>
      </div>
    </div>
    <button
      v-if="canShowMovePeriod"
      class="move-period move-period-next"
      @click="movePeriod(1)"
      :disabled="isPeriodMoveDisabled(1)"
    >
      <span class="icon-chevron-right"></span>
    </button>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ExpandOnClick from '../ExpandOnClick/ExpandOnClick';
import DateRangePicker from '../DateRangePicker/DateRangePicker.vue';
import PeriodDatePicker from '../PeriodDatePicker/PeriodDatePicker.vue';
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import Matomo from '../Matomo/Matomo';
import { translate } from '../translate';
import ComparisonsStore from '../Comparisons/Comparisons.store.instance';
import useExternalPluginComponent from '../useExternalPluginComponent';
import {
  Periods,
  parseDate,
  Range,
  format,
  datesAreInTheSamePeriod,
} from '../Periods';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import Tooltips from '../Tooltips/Tooltips';
import PresetDateRanges from './PresetDateRanges.vue';
import PeriodOptions from './PeriodOptions.vue';
import type {
  PresetDateRangeId,
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';

const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

const NBSP = Matomo.helper.htmlDecode('&nbsp;');

const COMPARE_PERIOD_TYPES = ['custom', 'previousPeriod', 'previousYear'];

const COMPARE_PERIOD_OPTIONS = [
  { key: 'custom', value: translate('General_Custom') },
  {
    key: 'previousPeriod',
    value: translate('General_PreviousPeriod').replace(/\s+/, NBSP),
  },
  {
    key: 'previousYear',
    value: translate('General_PreviousYear').replace(/\s+/, NBSP),
  },
];

// the date when the site was created
const piwikMinDate = new Date(Matomo.minDateYear, Matomo.minDateMonth - 1, Matomo.minDateDay);
// today/now
const piwikMaxDate = new Date(Matomo.maxDateYear, Matomo.maxDateMonth - 1, Matomo.maxDateDay);
const RANGE_PERIOD = 'range';
const CONTEXT_KEY_IGNORED_PARAMS = ['date', 'period', 'comparePeriods', 'comparePeriodType', 'compareDates', 'compareSegments'];

type UiSelection = { type: 'period'; id: string } | { type: 'preset'; id: PresetDateRangeId };
type InteractionSource = 'period' | 'preset' | 'calendar' | 'range' | null;
type SingleCalendarPeriod = 'day' | 'week' | 'month' | 'year';
type CalendarViewport = 'single' | 'range';

function isValidDate(d: any) { // eslint-disable-line @typescript-eslint/no-explicit-any
  if (Object.prototype.toString.call(d) !== '[object Date]') {
    return false;
  }

  return !Number.isNaN(d.getTime());
}

function isSingleCalendarPeriod(period: string): period is SingleCalendarPeriod {
  return period === 'day'
    || period === 'week'
    || period === 'month'
    || period === 'year';
}

interface PeriodSelectorState {
  uiSelection: UiSelection;
  lastInteractionSource: InteractionSource;
  nextHashUiSelection: UiSelection|null;
  nextHashSelectionKey: string|null;
  lastKnownHashSelectionKey: string|null;
  lastKnownHashContextKey: string|null;
  piwikMinDate: Date;
  piwikMaxDate: Date;
  comparePeriodDropdownOptions: typeof COMPARE_PERIOD_OPTIONS;
  activePresetId: PresetDateRangeId|null;
  pendingPresetSelection: PresetDateRangeSelection|null;
  periodValue: string;
  dateValue: Date|null;
  selectedPeriod: string;
  calendarViewport: CalendarViewport;
  singleCalendarPeriod: SingleCalendarPeriod;
  singleCalendarDate: Date|null;
  startRangeDate: string|null;
  endRangeDate: string|null;
  stagedRangeStartDate: string|null;
  stagedRangeEndDate: string|null;
  isRangeValid: boolean|null;
  isLoadingNewPage: boolean;
  isComparing: null|boolean;
  comparePeriodType: string;
  compareStartDate: string;
  compareEndDate: string;
  compareAppliedSignature: string;
}

export default defineComponent({
  props: {
    periods: Array,
  },
  components: {
    DateRangePicker,
    PeriodDatePicker,
    PresetDateRanges,
    PeriodOptions,
    Field,
    ActivityIndicator,
  },
  directives: {
    ExpandOnClick,
    Tooltips,
  },
  data(): PeriodSelectorState {
    const selectedPeriod = MatomoUrl.parsed.value.period as string;
    const initialSinglePeriod = isSingleCalendarPeriod(selectedPeriod)
      ? selectedPeriod
      : 'day';
    return {
      uiSelection: { type: 'period', id: selectedPeriod },
      lastInteractionSource: null,
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      piwikMinDate,
      piwikMaxDate,
      comparePeriodDropdownOptions: COMPARE_PERIOD_OPTIONS,
      activePresetId: null,
      pendingPresetSelection: null,
      periodValue: selectedPeriod,
      dateValue: null,
      selectedPeriod,
      calendarViewport: selectedPeriod === RANGE_PERIOD ? 'range' : 'single',
      singleCalendarPeriod: initialSinglePeriod,
      singleCalendarDate: null,
      startRangeDate: null,
      endRangeDate: null,
      stagedRangeStartDate: null,
      stagedRangeEndDate: null,
      isRangeValid: null,
      isLoadingNewPage: false,
      isComparing: null,
      comparePeriodType: 'previousPeriod',
      compareStartDate: '',
      compareEndDate: '',
      compareAppliedSignature: '',
    };
  },
  mounted() {
    Matomo.on('hidePeriodSelector', () => {
      window.$(this.$refs.root as HTMLElement).parent('#periodString').hide();
    });

    // some widgets might hide the period selector using the event above, so ensure it's
    // shown again when switching the page
    Matomo.on('matomoPageChange', () => {
      window.$(this.$refs.root as HTMLElement).parent('#periodString').show();
    });

    window.initTopControls(); // must be called when a top control changes width

    this.handleZIndexPositionRelativeCompareDropdownIssue();
  },
  computed: {
    matomoParsed() {
      return MatomoUrl.parsed.value;
    },
    isComparingStoreValue() {
      return ComparisonsStore.isComparingPeriods();
    },
    periodComparisonsStoreValue() {
      return ComparisonsStore.getPeriodComparisons();
    },
    currentlyViewingText() {
      let date;
      if (this.periodValue === 'range') {
        if (!this.startRangeDate || !this.endRangeDate) {
          return translate('General_Error');
        }

        date = `${this.startRangeDate},${this.endRangeDate}`;
      } else {
        if (!this.dateValue) {
          return translate('General_Error');
        }

        date = format(this.dateValue);
      }

      try {
        return Periods.parse(this.periodValue!, date).getPrettyString();
      } catch (e) {
        return translate('General_Error');
      }
    },
    isComparisonEnabled() {
      return ComparisonsStore.isComparisonEnabled();
    },
    periodsFiltered() {
      return (this.periods as string[] || []).filter(
        (periodLabel) => Periods.isRecognizedPeriod(periodLabel),
      );
    },
    selectedComparisonParams() {
      if (!this.isComparing) {
        return {};
      }

      if (this.comparePeriodType === 'custom') {
        return {
          comparePeriods: ['range'],
          comparePeriodType: 'custom',
          compareDates: [`${this.compareStartDate},${this.compareEndDate}`],
        };
      }

      if (this.comparePeriodType === 'previousPeriod') {
        return {
          comparePeriods: [this.selectedPeriod],
          comparePeriodType: 'previousPeriod',
          compareDates: [this.previousPeriodDateToSelectedPeriod],
        };
      }

      if (this.comparePeriodType === 'previousYear') {
        const dateStr = this.selectedPeriod === 'range'
          ? `${this.startRangeDate},${this.endRangeDate}`
          : format(this.dateValue!);

        const currentDateRange = Periods.parse(
          this.selectedPeriod as string,
          dateStr,
        ).getDateRange();
        currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
        currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);

        if (this.selectedPeriod === 'range') {
          return {
            comparePeriods: ['range'],
            comparePeriodType: 'previousYear',
            compareDates: [`${format(currentDateRange[0])},${format(currentDateRange[1])}`],
          };
        }

        return {
          comparePeriods: [this.selectedPeriod],
          comparePeriodType: 'previousYear',
          compareDates: [format(currentDateRange[0])],
        };
      }

      console.warn(`Unknown compare period type: ${this.comparePeriodType}`);
      return {};
    },
    previousPeriodDateToSelectedPeriod() {
      if (this.selectedPeriod === 'range') {
        const currentStartRange = parseDate(this.startRangeDate!);
        const currentEndRange = parseDate(this.endRangeDate!);
        const newEndDate = Range.getLastNRange('day', 2, currentStartRange).startDate;

        const rangeSize = Math.floor(
          (currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000,
        );
        const newRange = Range.getLastNRange('day', 1 + rangeSize, newEndDate);

        return `${format(newRange.startDate)},${format(newRange.endDate)}`;
      }

      const newStartDate = Range.getLastNRange(this.selectedPeriod, 2, this.dateValue!).startDate;
      return format(newStartDate);
    },
    selectedDateString() {
      if (this.selectedPeriod === 'range') {
        const dateFrom = this.startRangeDate!;
        const dateTo = this.endRangeDate!;
        const oDateFrom = parseDate(dateFrom);
        const oDateTo = parseDate(dateTo);

        if (!isValidDate(oDateFrom)
          || !isValidDate(oDateTo)
          || oDateFrom > oDateTo
        ) {
          // TODO: use a notification instead?
          window.$('#alert')
            .find('h2')
            .text(translate('General_InvalidDateRange'));
          Matomo.helper.modalConfirm('#alert', {});
          return null;
        }

        return `${dateFrom},${dateTo}`;
      }

      return format(this.dateValue!);
    },
    isErrorDisplayed() {
      return this.currentlyViewingText === translate('General_Error');
    },
    isRangeSelection() {
      return this.periodValue === 'range';
    },
    canShowMovePeriod() {
      return !this.isRangeSelection && !this.isErrorDisplayed;
    },
    compareCurrentSignature() {
      return JSON.stringify({
        isComparing: !!this.isComparing,
        comparePeriodType: this.comparePeriodType || '',
        compareStartDate: this.compareStartDate || '',
        compareEndDate: this.compareEndDate || '',
      });
    },
    isCompareDirty() {
      return this.compareCurrentSignature !== this.compareAppliedSignature;
    },
    hasPendingNonRangePeriodChange() {
      return this.uiSelection.type === 'period'
        && this.lastInteractionSource === 'period'
        && this.selectedPeriod !== RANGE_PERIOD
        && this.selectedPeriod !== this.periodValue;
    },
    isRangePresetSelection() {
      return this.uiSelection.type === 'preset'
        && this.selectedPeriod === RANGE_PERIOD;
    },
    displayRangeStartDate() {
      if (this.isRangePresetSelection && this.stagedRangeStartDate) {
        return this.stagedRangeStartDate;
      }

      return this.startRangeDate;
    },
    displayRangeEndDate() {
      if (this.isRangePresetSelection && this.stagedRangeEndDate) {
        return this.stagedRangeEndDate;
      }

      return this.endRangeDate;
    },
  },
  watch: {
    isComparingStoreValue: {
      immediate: true,
      handler(newVal: boolean) {
        this.isComparing = newVal;
      },
    },
    matomoParsed: {
      immediate: true,
      handler() {
        this.updateSelectedValuesFromHash();
      },
    },
    periodComparisonsStoreValue: {
      immediate: true,
      handler() {
        this.updateComparisonValuesFromStore();
        this.compareAppliedSignature = this.compareCurrentSignature;
      },
    },
  },
  methods: {
    onExpand(event: MouseEvent|KeyboardEvent) {
      const isKeyboardEvent = event.detail === 0;
      if (isKeyboardEvent) {
        window.$(this.$refs.root as HTMLElement).find('.ui-datepicker-month').focus();
      }
    },
    onClosed(event: MouseEvent|KeyboardEvent) {
      const isKeyboardEvent = event.detail === 0;
      if (isKeyboardEvent) {
        window.$(this.$refs.title as HTMLElement).focus();
      }
    },
    handleZIndexPositionRelativeCompareDropdownIssue() {
      const $element = window.$(this.$refs.root as HTMLElement);
      $element.on('focus', '#comparePeriodToDropdown .select-dropdown', () => {
        $element.addClass('compare-dropdown-open');
      }).on('blur', '#comparePeriodToDropdown .select-dropdown', () => {
        $element.removeClass('compare-dropdown-open');
      });
    },
    setUiSelection(selection: UiSelection, source: InteractionSource) {
      this.uiSelection = selection;
      this.lastInteractionSource = source;
    },
    clearPresetSelection() {
      this.activePresetId = null;
      this.pendingPresetSelection = null;
      this.stagedRangeStartDate = null;
      this.stagedRangeEndDate = null;
    },
    setPendingPeriodAndDate(period: string, date: Date) {
      this.periodValue = period;
      this.selectedPeriod = period;
      this.dateValue = date;
      this.setRangeStartEndFromPeriod(period, format(date));
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
        this.singleCalendarDate = date;
      }
    },
    setPiwikPeriodAndDate(period: string, date: Date) {
      this.setPendingPeriodAndDate(period, date);
      this.setUiSelection({ type: 'period', id: period }, 'period');

      const currentDateString = format(date);
      this.clearPresetSelection();
      this.commitSelectionToUrl(currentDateString, this.selectedPeriod);
    },
    setPendingCalendarSelection(period: string, date: Date) {
      this.setPendingPeriodAndDate(period, date);
    },
    commitSelectionToUrl(date: string, period: string) {
      this.nextHashUiSelection = { ...this.uiSelection };
      this.nextHashSelectionKey = this.getSelectionKey(period, date);
      this.compareAppliedSignature = this.compareCurrentSignature;
      this.propagateNewUrlParams(date, period);

      window.initTopControls();
    },
    onPeriodOptionSelected(payload: { period: string }) {
      this.setUiSelection({ type: 'period', id: payload.period }, 'period');
      this.selectedPeriod = payload.period;
      // Selecting a period option exits preset ownership and discards any unapplied preset staging.
      // After this point, Apply commits period-owned state only.
      this.clearPresetSelection();
      if (payload.period === RANGE_PERIOD) {
        this.calendarViewport = 'range';
        return;
      }

      this.calendarViewport = 'single';
      if (isSingleCalendarPeriod(payload.period)) {
        this.singleCalendarPeriod = payload.period;
      }
      this.singleCalendarDate = null;
    },
    onPeriodOptionDblClick(payload: { period: string }) {
      this.onPeriodOptionSelected(payload);
      if (payload.period === RANGE_PERIOD
        || payload.period === this.periodValue
        || !this.dateValue
      ) {
        return;
      }

      this.setPiwikPeriodAndDate(payload.period, this.dateValue);
    },
    canInteractWithSingleCalendar(): boolean {
      // Preset-owned selections are intentionally read-only for calendar interactions.
      // Users must switch ownership via period options before single-calendar clicks can commit.
      return this.calendarViewport === 'single'
        && this.uiSelection.type === 'period'
        && this.selectedPeriod !== RANGE_PERIOD;
    },
    onDatePickerSelected(date: Date) {
      if (!this.canInteractWithSingleCalendar()) {
        return;
      }

      this.setUiSelection({ type: 'period', id: this.selectedPeriod }, 'calendar');
      this.setPendingCalendarSelection(this.selectedPeriod, date);
      this.clearPresetSelection();
      this.commitSelectionToUrl(format(date), this.selectedPeriod);
    },
    onPresetDateRangeSelected(selection: PresetDateRangeSelection) {
      if (!this.periodsFiltered.includes(selection.period)) {
        return;
      }

      this.setUiSelection({ type: 'preset', id: selection.id }, 'preset');
      this.activePresetId = selection.id;
      this.selectedPeriod = selection.period;
      this.isRangeValid = true;
      this.pendingPresetSelection = selection;
      if (selection.period === RANGE_PERIOD) {
        this.stagedRangeStartDate = format(selection.startDate);
        this.stagedRangeEndDate = format(selection.endDate);
        this.calendarViewport = 'range';
        return;
      }

      this.stagedRangeStartDate = null;
      this.stagedRangeEndDate = null;
      this.calendarViewport = 'single';
      this.singleCalendarDate = selection.startDate;
      if (isSingleCalendarPeriod(selection.period)) {
        this.singleCalendarPeriod = selection.period;
      }
    },
    propagateNewUrlParams(date: string, period: string) {
      const compareParams = this.selectedComparisonParams;

      let baseParams: Record<string, unknown>;
      if (Matomo.helper.isReportingPage()) {
        this.closePeriodSelector();
        baseParams = MatomoUrl.hashParsed.value;
      } else {
        this.isLoadingNewPage = true;
        baseParams = MatomoUrl.parsed.value;
      }

      // get params without comparePeriods/comparePeriodType/compareDates
      const paramsWithoutCompare = { ...baseParams };
      delete paramsWithoutCompare.comparePeriods;
      delete paramsWithoutCompare.comparePeriodType;
      delete paramsWithoutCompare.compareDates;

      MatomoUrl.updateLocation({
        ...paramsWithoutCompare,
        date,
        period,
        ...compareParams,
      });
    },
    onApplyClicked() {
      if (this.pendingPresetSelection
        && this.uiSelection.type === 'preset'
        && this.pendingPresetSelection.id === this.uiSelection.id
      ) {
        this.periodValue = this.pendingPresetSelection.period;
        this.dateValue = this.pendingPresetSelection.startDate;
        this.startRangeDate = format(this.pendingPresetSelection.startDate);
        this.endRangeDate = format(this.pendingPresetSelection.endDate);
        // Keep relative preset tokens in the URL (for example, "last7") so bookmarks stay rolling.
        // Staged start/end dates can be clamped for current UI bounds,
        // but URL semantics stay relative.
        this.commitSelectionToUrl(
          this.pendingPresetSelection.date,
          this.pendingPresetSelection.period,
        );
        return;
      }

      if (this.selectedPeriod === RANGE_PERIOD) {
        const dateString = this.selectedDateString;
        if (!dateString) {
          return;
        }

        this.periodValue = RANGE_PERIOD;
        this.commitSelectionToUrl(dateString, RANGE_PERIOD);
        return;
      }

      if (!this.isCompareDirty || this.hasPendingNonRangePeriodChange) {
        return;
      }

      if (this.periodValue === RANGE_PERIOD) {
        if (!this.startRangeDate || !this.endRangeDate) {
          return;
        }

        this.commitSelectionToUrl(`${this.startRangeDate},${this.endRangeDate}`, RANGE_PERIOD);
        return;
      }

      if (!this.dateValue) {
        return;
      }

      this.commitSelectionToUrl(format(this.dateValue), this.periodValue);
    },
    updateComparisonValuesFromStore() {
      this.comparePeriodType = 'previousPeriod';
      this.compareStartDate = '';
      this.compareEndDate = '';

      // first is selected period, second is period to compare to
      const comparePeriods = ComparisonsStore.getPeriodComparisons();

      if (comparePeriods.length < 2) {
        return;
      }

      const comparePeriodType = MatomoUrl.parsed.value.comparePeriodType as string;

      if (!COMPARE_PERIOD_TYPES.includes(comparePeriodType)) {
        return;
      }

      this.comparePeriodType = comparePeriodType;

      if (this.comparePeriodType !== 'custom' || comparePeriods[1].params.period !== 'range') {
        return;
      }

      let periodObj;

      try {
        periodObj = Periods.parse(
          comparePeriods[1].params.period,
          comparePeriods[1].params.date,
        ) as Range;
      } catch {
        return;
      }

      const [startDate, endDate] = periodObj.getDateRange();

      this.compareStartDate = format(startDate);
      this.compareEndDate = format(endDate);
    },
    getContextKeyFromParsed(parsed: Record<string, unknown>): string {
      const normalizedContext: Record<string, unknown> = {};
      Object.keys(parsed)
        .filter((key) => !CONTEXT_KEY_IGNORED_PARAMS.includes(key))
        .sort()
        .forEach((key) => {
          normalizedContext[key] = parsed[key];
        });
      return JSON.stringify(normalizedContext);
    },
    getCurrentContextKey(): string {
      return this.getContextKeyFromParsed(MatomoUrl.parsed.value as Record<string, unknown>);
    },
    shouldSkipHashSync(currentSelectionKey: string, currentContextKey: string): boolean {
      return !this.nextHashUiSelection
        && currentSelectionKey === this.lastKnownHashSelectionKey
        && currentContextKey === this.lastKnownHashContextKey;
    },
    resolveSyncedUiSelection(
      currentSelectionKey: string,
      currentContextKey: string,
    ): UiSelection|null {
      const syncedUiSelection = this.nextHashUiSelection
        && this.nextHashSelectionKey === currentSelectionKey
        ? { ...this.nextHashUiSelection }
        : null;

      this.nextHashUiSelection = null;
      this.nextHashSelectionKey = null;
      this.lastInteractionSource = null;
      this.lastKnownHashSelectionKey = currentSelectionKey;
      this.lastKnownHashContextKey = currentContextKey;

      return syncedUiSelection;
    },
    applyUiSelectionFromHash(period: string, syncedUiSelection: UiSelection|null) {
      if (syncedUiSelection) {
        this.uiSelection = syncedUiSelection;
        this.activePresetId = syncedUiSelection.type === 'preset'
          ? syncedUiSelection.id
          : null;
        return;
      }

      this.setUiSelection({ type: 'period', id: period }, null);
      this.clearPresetSelection();
    },
    resetSelectedDateValues() {
      this.dateValue = null;
      this.startRangeDate = null;
      this.endRangeDate = null;
    },
    applyDateValuesFromHash(period: string, date: string) {
      if (period === RANGE_PERIOD) {
        const periodObj = Periods.get(period).parse(date) as Range;
        const [startDate, endDate] = periodObj.getDateRange();
        this.dateValue = startDate;
        this.startRangeDate = format(startDate);
        this.endRangeDate = format(endDate);
        return;
      }

      this.dateValue = parseDate(date);
      this.setRangeStartEndFromPeriod(period, date);
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
      }
      this.singleCalendarDate = this.dateValue;
    },
    updateSelectedValuesFromHash() {
      const date = (MatomoUrl.parsed.value.date as string) || '';
      const period = (MatomoUrl.parsed.value.period as string) || '';
      const currentSelectionKey = this.getSelectionKey(period, date);
      const currentContextKey = this.getCurrentContextKey();
      if (this.shouldSkipHashSync(currentSelectionKey, currentContextKey)) {
        return;
      }

      const syncedUiSelection = this.resolveSyncedUiSelection(
        currentSelectionKey,
        currentContextKey,
      );
      this.applyUiSelectionFromHash(period, syncedUiSelection);
      this.periodValue = period;
      this.selectedPeriod = period;
      this.resetSelectedDateValues();

      try {
        Periods.parse(period, date);
      } catch (e) {
        return;
      }

      this.applyDateValuesFromHash(period, date);
      this.pendingPresetSelection = null;
      this.stagedRangeStartDate = null;
      this.stagedRangeEndDate = null;
      this.calendarViewport = period === RANGE_PERIOD ? 'range' : 'single';
      this.compareAppliedSignature = this.compareCurrentSignature;
    },
    setRangeStartEndFromPeriod(period: string, dateStr: string) {
      const dateRange = Periods.parse(period, dateStr).getDateRange();
      this.startRangeDate = format(dateRange[0] < piwikMinDate ? piwikMinDate : dateRange[0]);
      this.endRangeDate = format(dateRange[1] > piwikMaxDate ? piwikMaxDate : dateRange[1]);
    },
    getSelectionKey(period: string, date: string) {
      return `${period}|${date}`;
    },
    canInteractWithRangeCalendar(): boolean {
      return this.calendarViewport === 'range'
        && this.uiSelection.type === 'period'
        && this.selectedPeriod === RANGE_PERIOD;
    },
    onRangeChange(start: string, end: string) {
      if (!this.canInteractWithRangeCalendar()) {
        return;
      }

      if (!start || !end) {
        this.isRangeValid = false;
        return;
      }

      this.isRangeValid = true;
      this.startRangeDate = start;
      this.endRangeDate = end;
      this.setUiSelection({ type: 'period', id: RANGE_PERIOD }, 'range');
    },
    onRangePresetDateCellClickCapture(event: MouseEvent) {
      if (!this.isRangePresetSelection) {
        return;
      }

      const target = event.target as HTMLElement | null;
      if (!target) {
        return;
      }

      if (target.closest('.ui-datepicker-calendar a')) {
        event.preventDefault();
        event.stopPropagation();
      }
    },
    isApplyEnabled() {
      if (this.selectedPeriod === RANGE_PERIOD
        && !this.pendingPresetSelection
        && !this.isRangeValid
      ) {
        return false;
      }

      if (this.isComparing
        && this.comparePeriodType === 'custom'
        && !this.isCompareRangeValid()
      ) {
        return false;
      }

      return true;
    },
    closePeriodSelector() {
      (this.$refs.root as HTMLElement).classList.remove('expanded');
    },
    isCompareRangeValid() {
      try {
        parseDate(this.compareStartDate);
      } catch (e) {
        return false;
      }

      try {
        parseDate(this.compareEndDate);
      } catch (e) {
        return false;
      }

      return true;
    },
    movePeriod(direction: number) {
      if (!this.canMovePeriod(direction)) {
        return;
      }

      const newDate = this.dateValue != null
        ? new Date(this.dateValue.getTime())
        : new Date();

      switch (this.periodValue) {
        case 'day':
          newDate.setDate(newDate.getDate() + direction);
          break;
        case 'week':
          newDate.setDate(newDate.getDate() + direction * 7);
          break;
        case 'month':
          newDate.setMonth(newDate.getMonth() + direction);
          break;
        case 'year':
          newDate.setFullYear(newDate.getFullYear() + direction);
          break;
        default:
          break;
      }

      // Ensure the date is not outside the min and max dates
      if (newDate < piwikMinDate) {
        newDate.setTime(piwikMinDate.getTime());
      }
      if (newDate > piwikMaxDate) {
        newDate.setTime(piwikMaxDate.getTime());
      }

      this.setPiwikPeriodAndDate(this.periodValue, newDate);
    },
    isPeriodMoveDisabled(direction: number) {
      // disable period move when date range is used or when we would go out of the min/max dates
      if (this.dateValue === null) {
        return this.isRangeSelection;
      }
      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction: number) {
      if (this.dateValue === null) {
        return false;
      }
      const boundaryDate = (direction === -1) ? piwikMinDate : piwikMaxDate;
      return !datesAreInTheSamePeriod(this.dateValue!, boundaryDate, this.periodValue);
    },
  },
});
</script>
