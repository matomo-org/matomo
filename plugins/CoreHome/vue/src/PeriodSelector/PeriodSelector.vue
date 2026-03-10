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
        <PeriodSelectorOptionsColumn
          :ui-selected-period="selectedPeriod"
          :periods-filtered="periodsFiltered"
          :ui-selection="uiSelection"
          :applied-period="committedPeriod"
          :active-preset-id="activePresetId"
          :min-allowed-date="minAllowedDate"
          :max-allowed-date="maxAllowedDate"
          @update:ui-selected-period="selectedPeriod = $event"
          @update:active-preset-id="activePresetId = $event"
          @period-select="onPeriodOptionSelected($event)"
          @period-dblclick="onPeriodOptionDblClick($event)"
          @preset-select="onPresetDateRangeSelected($event)"
        />
        <PeriodSelectorCalendarColumn
          :ui-selection="uiSelection"
          :calendar-viewport="calendarViewport"
          :display-range-start-date="displayRangeStartDate"
          :display-range-end-date="displayRangeEndDate"
          :single-calendar-period="singleCalendarPeriod"
          :single-calendar-selected-date="singleCalendarSelectedDate"
          :is-comparison-enabled="isComparisonEnabled"
          :is-comparing="isComparing"
          :compare-period-type="comparePeriodType"
          :compare-start-date="compareStartDate"
          :compare-end-date="compareEndDate"
          :compare-period-dropdown-options="comparePeriodDropdownOptions"
          :is-apply-enabled="isApplyEnabled()"
          @range-change="onRangeChange($event.start, $event.end)"
          @single-date-select="onDatePickerSelected($event)"
          @apply-click="onApplyClicked()"
          @range-preset-date-cell-click-capture="onRangePresetDateCellClickCapture($event)"
          @update:isComparing="isComparing = $event"
          @update:comparePeriodType="comparePeriodType = $event"
          @update:compareStartDate="compareStartDate = $event"
          @update:compareEndDate="compareEndDate = $event"
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
import ActivityIndicator from '../ActivityIndicator/ActivityIndicator.vue';
import ComparisonsStore from '../Comparisons/Comparisons.store.instance';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import {
  Periods,
  parseDate,
  Range,
  format,
  datesAreInTheSamePeriod,
} from '../Periods';
import Tooltips from '../Tooltips/Tooltips';
import { translate } from '../translate';
import {
  isApplyEnabledFromState,
  resolveNonRangeApplyAction,
} from './PeriodSelector.applyFlow';
import {
  clampDateToBounds,
  isKeyboardExpandEvent,
  shiftDateByPeriod,
  stripCompareDateParams,
} from './PeriodSelector.helpers';
import PeriodSelectorOptionsColumn from './PeriodSelectorOptionsColumn.vue';
import PeriodSelectorCalendarColumn from './PeriodSelectorCalendarColumn.vue';
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
import type {
  InteractionSource,
  PeriodSelectorState,
  UiSelection,
} from './PeriodSelector.types';
import {
  COMPARE_PERIOD_OPTIONS,
  COMPARE_PERIOD_TYPES,
  RANGE_PERIOD,
  getSiteMaxAllowedDate,
  getSiteMinAllowedDate,
  isValidDate,
  isSingleCalendarPeriod,
} from './PeriodSelector.types';

