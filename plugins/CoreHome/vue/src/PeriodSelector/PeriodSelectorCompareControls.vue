<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="compareRoot"
    class="compare-checkbox"
    v-if="isComparisonEnabled"
  >
    <label class="compare-checkbox-label">
      <input
        id="comparePeriodTo"
        class="compare-checkbox-input"
        type="checkbox"
        :checked="!!localIsComparing"
        @change="onCompareToggle($event)"
      />
      <span class="compare-checkbox-text">{{ translate('General_CompareTo') }}</span>
    </label>
    <div
      id="comparePeriodToDropdown"
      class="compare-period-dropdown"
    >
      <Field
        :model-value="localComparePeriodType"
        @update:model-value="onComparePeriodTypeChange($event)"
        :style="{'visibility': localIsComparing ? 'visible' : 'hidden'}"
        :name="'comparePeriodToDropdown'"
        :uicontrol="'select'"
        :options="comparePeriodDropdownOptions"
        :full-width="true"
        :disabled="!localIsComparing"
      />
    </div>
  </div>
  <div
    class="compare-date-range"
    v-if="localIsComparing && localComparePeriodType === 'custom'"
  >
    <div class="compare-date-fields">
      <div
        id="comparePeriodStartDate"
        class="compare-date-field compare-date-field-start"
      >
        <div>
          <Field
            :model-value="localCompareStartDate"
            @update:model-value="onCompareStartDateChange($event)"
            :name="'comparePeriodStartDate'"
            :uicontrol="'text'"
            :full-width="true"
            :title="translate('CoreHome_StartDate')"
            :placeholder="'YYYY-MM-DD'"
          />
        </div>
      </div>
      <span class="compare-dates-separator" />
      <div
        id="comparePeriodEndDate"
        class="compare-date-field compare-date-field-end"
      >
        <div>
          <Field
            :model-value="localCompareEndDate"
            @update:model-value="onCompareEndDateChange($event)"
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
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import ComparisonsStore from '../Comparisons/Comparisons.store.instance';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import {
  Periods,
  Range,
  format,
  parseDate,
} from '../Periods';
import { translate } from '../translate';
import useExternalPluginComponent from '../useExternalPluginComponent';
import type { CompareStateChangePayload } from './PeriodSelector.types';
import {
  COMPARE_PERIOD_TYPES,
  RANGE_PERIOD,
} from './PeriodSelector.types';
import { getSelectedComparisonParamsForState } from './PeriodSelector.helpers';

const Field = useExternalPluginComponent('CorePluginsAdmin', 'Field');

interface ComparePeriodOption {
  key: string;
  value: string;
}

