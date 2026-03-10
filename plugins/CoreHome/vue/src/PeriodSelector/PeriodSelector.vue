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
    <PeriodSelectorTrigger
      ref="title"
      :committed-period="committedPeriod"
      :committed-anchor-date="committedAnchorDate"
      :applied-range-start-date="appliedRangeStartDate"
      :applied-range-end-date="appliedRangeEndDate"
      @move-period="onMovePeriod($event.direction)"
    />

    <div
      id="periodMore"
      class="dropdown"
      :class="selectedPeriod === 'range' ? 'dual-calendar' : 'single-calendar'"
    >
      <div class="flex">
        <PeriodSelectorOptionsColumn
          :selected-period="selectedPeriod"
          :periods-filtered="periodsFiltered"
          :ui-selection="uiSelection"
          :committed-period="committedPeriod"
          :active-preset-id="activePresetId"
          :min-allowed-date="minAllowedDate"
          :max-allowed-date="maxAllowedDate"
          @update:selected-period="selectedPeriod = $event"
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
          :selected-period="selectedPeriod"
          :committed-anchor-date="committedAnchorDate"
          :compare-period-dropdown-options="comparePeriodDropdownOptions"
          :is-apply-enabled="isApplyEnabled()"
          @range-change="onRangeChange($event.start, $event.end)"
          @single-date-select="onDatePickerSelected($event)"
          @apply-click="onApplyClicked()"
          @range-preset-date-cell-click-capture="onRangePresetDateCellClickCapture($event)"
          @compare-state-change="onCompareStateChange($event)"
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
import { translate } from '../translate';
import {
  isApplyEnabledFromState,
  resolveNonRangeApplyAction,
} from './PeriodSelector.applyFlow';
import {
  clampDateToBounds,
  getSelectedComparisonParamsForState,
  isKeyboardExpandEvent,
  shiftDateByPeriod,
  stripCompareDateParams,
} from './PeriodSelector.helpers';
import PeriodSelectorOptionsColumn from './PeriodSelectorOptionsColumn.vue';
import PeriodSelectorCalendarColumn from './PeriodSelectorCalendarColumn.vue';
import PeriodSelectorTrigger from './PeriodSelectorTrigger.vue';
import type {
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';
import { getTokenPresetIdFromPeriodAndDate } from './PresetDateRangeResolver';
import {
  getContextKeyFromParsed,
  getSelectionKey,
  updateSelectedValuesFromHash as updateSelectedValuesFromHashState,
} from './PeriodSelectorHashSync';
import type {
  CompareStateChangePayload,
  InteractionSource,
  PeriodSelectorState,
  UiSelection,
} from './PeriodSelector.types';
import {
  COMPARE_PERIOD_OPTIONS,
  RANGE_PERIOD,
  getSiteMaxAllowedDate,
  getSiteMinAllowedDate,
  isValidDate,
  isSingleCalendarPeriod,
} from './PeriodSelector.types';
import {
  clearPresetSelection,
  getCurrentRollingDateParamIfOwnedByPreset,
  onPeriodOptionDblClick,
  onPeriodOptionSelected,
  onPresetDateRangeSelected,
  setPendingPeriodAndDate,
  setUiSelection,
} from './PeriodSelector.selectionState';

export default defineComponent({
  name: 'PeriodSelector',
  props: {
    periods: Array,
  },
  components: {
    PeriodSelectorTrigger,
    PeriodSelectorOptionsColumn,
    PeriodSelectorCalendarColumn,
    ActivityIndicator,
  },
  directives: {
    ExpandOnClick,
  },
  data(): PeriodSelectorState {
    const selectedPeriod = MatomoUrl.parsed.value.period as string;
    const initialSinglePeriod = isSingleCalendarPeriod(selectedPeriod)
      ? selectedPeriod
      : 'day';
    const minAllowedDate = getSiteMinAllowedDate();
    const maxAllowedDate = getSiteMaxAllowedDate();

    return {
      uiSelection: { type: 'period', id: selectedPeriod },
      lastInteractionSource: null,
      nextHashUiSelection: null,
      nextHashSelectionKey: null,
      nextHashContextKey: null,
      lastKnownHashSelectionKey: null,
      lastKnownHashContextKey: null,
      minAllowedDate,
      maxAllowedDate,
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
      compareCurrentSignature: '',
      isCompareRangeValidValue: true,
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
  },
  computed: {
    matomoParsed() {
      return MatomoUrl.parsed.value;
    },
    comparePeriodDropdownOptions() {
      return COMPARE_PERIOD_OPTIONS;
    },
    isComparisonEnabled() {
      return ComparisonsStore.isComparisonEnabled();
    },
    periodsFiltered() {
      return (this.periods as string[] || []).filter(
        (periodLabel) => Periods.isRecognizedPeriod(periodLabel),
      );
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
    canShowMovePeriod() {
      return this.committedPeriod !== RANGE_PERIOD && !!this.committedAnchorDate;
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
    matomoParsed: {
      immediate: true,
      handler() {
        this.updateSelectedValuesFromHash();
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
        const titleRef = this.$refs.title as { focusTitle?: () => void } | undefined;
        if (titleRef?.focusTitle) {
          titleRef.focusTitle();
        }
      }
    },
    onCompareStateChange(payload: CompareStateChangePayload) {
      this.isComparing = payload.isComparing;
      this.comparePeriodType = payload.comparePeriodType;
      this.compareStartDate = payload.compareStartDate;
      this.compareEndDate = payload.compareEndDate;
      this.compareCurrentSignature = payload.compareCurrentSignature;
      this.isCompareRangeValidValue = payload.isCompareRangeValid;

      if (payload.source === 'sync') {
        this.compareAppliedSignature = payload.compareCurrentSignature;
      }
    },
    setUiSelection(selection: UiSelection, source: InteractionSource) {
      setUiSelection(this, selection, source);
    },
    clearPresetSelection() {
      clearPresetSelection(this);
    },
    setPendingPeriodAndDate(period: string, date: Date) {
      setPendingPeriodAndDate(this, period, date, {
        setRangeStartEndFromPeriod: (periodValue: string, dateValue: string) => {
          this.setRangeStartEndFromPeriod(periodValue, dateValue);
        },
      });
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
      this.nextHashContextKey = this.getCurrentContextKey();
      this.compareAppliedSignature = this.compareCurrentSignature;
      this.propagateNewUrlParams(date, period);

      window.initTopControls();
    },
    getSelectedComparisonParamsForCommit(period: string) {
      return getSelectedComparisonParamsForState({
        isComparing: this.isComparing,
        comparePeriodType: this.comparePeriodType,
        compareStartDate: this.compareStartDate,
        compareEndDate: this.compareEndDate,
        selectedPeriod: period,
        committedAnchorDate: this.committedAnchorDate,
        appliedRangeStartDate: this.appliedRangeStartDate,
        appliedRangeEndDate: this.appliedRangeEndDate,
      });
    },
    onPeriodOptionSelected(periodSelection: { period: string }) {
      onPeriodOptionSelected(this, periodSelection);
    },
    onPeriodOptionDblClick(periodSelection: { period: string }) {
      onPeriodOptionDblClick(this, periodSelection, {
        setPiwikPeriodAndDate: (period: string, date: Date) => {
          this.setPiwikPeriodAndDate(period, date);
        },
      });
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
      onPresetDateRangeSelected(this, this.periodsFiltered, selection);
    },
    propagateNewUrlParams(date: string, period: string) {
      const comparisonParams = this.getSelectedComparisonParamsForCommit(period);

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
        ...comparisonParams,
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
        committedPeriod: this.committedPeriod,
        hasCommittedRangeBounds: this.hasCommittedRangeBounds(),
        rollingDateParam: this.getCurrentRollingDateParamIfOwnedByPreset(),
        appliedRangeStartDate: this.appliedRangeStartDate,
        appliedRangeEndDate: this.appliedRangeEndDate,
        formattedCommittedAnchorDate: this.committedAnchorDate
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

    // Non-range period mode intentionally cannot commit compare-only via Apply.
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
    getCurrentContextKey(): string {
      return getContextKeyFromParsed(MatomoUrl.parsed.value as Record<string, unknown>);
    },
    getCurrentRollingDateParamIfOwnedByPreset(): string|null {
      return getCurrentRollingDateParamIfOwnedByPreset(
        this,
        (MatomoUrl.parsed.value.period as string) || '',
        (MatomoUrl.parsed.value.date as string) || '',
        getTokenPresetIdFromPeriodAndDate,
      );
    },
    updateSelectedValuesFromHash() {
      updateSelectedValuesFromHashState(this, {
        parsed: MatomoUrl.parsed.value as Record<string, unknown>,
        currentContextKey: this.getCurrentContextKey(),
        rangePeriod: RANGE_PERIOD,
        parsePeriod: (periodValue: string, dateValue: string) => {
          Periods.parse(periodValue, dateValue);
        },
        getTokenPresetIdFromPeriodAndDate,
        setUiSelection: (selection: UiSelection, source: null) => {
          this.setUiSelection(selection, source);
        },
        clearPresetSelection: () => {
          this.clearPresetSelection();
        },
        parseRange: (periodValue: string, dateValue: string) => {
          const parsedRangePeriod = Periods.get(periodValue).parse(dateValue) as Range;
          return parsedRangePeriod.getDateRange() as [Date, Date];
        },
        parseDate,
        format,
        siteMinAllowedDate: this.minAllowedDate,
        siteMaxAllowedDate: this.maxAllowedDate,
        isSingleCalendarPeriod,
        setRangeStartEndFromPeriod: (periodValue: string, dateValue: string) => {
          this.setRangeStartEndFromPeriod(periodValue, dateValue);
        },
      });
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
        selectedPeriod: this.selectedPeriod,
        hasPendingNonRangePeriodChange: this.hasPendingNonRangePeriodChange,
        hasPendingPresetSelection: !!this.pendingPresetSelection,
        isRangeValid: this.isRangeValid,
        isComparing: this.isComparing,
        comparePeriodType: this.comparePeriodType,
        isCompareRangeValid: this.isCompareRangeValidValue,
      });
    },
    closePeriodSelector() {
      (this.$refs.root as HTMLElement).classList.remove('expanded');
    },
    onMovePeriod(direction: number) {
      if (!this.canMovePeriod(direction)) {
        return;
      }

      const baseDate = this.committedAnchorDate || new Date();
      const shiftedDate = shiftDateByPeriod(baseDate, this.committedPeriod, direction);
      const clampedDate = clampDateToBounds(shiftedDate, this.minAllowedDate, this.maxAllowedDate);

      this.setPiwikPeriodAndDate(this.committedPeriod, clampedDate);
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
