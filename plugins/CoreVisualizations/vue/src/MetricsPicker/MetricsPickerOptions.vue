<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul
    class="mtm-dropdownPanel__menu"
    role="menu"
    :aria-label="translate('General_ChooseMetrics')"
  >
    <li
      class="mtm-dropdownPanel__menuItem"
      role="none"
      v-for="columnConfig in selectableColumns"
      :key="columnConfig.column"
    >
      <div
        class="mtm-dropdownPanel__menuLink metricsPickerColumn"
        :role="multiselect ? 'menuitemcheckbox' : 'menuitemradio'"
        tabindex="0"
        :aria-checked="!!columnStates[columnConfig.column]"
        @click="optionSelected(columnConfig.column, columnStates)"
        @keydown.enter.prevent="activateItem"
        @keydown.space.prevent="activateItem"
      >
        <span class="mtm-dropdownPanel__menuLabel">{{ columnConfig.translation }}</span>
        <span
          v-if="columnStates[columnConfig.column]"
          class="mtm-dropdownPanel__rightIcon"
          aria-hidden="true"
        ><span class="icon-ok" /></span>
      </div>
    </li>
    <li
      class="mtm-dropdownPanel__menuHeading"
      role="presentation"
      v-if="selectableRows.length"
    >
      {{ translate('General_RecordsToPlot') }}
    </li>
    <li
      class="mtm-dropdownPanel__menuItem"
      role="none"
      v-for="rowConfig in selectableRows"
      :key="rowConfig.matcher"
    >
      <div
        class="mtm-dropdownPanel__menuLink metricsPickerRow"
        :role="multiselect ? 'menuitemcheckbox' : 'menuitemradio'"
        tabindex="0"
        :aria-checked="!!rowStates[rowConfig.matcher]"
        @click="optionSelected(rowConfig.matcher, rowStates)"
        @keydown.enter.prevent="activateItem"
        @keydown.space.prevent="activateItem"
      >
        <span class="mtm-dropdownPanel__menuLabel">{{ rowConfig.label }}</span>
        <span
          v-if="rowStates[rowConfig.matcher]"
          class="mtm-dropdownPanel__rightIcon"
          aria-hidden="true"
        ><span class="icon-ok" /></span>
      </div>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { activateMenuItem } from 'CoreHome';

export interface ColumnConfig {
  column: string;
  translation?: string;
}

export interface RowConfig {
  matcher: string;
  label?: string;
}

interface SelectableColumnInfo {
  column: string;
  translation: string;
}

interface SelectableRowInfo {
  matcher: string;
  label: string;
}

export interface MetricsPickerOptionsState {
  columnStates: Record<string, boolean>;
  rowStates: Record<string, boolean>;
}

// Declared outside the component because it is needed inside data(), before the
// component's methods are available.
function getInitialOptionStates(
  allOptions: (SelectableColumnInfo | SelectableRowInfo)[],
  selectedOptions: string[],
): Record<string, boolean> {
  const states: Record<string, boolean> = {};
  allOptions.forEach((columnConfig) => {
    const name = (columnConfig as SelectableColumnInfo).column
      || (columnConfig as SelectableRowInfo).matcher;
    states[name] = false;
  });
  selectedOptions.forEach((column) => {
    states[column] = true;
  });
  return states;
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
  data(): MetricsPickerOptionsState {
    return {
      columnStates: getInitialOptionStates(
        this.selectableColumns as SelectableColumnInfo[],
        this.selectedColumns as string[],
      ),
      rowStates: getInitialOptionStates(
        this.selectableRows as SelectableRowInfo[],
        this.selectedRows as string[],
      ),
    };
  },
  emits: ['select'],
  methods: {
    activateItem: activateMenuItem,
    unselectOptions(optionStates: Record<string, boolean>) {
      Object.keys(optionStates).forEach((optionName) => {
        optionStates[optionName] = false;
      });
    },
    getSelected(optionStates: Record<string, boolean>) {
      return Object.keys(optionStates).filter((optionName) => !!optionStates[optionName]);
    },
    optionSelected(optionValue: string, optionStates: Record<string, boolean>) {
      if (!this.multiselect) {
        this.unselectOptions(this.columnStates);
        this.unselectOptions(this.rowStates);
      }

      optionStates[optionValue] = !optionStates[optionValue];

      this.$emit('select', {
        columns: this.getSelected(this.columnStates),
        rows: this.getSelected(this.rowStates),
      });
    },
  },
});
</script>
