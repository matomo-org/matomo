<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="metrics-picker__options"
    :role="multiselect ? 'group' : 'radiogroup'"
    :aria-label="translate('General_ChooseMetrics')"
  >
    <label
      class="metrics-picker__column metrics-picker__label"
      v-for="columnConfig in selectableColumns"
      :key="columnConfig.column"
    >
      <input
        class="filled-in"
        :type="multiselect ? 'checkbox' : 'radio'"
        :checked="!!columnStates[columnConfig.column]"
        @change="optionSelected(columnConfig.column, columnStates)"
        @keydown.enter.prevent="optionSelected(columnConfig.column, columnStates)"
      />
      <span aria-hidden="true"></span>
      <span class="metrics-picker__title">{{ columnConfig.translation }}</span>
    </label>
    <p
      class="metrics-picker__headline"
      v-if="selectableRows.length"
    >
      {{ translate('General_RecordsToPlot') }}
    </p>
    <label
      class="metrics-picker__row metrics-picker__label"
      v-for="rowConfig in selectableRows"
      :key="rowConfig.matcher"
    >
      <input
        class="filled-in"
        :type="multiselect ? 'checkbox' : 'radio'"
        :checked="!!rowStates[rowConfig.matcher]"
        @change="optionSelected(rowConfig.matcher, rowStates)"
        @keydown.enter.prevent="optionSelected(rowConfig.matcher, rowStates)"
      />
      <span aria-hidden="true"></span>
      <span class="metrics-picker__title">{{ rowConfig.label }}</span>
    </label>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

interface SelectableColumnInfo {
  column: string;
  translation: string;
}

interface SelectableRowInfo {
  matcher: string;
  label: string;
}

interface MetricsPickerOptionsState {
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
