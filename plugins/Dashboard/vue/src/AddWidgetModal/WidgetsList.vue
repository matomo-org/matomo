<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <ul
    class="widgetpreview-widgetlist"
    :style="listStyle"
  >
    <li
      v-for="widget in widgets"
      :key="widget.uniqueId"
      :uniqueid="widget.uniqueId"
      :class="{
        'widgetpreview-choosen': widget.uniqueId === chosenWidget,
        'widgetpreview-unavailable': isUnavailable(widget),
      }"
      @mouseenter="onMouseEnter(widget)"
      @mouseleave="onMouseLeave"
      @click.prevent="onClick(widget)"
    >{{ widget.name }}</li>
  </ul>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { WidgetType } from 'CoreHome';

const HOVER_DELAY_MS = 400;
const KPI_METRIC_CATEGORY_ID = 'General_KpiMetric';

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
    // Vertical offset (px) so the list lines up with the chosen category on the left.
    // Mirrors the inline `top` / `marginBottom` set by the legacy jQuery widgetPreview.
    offsetTop: {
      type: Number,
      default: 0,
    },
  },
  emits: ['hover', 'select'],
  data() {
    return {
      hoverTimer: null as number | null,
    };
  },
  computed: {
    listStyle(): Record<string, string> {
      if (!this.offsetTop) {
        return {};
      }
      const offset = `${this.offsetTop}px`;
      return { top: offset, marginBottom: offset };
    },
  },
  methods: {
    isUnavailable(widget: WidgetType): boolean {
      if (widget.category && (widget.category as { id?: string }).id === KPI_METRIC_CATEGORY_ID) {
        return false;
      }
      if (!widget.uniqueId) {
        return false;
      }
      const placed = document.querySelectorAll('#dashboardWidgetsArea [widgetId]');
      return Array.from(placed).some(
        (el) => el.getAttribute('widgetId') === widget.uniqueId,
      );
    },

    onMouseEnter(widget: WidgetType) {
      if (this.isUnavailable(widget) || !widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
      const uniqueId = widget.uniqueId;
      this.hoverTimer = window.setTimeout(() => {
        this.hoverTimer = null;
        this.$emit('hover', uniqueId);
      }, HOVER_DELAY_MS);
    },

    onMouseLeave() {
      this.clearHoverTimer();
    },

    onClick(widget: WidgetType) {
      if (!widget.uniqueId) {
        return;
      }
      this.clearHoverTimer();
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
