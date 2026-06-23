<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul ref="list" class="widgetpreview-base widgetpreview-widgetlist">
    <li
      v-for="widget in widgets"
      :key="widget.uniqueId"
      :uniqueid="widget.uniqueId"
      :class="{
        'widgetpreview-choosen': widget.uniqueId === chosenWidgetId,
        'widgetpreview-unavailable': isUnavailable(widget),
      }"
      class="widget-list-item"
    >
      <button
        type="button"
        class="widget-button-item"
        @mouseenter="onMouseEnter(widget)"
        @mouseleave="onMouseLeave(widget)"
        @focus="onMouseEnter(widget)"
        @blur="onMouseLeave(widget)"
        @click.prevent="onRowClick(widget)"
        @keydown.enter.prevent="onActivate(widget)"
        @keydown.space.prevent="onActivate(widget)"
      >
        <span class="widgetpreview-widgetname">{{ widget.name }}</span>
        <span
          class="widgetpreview-add-hint"
          aria-hidden="true"
        ><i
          v-if="isJustAdded(widget)"
          class="icon-ok widgetpreview-add-check"
        ></i><span
          v-else
          class="widgetpreview-add-plus"
        >+</span> {{ translate(isJustAdded(widget) ? 'General_Added' : 'General_Add') }}</span>
      </button>
    </li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate, WidgetType } from 'CoreHome';

const HOVER_DELAY_MS = 400;
const KPI_METRIC_CATEGORY_ID = 'General_KpiMetric';

function hasHoverCapablePointer() {
  return typeof window !== 'undefined'
    && typeof window.matchMedia === 'function'
    && window.matchMedia('(any-hover: hover)').matches;
}

export default defineComponent({
  name: 'WidgetsList',
  props: {
    widgets: {
      type: Array as PropType<WidgetType[]>,
      required: true,
    },
    chosenWidgetId: {
      type: String as PropType<string | null>,
      default: null,
    },
    addedWidgets: {
      type: Object as PropType<Set<string>>,
      default: () => new Set<string>(),
    },
    existingWidgetIds: {
      type: Object as PropType<Set<string>>,
      default: () => new Set<string>(),
    },
  },
  emits: ['hover', 'select'],
  data() {
    return {
      hoverTimer: null as number | null,
      // Cached once: any hover-capable pointer gets desktop-like click-to-add
      // behaviour. Only pure no-hover environments use preview-first double-tap.
      supportsHover: hasHoverCapablePointer(),
      // The row most recently added in this session. Drives the transient green
      // check in the add hint; cleared as soon as the hover moves elsewhere (see
      // the chosenWidgetId watcher) so re-hovering an added row shows "+" again.
      justAddedId: null as string | null,
    };
  },
  watch: {
    chosenWidgetId(newId: string | null) {
      // Revert the green check the moment the preview/hover moves off the
      // just-added row. The add hint is only visible on the chosen row, so
      // tying the reset to chosenWidgetId matches what the user actually sees.
      if (newId !== this.justAddedId) {
        this.justAddedId = null;
      }
    },
  },
  methods: {
    translate,

    isRepeatableWidget(widget: WidgetType): boolean {
      return widget.category?.id === KPI_METRIC_CATEGORY_ID;
    },

    isJustAdded(widget: WidgetType): boolean {
      return !!widget.uniqueId && widget.uniqueId === this.justAddedId;
    },

    isUnavailable(widget: WidgetType): boolean {
      if (!widget.uniqueId) {
        return false;
      }
      if (this.addedWidgets.has(widget.uniqueId)) {
        return true;
      }
      if (this.isRepeatableWidget(widget)) {
        return false;
      }
      return this.existingWidgetIds.has(widget.uniqueId);
    },

    onMouseEnter(widget: WidgetType) {
      if (!widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
      const { uniqueId } = widget;
      this.hoverTimer = window.setTimeout(() => {
        this.hoverTimer = null;
        this.$emit('hover', uniqueId);
      }, HOVER_DELAY_MS);
    },

    onMouseLeave(widget: WidgetType) {
      // Matches the original jQuery widget menu: leaving an *unavailable* row keeps the
      // preview timer running so the user still gets a preview, while leaving any other
      // row cancels the pending preview.
      if (this.isUnavailable(widget)) {
        return;
      }
      this.clearHoverTimer();
    },

    onRowClick(widget: WidgetType) {
      if (!widget.uniqueId) {
        return;
      }
      // Rows flagged as `widgetpreview-unavailable` (already on the dashboard, or
      // added earlier in this modal session) stay clickable — the class is a
      // visual hint, not a hard block. Matches 5.x-dev's widgetMenu.js behaviour
      // where the click handler ignores the unavailable class.
      this.clearHoverTimer();

      // Touch / non-hover devices: first tap previews; second tap on the same row adds.
      if (!this.supportsHover && widget.uniqueId !== this.chosenWidgetId) {
        this.$emit('hover', widget.uniqueId);
        return;
      }

      this.justAddedId = widget.uniqueId;
      this.$emit('select', widget.uniqueId);
    },

    // Keyboard activation (Enter / Space). Bypasses the touch double-tap branch in
    // onRowClick on purpose — a keypress is not a touch interaction, so a focused
    // row should add immediately even when supportsHover is false.
    onActivate(widget: WidgetType) {
      if (!widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
      this.justAddedId = widget.uniqueId;
      this.$emit('select', widget.uniqueId);
    },

    focusFirst() {
      const list = this.$refs.list as HTMLUListElement | undefined;
      const first = list?.querySelector('li button');
      if (first instanceof HTMLElement) {
        first.focus();
      }
    },

    clearHoverTimer() {
      if (this.hoverTimer !== null) {
        window.clearTimeout(this.hoverTimer);
        this.hoverTimer = null;
      }
    },
  },
  beforeUnmount() {
    this.clearHoverTimer();
  },
});
</script>
