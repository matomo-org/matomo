<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="metrics-picker"
    v-expand-on-click="{ expander: 'expander' }"
  >
    <button
      ref="expander"
      type="button"
      class="metrics-picker__toggle"
    >
      <span class="metrics-picker__toggle-label">{{ translate('General_ChooseMetrics') }}</span>
      <span class="icon-chevron-down metrics-picker__chevron" />
    </button>
    <div class="metrics-picker__dropdown">
      <MetricsPickerOptions
        :multiselect="multiselect"
        :selectable-columns="selectableColumns"
        :selectable-rows="selectableRows"
        :selected-columns="selectedColumns"
        :selected-rows="selectedRows"
        @select="onSelect($event)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { ExpandOnClick } from 'CoreHome';
import MetricsPickerOptions from './MetricsPickerOptions.vue';

interface SelectedOptions {
  columns: string[];
  rows: string[];
}

export default defineComponent({
  props: {
    multiselect: Boolean,
    selectableColumns: {
      type: Array,
      default: () => [],
    },
    selectableRows: {
      type: Array,
      default: () => [],
    },
    selectedColumns: {
      type: Array,
      default: () => [],
    },
    selectedRows: {
      type: Array,
      default: () => [],
    },
  },
  components: {
    MetricsPickerOptions,
  },
  directives: {
    ExpandOnClick,
  },
  emits: ['select'],
  methods: {
    onSelect(selected: SelectedOptions) {
      this.$emit('select', selected);
      // selecting a metric applies the change and closes the dropdown
      (this.$refs.root as HTMLElement).classList.remove('expanded');
    },
  },
});
</script>
