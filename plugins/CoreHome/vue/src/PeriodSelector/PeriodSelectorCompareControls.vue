<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="compare-checkbox"
    v-if="isComparisonEnabled"
  >
    <label class="compare-checkbox-label">
      <input
        class="compare-checkbox-input"
        id="comparePeriodTo"
        type="checkbox"
        :checked="!!isComparing"
        @change="onCompareToggle($event)"
      />
      <span class="compare-checkbox-text">{{ translate('General_CompareTo') }}</span>
    </label>
    <div id="comparePeriodToDropdown">
      <Field
        :model-value="comparePeriodType"
        @update:model-value="$emit('update:comparePeriodType', $event)"
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
            :model-value="compareStartDate"
            @update:model-value="$emit('update:compareStartDate', $event)"
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
            :model-value="compareEndDate"
            @update:model-value="$emit('update:compareEndDate', $event)"
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
import { translate } from '../translate';
import useExternalPluginComponent from '../useExternalPluginComponent';

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
    isComparing: {
      type: Boolean as PropType<boolean|null>,
      default: null,
    },
    comparePeriodType: {
      type: String,
      required: true,
    },
    compareStartDate: {
      type: String,
      required: true,
    },
    compareEndDate: {
      type: String,
      required: true,
    },
    comparePeriodDropdownOptions: {
      type: Array as PropType<ComparePeriodOption[]>,
      required: true,
    },
  },
  emits: [
    'update:isComparing',
    'update:comparePeriodType',
    'update:compareStartDate',
    'update:compareEndDate',
  ],
  methods: {
    translate,
    onCompareToggle(event: Event) {
      this.$emit('update:isComparing', (event.target as HTMLInputElement).checked);
    },
  },
});
</script>
