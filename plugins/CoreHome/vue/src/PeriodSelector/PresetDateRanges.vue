<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="presetDateRanges">
    <div
      v-for="(group, index) in groupedPresetDateRanges"
      :key="index"
      class="preset-date-range-group"
    >
      <div
        v-if="index > 0"
        class="preset-date-range-group-separator"
      />
      <p
        v-for="preset in group"
        :key="preset.id"
      >
        <label
          :class="{ 'selected-period-label': checkedPresetId === preset.id }"
          :title="checkedPresetId === preset.id
            ? ''
            : translate('General_DoubleClickToChangePeriod')"
          @dblclick="handlePresetDoubleClick(preset.id)"
        >
          <input
            type="radio"
            class="preset-option-input"
            :name="presetInputName"
            :id="`preset_date_${preset.id}`"
            :checked="checkedPresetId === preset.id"
            @change="handlePresetSelected(preset.id)"
          />
          <span class="preset-option-text">{{ translate(preset.labelKey) }}</span>
        </label>
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { getToday } from '../Periods';
import { translate } from '../translate';
import {
  PRESET_DATE_RANGES,
  PRESET_DATE_RANGE_PERIODS,
  clampDateToBounds,
  resolvePresetDateRange,
} from './PresetDateRangeResolver';
import type {
  PresetDateRangeId,
  PresetDateRangeOption,
  PresetDateRangeSelection,
} from './PresetDateRangeResolver';

export type { PresetDateRangeId, PresetDateRangeSelection };

const PRESET_DATE_RANGE_GROUPS: PresetDateRangeId[][] = [
  ['today', 'yesterday'],
  ['last7days', 'last30days', 'last90days'],
  ['lastWeekMonSun', 'lastMonth', 'lastQuarter', 'lastYear'],
  ['thisWeekMonToday', 'thisMonth', 'thisQuarter', 'thisYear'],
];

let nextPresetDateRangeGroupId = 0;

export default defineComponent({
  props: {
    modelValue: {
      type: String as PropType<PresetDateRangeId|null>,
      default: null,
    },
    checkedPresetId: {
      type: String as PropType<PresetDateRangeId|null>,
      default: null,
    },
    minDate: {
      type: Date,
      required: true,
    },
    maxDate: {
      type: Date,
      required: true,
    },
    today: {
      type: Date,
      default: () => getToday(),
    },
    allowedPeriods: {
      type: Array as PropType<string[]>,
      required: true,
    },
  },
  data() {
    const presetInputName = `preset-date-range-${nextPresetDateRangeGroupId}`;
    nextPresetDateRangeGroupId += 1;

    return {
      presetInputName,
    };
  },
  emits: ['update:modelValue', 'select', 'dblclick'],
  computed: {
    presetDateRanges(): PresetDateRangeOption[] {
      return PRESET_DATE_RANGES.filter(
        (preset) => this.allowedPeriods.includes(PRESET_DATE_RANGE_PERIODS[preset.id]),
      );
    },
    groupedPresetDateRanges(): PresetDateRangeOption[][] {
      const presetDateRangeById = new Map<PresetDateRangeId, PresetDateRangeOption>(
        this.presetDateRanges.map((preset) => [preset.id, preset]),
      );

      return PRESET_DATE_RANGE_GROUPS.map((group) => group
        .map((presetId) => presetDateRangeById.get(presetId))
        .filter((preset): preset is PresetDateRangeOption => !!preset))
        .filter((group) => group.length);
    },
  },
  methods: {
    translate,
    handlePresetSelected(presetId: PresetDateRangeId) {
      const resolvedPreset = resolvePresetDateRange(presetId, this.today);

      this.$emit('update:modelValue', presetId);
      this.$emit('select', {
        ...resolvedPreset,
        startDate: clampDateToBounds(resolvedPreset.startDate, this.minDate, this.maxDate),
        endDate: clampDateToBounds(resolvedPreset.endDate, this.minDate, this.maxDate),
      } as PresetDateRangeSelection);
    },
    handlePresetDoubleClick(presetId: PresetDateRangeId) {
      const resolvedPreset = resolvePresetDateRange(presetId, this.today);

      this.$emit('dblclick', {
        ...resolvedPreset,
        startDate: clampDateToBounds(resolvedPreset.startDate, this.minDate, this.maxDate),
        endDate: clampDateToBounds(resolvedPreset.endDate, this.minDate, this.maxDate),
      } as PresetDateRangeSelection);
    },
  },
});
</script>
