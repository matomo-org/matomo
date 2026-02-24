<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="presetDateRanges">
    <p
      v-for="preset in presetDateRanges"
      :key="preset.id"
    >
      <label
        :class="{ 'selected-period-label': checkedPresetId === preset.id }"
      >
        <input
          type="radio"
          name="presetDateRange"
          :id="`preset_date_${preset.id}`"
          :checked="checkedPresetId === preset.id"
          @change="handlePresetSelected(preset.id)"
        />
        <span>{{ translate(preset.labelKey) }}</span>
      </label>
    </p>
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
  emits: ['update:modelValue', 'select'],
  computed: {
    presetDateRanges(): PresetDateRangeOption[] {
      return PRESET_DATE_RANGES.filter(
        (preset) => this.allowedPeriods.includes(PRESET_DATE_RANGE_PERIODS[preset.id]),
      );
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
  },
});
</script>