export default defineComponent({
  name: 'PeriodSelectorCompareControls',
  components: {
    Field,
  },
  props: {
    isComparisonEnabled: {
      type: Boolean,
      required: true,
    },
    selectedPeriod: {
      type: String,
      required: true,
    },
    committedAnchorDate: {
      type: Date as PropType<Date|null>,
      default: null,
    },
    appliedRangeStartDate: {
      type: String,
      required: true,
    },
    appliedRangeEndDate: {
      type: String,
      required: true,
    },
    comparePeriodDropdownOptions: {
      type: Array as PropType<ComparePeriodOption[]>,
      required: true,
    },
  },
  emits: ['compare-state-change'],
  data() {
    return {
      localIsComparing: false,
      localComparePeriodType: 'previousPeriod',
      localCompareStartDate: '',
      localCompareEndDate: '',
      removeDropdownFocusHandlers: null as null | (() => void),
    };
  },
  mounted() {
    this.hydrateCompareStateFromStore();
    this.emitCompareState('sync');
    this.bindDropdownFocusHandlers();
  },
  beforeUnmount() {
    if (this.removeDropdownFocusHandlers) {
      this.removeDropdownFocusHandlers();
    }
    this.removeDropdownFocusHandlers = null;
  },
  computed: {
    isComparingStoreValue() {
      return ComparisonsStore.isComparingPeriods();
    },
    periodComparisonsStoreValue() {
      return ComparisonsStore.getPeriodComparisons();
    },
    matomoParsedComparePeriodType() {
      return MatomoUrl.parsed.value.comparePeriodType;
    },
    selectedComparisonParams() {
      return getSelectedComparisonParamsForState({
        isComparing: this.localIsComparing,
        comparePeriodType: this.localComparePeriodType,
        compareStartDate: this.localCompareStartDate,
        compareEndDate: this.localCompareEndDate,
        selectedPeriod: this.selectedPeriod,
        committedAnchorDate: this.committedAnchorDate,
        appliedRangeStartDate: this.appliedRangeStartDate,
        appliedRangeEndDate: this.appliedRangeEndDate,
      });
    },
    compareCurrentSignature() {
      return JSON.stringify({
        isComparing: !!this.localIsComparing,
        comparePeriodType: this.localComparePeriodType || '',
        compareStartDate: this.localCompareStartDate || '',
        compareEndDate: this.localCompareEndDate || '',
      });
    },
  },
  watch: {
    isComparingStoreValue() {
      this.hydrateCompareStateFromStore();
      this.emitCompareState('sync');
    },
    periodComparisonsStoreValue() {
      this.hydrateCompareStateFromStore();
      this.emitCompareState('sync');
    },
    matomoParsedComparePeriodType() {
      this.hydrateCompareStateFromStore();
      this.emitCompareState('sync');
    },
    selectedPeriod() {
      this.emitCompareState('context');
    },
    committedAnchorDate() {
      this.emitCompareState('context');
    },
    appliedRangeStartDate() {
      this.emitCompareState('context');
    },
    appliedRangeEndDate() {
      this.emitCompareState('context');
    },
  },
  methods: {
    translate,
    bindDropdownFocusHandlers() {
      const compareRoot = this.$refs.compareRoot as HTMLElement | undefined;
      const periodSelectorRoot = compareRoot?.closest('.periodSelector');

      if (!compareRoot || !periodSelectorRoot) {
        return;
      }

      const focusInHandler = (event: Event) => {
        const eventTarget = event.target as HTMLElement | null;
        if (eventTarget?.closest('.compare-period-dropdown .select-dropdown')) {
          periodSelectorRoot.classList.add('compare-dropdown-open');
        }
      };
      const focusOutHandler = (event: Event) => {
        const eventTarget = event.target as HTMLElement | null;
        if (eventTarget?.closest('.compare-period-dropdown .select-dropdown')) {
          periodSelectorRoot.classList.remove('compare-dropdown-open');
        }
      };

      compareRoot.addEventListener('focusin', focusInHandler);
      compareRoot.addEventListener('focusout', focusOutHandler);
      this.removeDropdownFocusHandlers = () => {
        compareRoot.removeEventListener('focusin', focusInHandler);
        compareRoot.removeEventListener('focusout', focusOutHandler);
        periodSelectorRoot.classList.remove('compare-dropdown-open');
      };
    },
    hydrateCompareStateFromStore() {
      this.localIsComparing = ComparisonsStore.isComparingPeriods();
      this.localComparePeriodType = 'previousPeriod';
      this.localCompareStartDate = '';
      this.localCompareEndDate = '';

      const periodComparisons = ComparisonsStore.getPeriodComparisons();

      if (periodComparisons.length < 2) {
        return;
      }

      const parsedComparePeriodType = MatomoUrl.parsed.value.comparePeriodType as string;

      if (!COMPARE_PERIOD_TYPES.includes(parsedComparePeriodType)) {
        return;
      }

      this.localComparePeriodType = parsedComparePeriodType;

      if (this.localComparePeriodType !== 'custom' || periodComparisons[1].params.period !== RANGE_PERIOD) {
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

      this.localCompareStartDate = format(startDate);
      this.localCompareEndDate = format(endDate);
    },
    isCompareRangeValid() {
      try {
        parseDate(this.localCompareStartDate);
      } catch (e) {
        return false;
      }

      try {
        parseDate(this.localCompareEndDate);
      } catch (e) {
        return false;
      }

      return true;
    },
    emitCompareState(source: CompareStateChangePayload['source']) {
      const payload: CompareStateChangePayload = {
        isComparing: !!this.localIsComparing,
        comparePeriodType: this.localComparePeriodType,
        compareStartDate: this.localCompareStartDate,
        compareEndDate: this.localCompareEndDate,
        compareCurrentSignature: this.compareCurrentSignature,
        isCompareRangeValid: this.isCompareRangeValid(),
        source,
      };
      this.$emit('compare-state-change', payload);
    },
    onCompareToggle(event: Event) {
      this.localIsComparing = (event.target as HTMLInputElement).checked;
      this.emitCompareState('user');
    },
    onComparePeriodTypeChange(value: string) {
      this.localComparePeriodType = value;
      this.emitCompareState('user');
    },
    onCompareStartDateChange(value: string) {
      this.localCompareStartDate = value;
      this.emitCompareState('user');
    },
    onCompareEndDateChange(value: string) {
      this.localCompareEndDate = value;
      this.emitCompareState('user');
    },
  },
});
</script>
