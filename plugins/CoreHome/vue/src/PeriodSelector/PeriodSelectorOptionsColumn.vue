<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="period-type period-selector-options-column">
    <h6><b>{{ translate('General_ChoosePeriod') }}</b></h6>
    <div id="otherPeriods">
      <PeriodOptions
        :model-value="uiSelectedPeriod"
        :periods="periodsFiltered"
        :checked-period-id="uiSelection.type === 'period' ? uiSelection.id : null"
        :active-date-period="appliedPeriod"
        @update:model-value="$emit('update:uiSelectedPeriod', $event)"
        @select="$emit('period-select', $event)"
        @dblclick="$emit('period-dblclick', $event)"
      />
      <PresetDateRanges
        :model-value="activePresetId"
        :checked-preset-id="uiSelection.type === 'preset' ? uiSelection.id : null"
        :allowed-periods="periodsFiltered"
        :min-date="minAllowedDate"
        :max-date="maxAllowedDate"
        @update:model-value="$emit('update:activePresetId', $event)"
        @select="$emit('preset-select', $event)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from '../translate';
import PresetDateRanges from './PresetDateRanges.vue';
import PeriodOptions from './PeriodOptions.vue';

export default defineComponent({
  name: 'PeriodSelectorOptionsColumn',
  components: {
    PresetDateRanges,
    PeriodOptions,
  },
  props: {
    uiSelectedPeriod: {
      type: String,
      required: true,
    },
    periodsFiltered: {
      type: Array as PropType<string[]>,
      required: true,
    },
    uiSelection: {
      type: Object as PropType<{ type: string; id: string }>,
      required: true,
    },
    appliedPeriod: {
      type: String,
      required: true,
    },
    activePresetId: {
      type: String as PropType<string|null>,
      default: null,
    },
    minAllowedDate: {
      type: Date,
      required: true,
    },
    maxAllowedDate: {
      type: Date,
      required: true,
    },
  },
  emits: [
    'update:uiSelectedPeriod',
    'update:activePresetId',
    'period-select',
    'period-dblclick',
    'preset-select',
  ],
  methods: {
    translate,
  },
});
</script>
