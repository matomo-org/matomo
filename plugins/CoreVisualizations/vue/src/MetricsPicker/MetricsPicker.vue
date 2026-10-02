<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="mtm-selector"
    v-expand-on-click="{ expander: 'expander', expandedClass: EXPANDED_CLASS }"
  >
    <button
      ref="expander"
      type="button"
      class="mtm-selector__trigger"
    >
      <span class="mtm-selector__label">{{ translate('General_ChooseMetrics') }}</span>
      <span class="mtm-selector__rightIcon" aria-hidden="true">
        <span class="icon-chevron-down" />
      </span>
    </button>
    <div
      class="mtm-selector__dropdown mtm-selector__dropdown--anchorLeft
             mtm-selector__dropdown--aboveOverlays"
    >
      <!-- `--wide` rather than `--fixedWidth`: 254px is a floor here, so a metric whose name is
           wider than the panel widens it instead of being ellipsised. -->
      <div class="mtm-dropdownPanel mtm-dropdownPanel--wide">
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
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ExpandOnClick } from 'CoreHome';
import MetricsPickerOptions, { ColumnConfig, RowConfig } from './MetricsPickerOptions.vue';

const EXPANDED_CLASS = 'mtm-selector--expanded';

interface SelectedOptions {
  columns: string[];
  rows: string[];
}

export default defineComponent({
  props: {
    multiselect: Boolean,
    selectableColumns: {
      type: Array as PropType<ColumnConfig[]>,
      default: () => [],
    },
    selectableRows: {
      type: Array as PropType<RowConfig[]>,
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
  data() {
    return { EXPANDED_CLASS };
  },
  methods: {
    onSelect(selected: SelectedOptions) {
      this.$emit('select', selected);
      // selecting a metric applies the change and closes the dropdown
      (this.$refs.root as HTMLElement).classList.remove(EXPANDED_CLASS);
    },
  },
});
</script>
