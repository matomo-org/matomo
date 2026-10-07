<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    ref="root"
    class="mtm-selector"
    v-expand-on-click="binding"
  >
    <button
      ref="expander"
      type="button"
      class="mtm-selector__trigger"
      v-bind="triggerProps"
    >
      <span class="mtm-selector__label">{{ translate('General_ChooseMetrics') }}</span>
      <span class="mtm-selector__rightIcon" aria-hidden="true">
        <span class="icon-chevron-down" />
      </span>
    </button>
    <div
      class="mtm-selector__dropdown mtm-selector__dropdown--anchorLeft
             mtm-selector__dropdown--aboveOverlays"
      @keydown="selector.onKeydown"
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
          @select="onSelect"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ExpandOnClick, useSelectorDropdown, SelectorDropdown } from 'CoreHome';
import MetricsPickerOptions, { ColumnConfig, RowConfig } from './MetricsPickerOptions.vue';

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
  // Created once: ExpandOnClick keeps its own state inside the binding object, so handing it a
  // fresh one on every render would lose it.
  created() {
    this.selector = useSelectorDropdown(
      { role: 'menu', expandedClass: 'mtm-selector--expanded' },
      () => this.$refs.root as HTMLElement | null,
      () => this.$refs.expander as HTMLElement | null,
    ) as unknown as typeof this.selector;
    this.binding = this.selector.expandBinding('expander');
  },
  data() {
    return {
      selector: null as unknown as SelectorDropdown,
      binding: null as unknown as Record<string, unknown>,
    };
  },
  computed: {
    triggerProps(): Record<string, string> {
      return this.selector.triggerProps();
    },
  },
  methods: {
    // Selecting a metric applies the change, which redraws the graph and this picker with it, so
    // the caller is told whether the keyboard was used: only then does the new trigger need the
    // focus back.
    onSelect(selected: SelectedOptions, event: MouseEvent|KeyboardEvent) {
      this.selector.closedBy(event);
      this.$emit('select', { ...selected, byKeyboard: event.detail === 0 });
    },
  },
});
</script>
