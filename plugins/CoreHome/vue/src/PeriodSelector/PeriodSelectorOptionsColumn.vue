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
        :checked-period-id="uiSelectedPeriod"
        :active-date-period="appliedPeriod"
        @update:model-value="$emit('update:uiSelectedPeriod', $event)"
        @select="$emit('period-select', $event)"
        @dblclick="$emit('period-dblclick', $event)"
      />
      <PresetDateRanges
        :checked-preset-id="activePresetId"
        :allowed-periods="periodsFiltered"
        :min-date="minAllowedDate"
        :max-date="maxAllowedDate"
        @select="$emit('preset-select', $event)"
        @dblclick="$emit('preset-dblclick', $event)"
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
    'period-select',
    'period-dblclick',
    'preset-select',
    'preset-dblclick',
  ],
  methods: {
    translate,
  },
});
</script>
