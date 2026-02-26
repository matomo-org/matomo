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
      :class="uiSelectedPeriod === 'range' ? 'dual-calendar' : 'single-calendar'"
    >
      <div class="flex">
        <div class="period-type period-selector-options-column">
          <h6>{{ translate('General_ChoosePeriod') }}</h6>
          <div id="otherPeriods">
            <PeriodOptions
              v-model="uiSelectedPeriod"
              :periods="periodsFiltered"
              :checked-period-id="uiSelection.type === 'period' ? uiSelection.id : null"
              :active-date-period="appliedPeriod"
              @select="onPeriodOptionSelected($event)"
              @dblclick="onPeriodOptionDblClick($event)"
            />
            <PresetDateRanges
              v-model="activePresetId"
              :checked-preset-id="uiSelection.type === 'preset' ? uiSelection.id : null"
              :allowed-periods="periodsFiltered"
              :min-date="minAllowedDate"
              :max-date="maxAllowedDate"
              @select="onPresetDateRangeSelected($event)"
            />
          </div>
        </div>
        <div class="period-selector-calendar-column">
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
              :date="singleCalendarSelectedDate"
              @select="onDatePickerSelected($event.date)"
            >
            </PeriodDatePicker>
          </div>
          <PeriodSelectorCompareControls
            :is-comparison-enabled="isComparisonEnabled"
            :is-comparing="isComparing"
            :compare-period-type="comparePeriodType"
            :compare-start-date="compareStartDate"
            :compare-end-date="compareEndDate"
            :compare-period-dropdown-options="comparePeriodDropdownOptions"
            @update:isComparing="isComparing = $event"
            @update:comparePeriodType="comparePeriodType = $event"
            @update:compareStartDate="compareStartDate = $event"
            @update:compareEndDate="compareEndDate = $event"
          />
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
        </div>
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
import PeriodSelectorCompareControls from './PeriodSelectorCompareControls.vue';
import type {
  PresetDateRangeId,
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';
import { getTokenPresetIdFromPeriodAndDate } from './PresetDateRangeResolver';
import {
  getContextKeyFromParsed,
  getSelectionKey,
  resolveSyncedUiSelection,
  shouldSkipHashSync,
} from './PeriodSelectorHashSync';
import type { UiSelection as HashSyncUiSelection } from './PeriodSelectorHashSync';

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
const siteMinAllowedDate = new Date(Matomo.minDateYear, Matomo.minDateMonth - 1, Matomo.minDateDay);
// today/now
const siteMaxAllowedDate = new Date(Matomo.maxDateYear, Matomo.maxDateMonth - 1, Matomo.maxDateDay);
const RANGE_PERIOD = 'range';

type InteractionSource = 'period' | 'preset' | 'calendar' | 'range' | null;
type SingleCalendarPeriod = 'day' | 'week' | 'month' | 'year';
type CalendarViewport = 'single' | 'range';
type UiSelection = HashSyncUiSelection<PresetDateRangeId>;

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
  minAllowedDate: Date;
  maxAllowedDate: Date;
  activePresetId: PresetDateRangeId|null;
  pendingPresetSelection: PresetDateRangeSelection|null;
  appliedPeriod: string;
  appliedAnchorDate: Date|null;
  uiSelectedPeriod: string;
  calendarViewport: CalendarViewport;
  singleCalendarPeriod: SingleCalendarPeriod;
  singleCalendarSelectedDate: Date|null;
  appliedRangeStartDate: string|null;
  appliedRangeEndDate: string|null;
  stagedPresetRangeStartDate: string|null;
  stagedPresetRangeEndDate: string|null;
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
    PeriodSelectorCompareControls,
    ActivityIndicator,
  },
  directives: {
    ExpandOnClick,
    Tooltips,
  },
  data(): PeriodSelectorState {
    const uiSelectedPeriod = MatomoUrl.parsed.value.period as string;
    const initialSinglePeriod = isSingleCalendarPeriod(uiSelectedPeriod)
      ? uiSelectedPeriod
      : 'day';
    return {
      uiSelection: { type: 'period', id: uiSelectedPeriod },
      lastInteractionSource: null,
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      minAllowedDate: siteMinAllowedDate,
      maxAllowedDate: siteMaxAllowedDate,
      activePresetId: null,
      pendingPresetSelection: null,
      appliedPeriod: uiSelectedPeriod,
      appliedAnchorDate: null,
      uiSelectedPeriod,
      calendarViewport: uiSelectedPeriod === RANGE_PERIOD ? 'range' : 'single',
      singleCalendarPeriod: initialSinglePeriod,
      singleCalendarSelectedDate: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
      stagedPresetRangeStartDate: null,
      stagedPresetRangeEndDate: null,
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
    comparePeriodDropdownOptions() {
      return COMPARE_PERIOD_OPTIONS;
    },
    currentlyViewingText() {
      let date;
      if (this.appliedPeriod === 'range') {
        if (!this.appliedRangeStartDate || !this.appliedRangeEndDate) {
          return translate('General_Error');
        }

        date = `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`;
      } else {
        if (!this.appliedAnchorDate) {
          return translate('General_Error');
        }

        date = format(this.appliedAnchorDate);
      }

      try {
        return Periods.parse(this.appliedPeriod!, date).getPrettyString();
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
          comparePeriods: [this.uiSelectedPeriod],
          comparePeriodType: 'previousPeriod',
          compareDates: [this.previousPeriodDateToSelectedPeriod],
        };
      }

      if (this.comparePeriodType === 'previousYear') {
        const dateStr = this.uiSelectedPeriod === 'range'
          ? `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`
          : format(this.appliedAnchorDate!);

        const currentDateRange = Periods.parse(
          this.uiSelectedPeriod as string,
          dateStr,
        ).getDateRange();
        currentDateRange[0].setFullYear(currentDateRange[0].getFullYear() - 1);
        currentDateRange[1].setFullYear(currentDateRange[1].getFullYear() - 1);

        if (this.uiSelectedPeriod === 'range') {
          return {
            comparePeriods: ['range'],
            comparePeriodType: 'previousYear',
            compareDates: [`${format(currentDateRange[0])},${format(currentDateRange[1])}`],
          };
        }

        return {
          comparePeriods: [this.uiSelectedPeriod],
          comparePeriodType: 'previousYear',
          compareDates: [format(currentDateRange[0])],
        };
      }

      console.warn(`Unknown compare period type: ${this.comparePeriodType}`);
      return {};
    },
    previousPeriodDateToSelectedPeriod() {
      if (this.uiSelectedPeriod === 'range') {
        const currentStartRange = parseDate(this.appliedRangeStartDate!);
        const currentEndRange = parseDate(this.appliedRangeEndDate!);
        const newEndDate = Range.getLastNRange('day', 2, currentStartRange).startDate;

        const rangeSize = Math.floor(
          (currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000,
        );
        const newRange = Range.getLastNRange('day', 1 + rangeSize, newEndDate);

        return `${format(newRange.startDate)},${format(newRange.endDate)}`;
      }

      const newStartDate = Range.getLastNRange(
        this.uiSelectedPeriod,
        2,
        this.appliedAnchorDate!,
      ).startDate;
      return format(newStartDate);
    },
    selectedDateString() {
      if (this.uiSelectedPeriod === 'range') {
        const dateFrom = this.appliedRangeStartDate!;
        const dateTo = this.appliedRangeEndDate!;
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

      return format(this.appliedAnchorDate!);
    },
    isErrorDisplayed() {
      return this.currentlyViewingText === translate('General_Error');
    },
    isRangeSelection() {
      return this.appliedPeriod === 'range';
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
        && this.uiSelectedPeriod !== RANGE_PERIOD
        && this.uiSelectedPeriod !== this.appliedPeriod;
    },
    isRangePresetSelection() {
      return this.uiSelection.type === 'preset'
        && this.uiSelectedPeriod === RANGE_PERIOD;
    },
    displayRangeStartDate() {
      if (this.isRangePresetSelection && this.stagedPresetRangeStartDate) {
        return this.stagedPresetRangeStartDate;
      }

      return this.appliedRangeStartDate;
    },
    displayRangeEndDate() {
      if (this.isRangePresetSelection && this.stagedPresetRangeEndDate) {
        return this.stagedPresetRangeEndDate;
      }

      return this.appliedRangeEndDate;
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
      this.stagedPresetRangeStartDate = null;
      this.stagedPresetRangeEndDate = null;
    },
    setPendingPeriodAndDate(period: string, date: Date) {
      this.appliedPeriod = period;
      this.uiSelectedPeriod = period;
      this.appliedAnchorDate = date;
      this.setRangeStartEndFromPeriod(period, format(date));
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
        this.singleCalendarSelectedDate = date;
      }
    },
    setPiwikPeriodAndDate(period: string, date: Date) {
      this.setPendingPeriodAndDate(period, date);
      this.setUiSelection({ type: 'period', id: period }, 'period');

      const currentDateString = format(date);
      this.clearPresetSelection();
      this.commitSelectionToUrl(currentDateString, this.uiSelectedPeriod);
    },
    commitSelectionToUrl(date: string, period: string) {
      this.nextHashUiSelection = { ...this.uiSelection };
      this.nextHashSelectionKey = getSelectionKey(period, date);
      this.compareAppliedSignature = this.compareCurrentSignature;
      this.propagateNewUrlParams(date, period);

      window.initTopControls();
    },
    onPeriodOptionSelected(payload: { period: string }) {
      this.setUiSelection({ type: 'period', id: payload.period }, 'period');
      this.uiSelectedPeriod = payload.period;
      // Selecting a period option exits preset ownership and discards any unapplied preset staging.
      // After this point, Apply commits period-owned state only.
      this.clearPresetSelection();
      if (payload.period === RANGE_PERIOD) {
        this.calendarViewport = 'range';
        this.isRangeValid = true;
        return;
      }

      this.calendarViewport = 'single';
      if (isSingleCalendarPeriod(payload.period)) {
        this.singleCalendarPeriod = payload.period;
      }
      this.singleCalendarSelectedDate = null;
    },
    onPeriodOptionDblClick(payload: { period: string }) {
      this.onPeriodOptionSelected(payload);
      if (payload.period === RANGE_PERIOD
        || payload.period === this.appliedPeriod
        || !this.appliedAnchorDate
      ) {
        return;
      }

      this.setPiwikPeriodAndDate(payload.period, this.appliedAnchorDate);
    },
    canInteractWithSingleCalendar(): boolean {
      // Preset-owned selections are intentionally read-only for calendar interactions.
      // Users must switch ownership via period options before single-calendar clicks can commit.
      return this.calendarViewport === 'single'
        && this.uiSelection.type === 'period'
        && this.uiSelectedPeriod !== RANGE_PERIOD;
    },
    onDatePickerSelected(date: Date) {
      if (!this.canInteractWithSingleCalendar()) {
        return;
      }

      this.setUiSelection({ type: 'period', id: this.uiSelectedPeriod }, 'calendar');
      this.setPendingPeriodAndDate(this.uiSelectedPeriod, date);
      this.clearPresetSelection();
      this.commitSelectionToUrl(format(date), this.uiSelectedPeriod);
    },
    onPresetDateRangeSelected(selection: PresetDateRangeSelection) {
      if (!this.periodsFiltered.includes(selection.period)) {
        return;
      }

      this.setUiSelection({ type: 'preset', id: selection.id }, 'preset');
      this.activePresetId = selection.id;
      this.uiSelectedPeriod = selection.period;
      this.isRangeValid = true;
      this.pendingPresetSelection = selection;
      if (selection.period === RANGE_PERIOD) {
        this.stagedPresetRangeStartDate = format(selection.startDate);
        this.stagedPresetRangeEndDate = format(selection.endDate);
        this.calendarViewport = 'range';
        return;
      }

      this.stagedPresetRangeStartDate = null;
      this.stagedPresetRangeEndDate = null;
      this.calendarViewport = 'single';
      this.singleCalendarSelectedDate = selection.startDate;
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
    hasPendingPresetSelectionOwnedByUi(): boolean {
      return !!this.pendingPresetSelection
        && this.uiSelection.type === 'preset'
        && this.pendingPresetSelection.id === this.uiSelection.id;
    },
    shouldCloseSelectorWithoutApplying(): boolean {
      return this.uiSelection.type === 'preset'
        && this.uiSelectedPeriod !== RANGE_PERIOD;
    },
    hasCommittedRangeBounds(): boolean {
      return !!this.appliedRangeStartDate && !!this.appliedRangeEndDate;
    },
    applyPendingPresetSelection(): boolean {
      if (!this.hasPendingPresetSelectionOwnedByUi()) {
        return false;
      }

      const pendingPreset = this.pendingPresetSelection!;
      this.appliedPeriod = pendingPreset.period;
      this.appliedAnchorDate = pendingPreset.startDate;
      this.appliedRangeStartDate = format(pendingPreset.startDate);
      this.appliedRangeEndDate = format(pendingPreset.endDate);
      // Keep relative preset tokens in the URL (for example, "last7") so bookmarks stay rolling.
      // Staged start/end dates can be clamped for current UI bounds,
      // but URL semantics stay relative.
      this.commitSelectionToUrl(
        pendingPreset.date,
        pendingPreset.period,
      );
      return true;
    },
    applyRangeSelection(): boolean {
      if (this.uiSelectedPeriod !== RANGE_PERIOD) {
        return false;
      }

      const dateString = this.selectedDateString;
      if (!dateString) {
        return true;
      }

      this.appliedPeriod = RANGE_PERIOD;
      this.commitSelectionToUrl(
        this.getCurrentRollingDateParamIfOwnedByPreset() || dateString,
        RANGE_PERIOD,
      );
      return true;
    },
    applyNonRangeOrCompareChanges() {
      if (this.hasPendingNonRangePeriodChange) {
        return;
      }

      if (!this.isCompareDirty) {
        if (this.shouldCloseSelectorWithoutApplying()) {
          this.closePeriodSelector();
        }
        return;
      }

      if (this.appliedPeriod === RANGE_PERIOD) {
        if (!this.hasCommittedRangeBounds()) {
          return;
        }

        this.commitSelectionToUrl(
          this.getCurrentRollingDateParamIfOwnedByPreset()
          || `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`,
          RANGE_PERIOD,
        );
        return;
      }

      if (!this.appliedAnchorDate) {
        return;
      }

      this.commitSelectionToUrl(
        this.getCurrentRollingDateParamIfOwnedByPreset() || format(this.appliedAnchorDate),
        this.appliedPeriod,
      );
    },
    onApplyClicked() {
      if (this.applyPendingPresetSelection()) {
        return;
      }

      if (this.applyRangeSelection()) {
        return;
      }

      this.applyNonRangeOrCompareChanges();
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
    getCurrentContextKey(): string {
      return getContextKeyFromParsed(MatomoUrl.parsed.value as Record<string, unknown>);
    },
    applyUiSelectionFromHash(period: string, date: string, syncedUiSelection: UiSelection|null) {
      if (syncedUiSelection) {
        this.uiSelection = syncedUiSelection;
        this.activePresetId = syncedUiSelection.type === 'preset'
          ? syncedUiSelection.id
          : null;
        return;
      }

      const presetId = getTokenPresetIdFromPeriodAndDate(period, date);
      if (presetId
        && this.periodsFiltered.includes(period)
      ) {
        this.uiSelection = { type: 'preset', id: presetId };
        this.activePresetId = presetId;
        this.pendingPresetSelection = null;
        this.stagedPresetRangeStartDate = null;
        this.stagedPresetRangeEndDate = null;
        return;
      }

      this.setUiSelection({ type: 'period', id: period }, null);
      this.clearPresetSelection();
    },
    getCurrentRollingDateParamIfOwnedByPreset(): string|null {
      if (this.uiSelection.type !== 'preset') {
        return null;
      }

      const parsedPeriod = (MatomoUrl.parsed.value.period as string) || '';
      const parsedDate = (MatomoUrl.parsed.value.date as string) || '';
      if (parsedPeriod !== this.appliedPeriod || !parsedDate) {
        return null;
      }

      const presetId = getTokenPresetIdFromPeriodAndDate(parsedPeriod, parsedDate);
      if (presetId !== this.uiSelection.id) {
        return null;
      }

      return parsedDate;
    },
    resetSelectedDateValues() {
      this.appliedAnchorDate = null;
      this.appliedRangeStartDate = null;
      this.appliedRangeEndDate = null;
    },
    applyDateValuesFromHash(period: string, date: string) {
      if (period === RANGE_PERIOD) {
        const periodObj = Periods.get(period).parse(date) as Range;
        const [startDate, endDate] = periodObj.getDateRange();
        this.appliedAnchorDate = startDate;
        this.appliedRangeStartDate = format(startDate);
        this.appliedRangeEndDate = format(endDate);
        return;
      }

      this.appliedAnchorDate = parseDate(date);
      this.setRangeStartEndFromPeriod(period, date);
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
      }
      this.singleCalendarSelectedDate = this.appliedAnchorDate;
    },
    updateSelectedValuesFromHash() {
      const date = (MatomoUrl.parsed.value.date as string) || '';
      const period = (MatomoUrl.parsed.value.period as string) || '';
      const currentSelectionKey = getSelectionKey(period, date);
      const currentContextKey = this.getCurrentContextKey();
      if (shouldSkipHashSync(
        currentSelectionKey,
        currentContextKey,
        this.nextHashUiSelection,
        this.lastKnownHashSelectionKey,
        this.lastKnownHashContextKey,
      )) {
        return;
      }

      const hashSyncState = resolveSyncedUiSelection<PresetDateRangeId>(
        currentSelectionKey,
        currentContextKey,
        this.nextHashUiSelection,
        this.nextHashSelectionKey,
      );
      this.nextHashUiSelection = hashSyncState.nextHashUiSelection;
      this.nextHashSelectionKey = hashSyncState.nextHashSelectionKey;
      this.lastInteractionSource = hashSyncState.lastInteractionSource;
      this.lastKnownHashSelectionKey = hashSyncState.lastKnownHashSelectionKey;
      this.lastKnownHashContextKey = hashSyncState.lastKnownHashContextKey;

      this.applyUiSelectionFromHash(period, date, hashSyncState.syncedUiSelection);
      this.appliedPeriod = period;
      this.uiSelectedPeriod = period;
      this.resetSelectedDateValues();

      try {
        Periods.parse(period, date);
      } catch (e) {
        if (period === RANGE_PERIOD) {
          this.isRangeValid = false;
        } else {
          this.isRangeValid = null;
        }
        return;
      }

      this.applyDateValuesFromHash(period, date);
      this.isRangeValid = period === RANGE_PERIOD ? true : null;
      this.pendingPresetSelection = null;
      this.stagedPresetRangeStartDate = null;
      this.stagedPresetRangeEndDate = null;
      this.calendarViewport = period === RANGE_PERIOD ? 'range' : 'single';
      this.compareAppliedSignature = this.compareCurrentSignature;
    },
    setRangeStartEndFromPeriod(period: string, dateStr: string) {
      const dateRange = Periods.parse(period, dateStr).getDateRange();
      this.appliedRangeStartDate = format(
        dateRange[0] < siteMinAllowedDate ? siteMinAllowedDate : dateRange[0],
      );
      this.appliedRangeEndDate = format(
        dateRange[1] > siteMaxAllowedDate ? siteMaxAllowedDate : dateRange[1],
      );
    },
    canInteractWithRangeCalendar(): boolean {
      return this.calendarViewport === 'range'
        && this.uiSelection.type === 'period'
        && this.uiSelectedPeriod === RANGE_PERIOD;
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
      this.appliedRangeStartDate = start;
      this.appliedRangeEndDate = end;
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
      if (this.uiSelection.type === 'period'
        && this.uiSelectedPeriod !== RANGE_PERIOD
        && !this.isCompareDirty
      ) {
        return false;
      }

      if (this.hasPendingNonRangePeriodChange) {
        return false;
      }

      if (this.uiSelectedPeriod === RANGE_PERIOD
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

      const newDate = this.appliedAnchorDate != null
        ? new Date(this.appliedAnchorDate.getTime())
        : new Date();

      switch (this.appliedPeriod) {
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
      if (newDate < siteMinAllowedDate) {
        newDate.setTime(siteMinAllowedDate.getTime());
      }
      if (newDate > siteMaxAllowedDate) {
        newDate.setTime(siteMaxAllowedDate.getTime());
      }

      this.setPiwikPeriodAndDate(this.appliedPeriod, newDate);
    },
    isPeriodMoveDisabled(direction: number) {
      // disable period move when date range is used or when we would go out of the min/max dates
      if (this.appliedAnchorDate === null) {
        return this.isRangeSelection;
      }
      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction: number) {
      if (this.appliedAnchorDate === null) {
        return false;
      }
      const boundaryDate = (direction === -1) ? siteMinAllowedDate : siteMaxAllowedDate;
      return !datesAreInTheSamePeriod(this.appliedAnchorDate!, boundaryDate, this.appliedPeriod);
    },
  },
});
</script>
