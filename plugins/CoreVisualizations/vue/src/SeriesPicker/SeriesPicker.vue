<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="jqplot-seriespicker"
    @mouseenter="isPopupVisible = true"
    @mouseleave="onLeavePopup()"
    :class="{open: isPopupVisible}"
  >
    <a
      href="#"
      @click.prevent.stop
    >
      +
    </a>
    <div
      class="jqplot-seriespicker-popover"
      v-if="isPopupVisible"
    >
      <p class="headline">
        {{ translate(multiselect ? 'General_MetricsToPlot' : 'General_MetricToPlot') }}</p>
      <p
        class="pickColumn"
        @click="optionSelected(columnConfig.column, columnStates)"
        v-for="columnConfig in selectableColumns"
        :key="columnConfig.column"
      >
        <label>
          <input
            class="select"
            :type="multiselect ? 'checkbox' : 'radio'"
            :checked="!!columnStates[columnConfig.column]"
          />
          <span>{{ columnConfig.translation }}</span>
        </label>
      </p>
      <p
        class="headline recordsToPlot"
        v-if="selectableRows.length"
      >
        {{ translate('General_RecordsToPlot') }}
      </p>
      <p
        class="pickRow"
        @click="optionSelected(rowConfig.matcher, rowStates)"
        v-for="rowConfig in selectableRows"
        :key="rowConfig.matcher"
      >
        <label>
          <input
            class="select"
            :type="multiselect ? 'checkbox' : 'radio'"
            :checked="!!rowStates[rowConfig.matcher]"
          />
          <span>{{ rowConfig.label }}</span>
        </label>
      </p>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { debounce } from 'CoreHome';

interface SelectableColumnInfo {
  column: string;
  translation: string;
}

interface SelectableRowInfo {
  matcher: string;
  label: string;
}

interface SeriesPickerState {
  isPopupVisible: boolean;
  columnStates: Record<string, boolean>;
  rowStates: Record<string, boolean>;
}

function getInitialOptionStates(
  allOptions: (SelectableColumnInfo | SelectableRowInfo)[],
  selectedOptions: string[],
) {
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

function arrayEqual<T>(lhs: T[], rhs: T[]) {
  if (lhs.length !== rhs.length) {
    return false;
  }

  return lhs.filter((element) => rhs.indexOf(element) === -1).length === 0;
}

function unselectOptions(optionStates: Record<string, boolean>) {
  Object.keys(optionStates).forEach((optionName) => {
    optionStates[optionName] = false;
  });
}

function getSelected(optionStates: Record<string, boolean>) {
  return Object.keys(optionStates).filter((optionName) => !!optionStates[optionName]);
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
  data(): SeriesPickerState {
    return {
      isPopupVisible: false,
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
  created() {
    this.optionSelected = debounce(this.optionSelected, 0);
  },
  methods: {
    optionSelected(optionValue: string, optionStates: Record<string, boolean>) {
      if (!this.multiselect) {
        unselectOptions(this.columnStates);
        unselectOptions(this.rowStates);
      }

      optionStates[optionValue] = !optionStates[optionValue];
      this.triggerOnSelectAndClose();
    },
    onLeavePopup() {
      this.isPopupVisible = false;

      if (this.optionsChanged()) {
        this.triggerOnSelectAndClose();
      }
    },
    triggerOnSelectAndClose() {
      this.isPopupVisible = false;
      this.$emit('select', {
        columns: getSelected(this.columnStates),
        rows: getSelected(this.rowStates),
      });
    },
    optionsChanged() {
      return !arrayEqual(
        getSelected(this.columnStates),
        this.selectedColumns,
      ) || !arrayEqual(
        getSelected(this.rowStates),
        this.selectedRows,
      );
    },
  },
});
</script>
