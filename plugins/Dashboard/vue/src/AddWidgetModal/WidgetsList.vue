<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul class="widgetpreview-base widgetpreview-widgetlist">
    <li
      v-for="widget in widgets"
      :key="widget.uniqueId"
      :uniqueid="widget.uniqueId"
      :class="{
        'widgetpreview-choosen': widget.uniqueId === chosenWidget,
        'widgetpreview-unavailable': isUnavailable(widget),
      }"
      @mouseenter="onMouseEnter(widget)"
      @mouseleave="onMouseLeave(widget)"
      @click.prevent="onRowClick(widget)"
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
    chosenWidget: {
      type: String as PropType<string | null>,
      default: null,
    },
    addedWidgets: {
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
  computed: {
    placedWidgetIds(): Set<string> {
      // Reference `widgets` so the Set rebuilds on category switch — the
      // dashboard DOM is stable while the modal is open, and session-added
      // widgets are tracked separately via the `addedWidgets` prop.
      void this.widgets;
      const placed = document.querySelectorAll('#dashboardWidgetsArea [widgetId]');
      const ids = new Set<string>();
      placed.forEach((el) => {
        const id = el.getAttribute('widgetId');
        if (id) {
          ids.add(id);
        }
      });
      return ids;
    },
  },
  methods: {
    translate,

    isUnavailable(widget: WidgetType): boolean {
      if (!widget.uniqueId) {
        return false;
      }
      if (this.addedWidgets.has(widget.uniqueId)) {
        return true;
      }
      const { category } = widget as { category?: { id?: string } };
      if (category && category.id === KPI_METRIC_CATEGORY_ID) {
        return false;
      }
      return this.placedWidgetIds.has(widget.uniqueId);
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
      if (!this.supportsHover && widget.uniqueId !== this.chosenWidget) {
        this.$emit('hover', widget.uniqueId);
        return;
      }

      this.$emit('select', widget.uniqueId);
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