export default defineComponent({
  name: 'PeriodSelector',
  props: {
    periods: Array,
  },
  components: {
    PeriodSelectorOptionsColumn,
    PeriodSelectorCalendarColumn,
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
    const siteMinAllowedDate = getSiteMinAllowedDate();
    const siteMaxAllowedDate = getSiteMaxAllowedDate();

    return {
      uiSelection: { type: 'period', id: selectedPeriod },
      lastInteractionSource: null,
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      minAllowedDate: siteMinAllowedDate,
      maxAllowedDate: siteMaxAllowedDate,
      activePresetId: null,
      pendingPresetSelection: null,
      committedPeriod: selectedPeriod,
      committedAnchorDate: null,
      selectedPeriod,
      calendarViewport: selectedPeriod === RANGE_PERIOD ? 'range' : 'single',
      singleCalendarPeriod: initialSinglePeriod,
      singleCalendarSelectedDate: null,
      appliedRangeStartDate: null,
      appliedRangeEndDate: null,
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
      let selectedDateValue;
      if (this.committedPeriod === 'range') {
        if (!this.appliedRangeStartDate || !this.appliedRangeEndDate) {
          return translate('General_Error');
        }

        selectedDateValue = `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`;
      } else {
        if (!this.committedAnchorDate) {
          return translate('General_Error');
        }

        selectedDateValue = format(this.committedAnchorDate);
      }

      try {
        return Periods.parse(this.committedPeriod!, selectedDateValue).getPrettyString();
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
        const selectedDateValue = this.selectedPeriod === 'range'
          ? `${this.appliedRangeStartDate},${this.appliedRangeEndDate}`
          : format(this.committedAnchorDate!);

        const previousYearComparisonDateRange = Periods.parse(
          this.selectedPeriod as string,
          selectedDateValue,
        ).getDateRange();
        previousYearComparisonDateRange[0].setFullYear(
          previousYearComparisonDateRange[0].getFullYear() - 1,
        );
        previousYearComparisonDateRange[1].setFullYear(
          previousYearComparisonDateRange[1].getFullYear() - 1,
        );

        if (this.selectedPeriod === 'range') {
          return {
            comparePeriods: ['range'],
            comparePeriodType: 'previousYear',
            compareDates: [
              `${format(previousYearComparisonDateRange[0])},${format(previousYearComparisonDateRange[1])}`,
            ],
          };
        }

        return {
          comparePeriods: [this.selectedPeriod],
          comparePeriodType: 'previousYear',
          compareDates: [format(previousYearComparisonDateRange[0])],
        };
      }

      console.warn(`Unknown compare period type: ${this.comparePeriodType}`);
      return {};
    },
    previousPeriodDateToSelectedPeriod() {
      if (this.selectedPeriod === 'range') {
        const currentStartRange = parseDate(this.appliedRangeStartDate!);
        const currentEndRange = parseDate(this.appliedRangeEndDate!);
        const previousPeriodEndDate = Range.getLastNRange('day', 2, currentStartRange).startDate;

        const selectedRangeLengthInDays = Math.floor(
          (currentEndRange.valueOf() - currentStartRange.valueOf()) / 86400000,
        );
        const previousRange = Range.getLastNRange(
          'day',
          1 + selectedRangeLengthInDays,
          previousPeriodEndDate,
        );

        return `${format(previousRange.startDate)},${format(previousRange.endDate)}`;
      }

      const previousPeriodStartDate = Range.getLastNRange(
        this.selectedPeriod,
        2,
        this.committedAnchorDate!,
      ).startDate;
      return format(previousPeriodStartDate);
    },
    selectedDateString() {
      if (this.selectedPeriod === 'range') {
        const selectedStartDate = this.appliedRangeStartDate!;
        const selectedEndDate = this.appliedRangeEndDate!;
        const parsedStartDate = parseDate(selectedStartDate);
        const parsedEndDate = parseDate(selectedEndDate);

        if (!isValidDate(parsedStartDate)
          || !isValidDate(parsedEndDate)
          || parsedStartDate > parsedEndDate
        ) {
          // TODO: use a notification instead?
          window.$('#alert')
            .find('h2')
            .text(translate('General_InvalidDateRange'));
          Matomo.helper.modalConfirm('#alert', {});
          return null;
        }

        return `${selectedStartDate},${selectedEndDate}`;
      }

      return format(this.committedAnchorDate!);
    },
    isErrorDisplayed() {
      return this.currentlyViewingText === translate('General_Error');
    },
    isRangeSelection() {
      return this.committedPeriod === 'range';
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
        && this.selectedPeriod !== this.committedPeriod;
    },
    isRangePresetSelection() {
      return this.uiSelection.type === 'preset'
        && this.selectedPeriod === RANGE_PERIOD;
    },
    displayRangeStartDate() {
      if (this.isRangePresetSelection && this.pendingPresetSelection) {
        return format(this.pendingPresetSelection.startDate);
      }

      return this.appliedRangeStartDate;
    },
    displayRangeEndDate() {
      if (this.isRangePresetSelection && this.pendingPresetSelection) {
        return format(this.pendingPresetSelection.endDate);
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
      if (isKeyboardExpandEvent(event)) {
        window.$(this.$refs.root as HTMLElement).find('.ui-datepicker-month').focus();
      }
    },
    onClosed(event: MouseEvent|KeyboardEvent) {
      if (isKeyboardExpandEvent(event)) {
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
    },
    setPendingPeriodAndDate(period: string, date: Date) {
      this.committedPeriod = period;
      this.selectedPeriod = period;
      this.committedAnchorDate = date;
      this.setRangeStartEndFromPeriod(period, format(date));
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
        this.singleCalendarSelectedDate = date;
      }
    },
    setPiwikPeriodAndDate(period: string, date: Date) {
      this.setPendingPeriodAndDate(period, date);
      this.setUiSelection({ type: 'period', id: period }, 'period');

      const formattedDate = format(date);
      this.clearPresetSelection();
      this.commitSelectionToUrl(formattedDate, this.selectedPeriod);
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
      this.selectedPeriod = payload.period;
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
      this.singleCalendarSelectedDate = payload.period === this.committedPeriod
        ? this.committedAnchorDate
        : null;
    },
    onPeriodOptionDblClick(payload: { period: string }) {
      this.onPeriodOptionSelected(payload);
      if (payload.period === RANGE_PERIOD
        || payload.period === this.committedPeriod
        || !this.committedAnchorDate
      ) {
        return;
      }

      this.setPiwikPeriodAndDate(payload.period, this.committedAnchorDate);
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
      this.setPendingPeriodAndDate(this.selectedPeriod, date);
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
        this.calendarViewport = 'range';
        return;
      }

      this.calendarViewport = 'single';
      this.singleCalendarSelectedDate = selection.startDate;
      if (isSingleCalendarPeriod(selection.period)) {
        this.singleCalendarPeriod = selection.period;
      }
    },
    propagateNewUrlParams(date: string, period: string) {
      const selectedCompareParams = this.selectedComparisonParams;

      let baseUrlParams: Record<string, unknown>;
      if (Matomo.helper.isReportingPage()) {
        this.closePeriodSelector();
        baseUrlParams = MatomoUrl.hashParsed.value;
      } else {
        this.isLoadingNewPage = true;
        baseUrlParams = MatomoUrl.parsed.value;
      }

      MatomoUrl.updateLocation({
        ...stripCompareDateParams(baseUrlParams),
        date,
        period,
        ...selectedCompareParams,
      });
    },
    hasPendingPresetSelectionOwnedByUi(): boolean {
      return !!this.pendingPresetSelection
        && this.uiSelection.type === 'preset'
        && this.pendingPresetSelection.id === this.uiSelection.id;
    },
    shouldCloseSelectorWithoutApplying(): boolean {
      return this.uiSelection.type === 'preset'
        && this.selectedPeriod !== RANGE_PERIOD;
    },
    hasCommittedRangeBounds(): boolean {
      return !!this.appliedRangeStartDate && !!this.appliedRangeEndDate;
    },
    applyPendingPresetSelection(): boolean {
      if (!this.hasPendingPresetSelectionOwnedByUi()) {
        return false;
      }

      const pendingPresetSelection = this.pendingPresetSelection!;
      this.committedPeriod = pendingPresetSelection.period;
      this.committedAnchorDate = pendingPresetSelection.startDate;
      this.appliedRangeStartDate = format(pendingPresetSelection.startDate);
      this.appliedRangeEndDate = format(pendingPresetSelection.endDate);
      // Keep relative preset tokens in the URL (for example, "last7") so bookmarks stay rolling.
      // Staged start/end dates can be clamped for current UI bounds,
      // but URL semantics stay relative.
      this.commitSelectionToUrl(
        pendingPresetSelection.date,
        pendingPresetSelection.period,
      );
      return true;
    },
    applyRangeSelection(): boolean {
      if (this.selectedPeriod !== RANGE_PERIOD) {
        return false;
      }

      const dateString = this.selectedDateString;
      if (!dateString) {
        return true;
      }

      this.committedPeriod = RANGE_PERIOD;
      this.commitSelectionToUrl(
        this.getCurrentRollingDateParamIfOwnedByPreset() || dateString,
        RANGE_PERIOD,
      );
      return true;
    },
    applyNonRangeOrCompareChanges() {
      const action = resolveNonRangeApplyAction({
        hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
        isCompareDirty: this.isCompareDirty,
        shouldCloseSelectorWithoutApplying: this.shouldCloseSelectorWithoutApplying(),
        appliedPeriod: this.committedPeriod,
        hasCommittedRangeBounds: this.hasCommittedRangeBounds(),
        rollingDateParam: this.getCurrentRollingDateParamIfOwnedByPreset(),
        appliedRangeStartDate: this.appliedRangeStartDate,
        appliedRangeEndDate: this.appliedRangeEndDate,
        formattedAppliedAnchorDate: this.committedAnchorDate
          ? format(this.committedAnchorDate)
          : null,
      });

      if (action.type === 'stop') {
        return;
      }

      if (action.type === 'close') {
        this.closePeriodSelector();
        return;
      }

      this.commitSelectionToUrl(action.date, action.period);
    },

    // Invariant: non-range period mode intentionally cannot commit compare-only via Apply.
    // When a non-range period option owns the selection, 'Apply' button stays disabled.
    // Compare controls can still be edited in this state, but users must click the calendar
    // to commit date/compare changes.
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
      const periodComparisons = ComparisonsStore.getPeriodComparisons();

      if (periodComparisons.length < 2) {
        return;
      }

      const parsedComparePeriodType = MatomoUrl.parsed.value.comparePeriodType as string;

      if (!COMPARE_PERIOD_TYPES.includes(parsedComparePeriodType)) {
        return;
      }

      this.comparePeriodType = parsedComparePeriodType;

      if (this.comparePeriodType !== 'custom' || periodComparisons[1].params.period !== 'range') {
        return;
      }

      let parsedCompareRangePeriod;

      try {
        parsedCompareRangePeriod = Periods.parse(
          periodComparisons[1].params.period,
          periodComparisons[1].params.date,
        ) as Range;
      } catch {
        return;
      }

      const [startDate, endDate] = parsedCompareRangePeriod.getDateRange();

      this.compareStartDate = format(startDate);
      this.compareEndDate = format(endDate);
    },
    getCurrentContextKey(): string {
      return getContextKeyFromParsed(MatomoUrl.parsed.value as Record<string, unknown>);
    },
    applyUiSelectionFromHash(
      selectedPeriod: string,
      selectedDate: string,
      syncedUiSelection: UiSelection|null,
    ) {
      if (syncedUiSelection) {
        this.uiSelection = syncedUiSelection;
        this.activePresetId = syncedUiSelection.type === 'preset'
          ? syncedUiSelection.id
          : null;
        return;
      }

      const presetId = getTokenPresetIdFromPeriodAndDate(selectedPeriod, selectedDate);
      if (presetId
        && this.periodsFiltered.includes(selectedPeriod)
      ) {
        this.uiSelection = { type: 'preset', id: presetId };
        this.activePresetId = presetId;
        this.pendingPresetSelection = null;
        return;
      }

      this.setUiSelection({ type: 'period', id: selectedPeriod }, null);
      this.clearPresetSelection();
    },
    getCurrentRollingDateParamIfOwnedByPreset(): string|null {
      if (this.uiSelection.type !== 'preset') {
        return null;
      }

      const currentUrlPeriod = (MatomoUrl.parsed.value.period as string) || '';
      const currentUrlDate = (MatomoUrl.parsed.value.date as string) || '';
      if (currentUrlPeriod !== this.committedPeriod || !currentUrlDate) {
        return null;
      }

      const presetId = getTokenPresetIdFromPeriodAndDate(currentUrlPeriod, currentUrlDate);
      if (presetId !== this.uiSelection.id) {
        return null;
      }

      return currentUrlDate;
    },
    resetSelectedDateValues() {
      this.committedAnchorDate = null;
      this.appliedRangeStartDate = null;
      this.appliedRangeEndDate = null;
    },
    applyDateValuesFromHash(period: string, date: string) {
      if (period === RANGE_PERIOD) {
        const parsedRangePeriod = Periods.get(period).parse(date) as Range;
        const [startDate, endDate] = parsedRangePeriod.getDateRange();
        this.committedAnchorDate = startDate;
        this.appliedRangeStartDate = format(startDate);
        this.appliedRangeEndDate = format(endDate);
        return;
      }

      this.committedAnchorDate = parseDate(date);
      this.setRangeStartEndFromPeriod(period, date);
      if (isSingleCalendarPeriod(period)) {
        this.singleCalendarPeriod = period;
      }
      this.singleCalendarSelectedDate = this.committedAnchorDate;
    },
    updateSelectedValuesFromHash() {
      const currentDate = (MatomoUrl.parsed.value.date as string) || '';
      const currentPeriod = (MatomoUrl.parsed.value.period as string) || '';
      const currentSelectionKey = getSelectionKey(currentPeriod, currentDate);
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

      const resolvedHashSyncState = resolveSyncedUiSelection<PresetDateRangeId>(
        currentSelectionKey,
        currentContextKey,
        this.nextHashUiSelection,
        this.nextHashSelectionKey,
      );
      this.nextHashUiSelection = resolvedHashSyncState.nextHashUiSelection;
      this.nextHashSelectionKey = resolvedHashSyncState.nextHashSelectionKey;
      this.lastInteractionSource = resolvedHashSyncState.lastInteractionSource;
      this.lastKnownHashSelectionKey = resolvedHashSyncState.lastKnownHashSelectionKey;
      this.lastKnownHashContextKey = resolvedHashSyncState.lastKnownHashContextKey;

      this.applyUiSelectionFromHash(
        currentPeriod,
        currentDate,
        resolvedHashSyncState.syncedUiSelection,
      );
      this.committedPeriod = currentPeriod;
      this.selectedPeriod = currentPeriod;
      this.resetSelectedDateValues();

      try {
        Periods.parse(currentPeriod, currentDate);
      } catch (e) {
        if (currentPeriod === RANGE_PERIOD) {
          this.isRangeValid = false;
        } else {
          this.isRangeValid = null;
        }
        return;
      }

      this.applyDateValuesFromHash(currentPeriod, currentDate);
      this.isRangeValid = currentPeriod === RANGE_PERIOD ? true : null;
      this.pendingPresetSelection = null;
      this.calendarViewport = currentPeriod === RANGE_PERIOD ? 'range' : 'single';
      this.compareAppliedSignature = this.compareCurrentSignature;
    },
    setRangeStartEndFromPeriod(period: string, dateStr: string) {
      const periodDateRange = Periods.parse(period, dateStr).getDateRange();
      this.appliedRangeStartDate = format(
        periodDateRange[0] < this.minAllowedDate ? this.minAllowedDate : periodDateRange[0],
      );
      this.appliedRangeEndDate = format(
        periodDateRange[1] > this.maxAllowedDate ? this.maxAllowedDate : periodDateRange[1],
      );
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
      this.appliedRangeStartDate = start;
      this.appliedRangeEndDate = end;
      this.setUiSelection({ type: 'period', id: RANGE_PERIOD }, 'range');
    },
    onRangePresetDateCellClickCapture(event: MouseEvent) {
      if (!this.isRangePresetSelection) {
        return;
      }

      const eventTarget = event.target as HTMLElement | null;
      if (!eventTarget) {
        return;
      }

      if (eventTarget.closest('.ui-datepicker-calendar a')) {
        event.preventDefault();
        event.stopPropagation();
      }
    },
    isApplyEnabled() {
      return isApplyEnabledFromState({
        uiSelectionType: this.uiSelection.type,
        uiSelectedPeriod: this.selectedPeriod,
        hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
        hasPendingPresetSelection: !!this.pendingPresetSelection,
        isRangeValid: this.isRangeValid,
        isComparing: this.isComparing,
        comparePeriodType: this.comparePeriodType,
        isCompareRangeValid: this.isCompareRangeValid(),
      });
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

      const baseDate = this.committedAnchorDate || new Date();
      const shiftedDate = shiftDateByPeriod(baseDate, this.committedPeriod, direction);
      const clampedDate = clampDateToBounds(shiftedDate, this.minAllowedDate, this.maxAllowedDate);

      this.setPiwikPeriodAndDate(this.committedPeriod, clampedDate);
    },
    isPeriodMoveDisabled(direction: number) {
      // disable period move when date range is used or when we would go out of the min/max dates
      if (this.committedAnchorDate === null) {
        return this.isRangeSelection;
      }
      return this.isRangeSelection || !this.canMovePeriod(direction);
    },
    canMovePeriod(direction: number) {
      if (this.committedAnchorDate === null) {
        return false;
      }
      const relevantBoundaryDate = (direction === -1) ? this.minAllowedDate : this.maxAllowedDate;
      return !datesAreInTheSamePeriod(
        this.committedAnchorDate!,
        relevantBoundaryDate,
        this.committedPeriod,
      );
    },
  },
});
</script>
