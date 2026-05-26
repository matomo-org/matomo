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
      role="button"
      tabindex="0"
      :class="{
        'widgetpreview-choosen': widget.uniqueId === chosenWidgetId,
        'widgetpreview-unavailable': isUnavailable(widget),
      }"
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
      >+ {{ translate('General_Add') }}</span>
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
    };
  },
  methods: {
    translate,

    isRepeatableWidget(widget: WidgetType): boolean {
      return widget.category?.id === KPI_METRIC_CATEGORY_ID;
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
      this.$emit('select', widget.uniqueId);
    },

    focusFirst() {
      const list = this.$refs.list as HTMLUListElement | undefined;
      const first = list?.querySelector('li');
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
