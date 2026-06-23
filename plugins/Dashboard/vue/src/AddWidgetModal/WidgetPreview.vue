<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="widgetpreview-preview">
    <div
      v-if="previewWidget"
      class="widget"
    >
      <div
        class="widgetTop"
        :title="translate('Dashboard_AddPreviewedWidget')"
        role="button"
        tabindex="0"
        @click="$emit('select', previewWidget.uniqueId)"
        @keydown.enter.prevent="$emit('select', previewWidget.uniqueId)"
        @keydown.space.prevent="$emit('select', previewWidget.uniqueId)"
      >
        <h3 class="widgetName">{{ translate('Dashboard_WidgetPreview') }}</h3>
      </div>
      <div class="widgetContent">
        <Widget
          :key="previewWidget.uniqueId"
          :widget="previewWidget"
          :widgetized="true"
          :suppress-notifications="true"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  Matomo,
  translate,
  Widget,
  WidgetContainerType,
  WidgetType,
} from 'CoreHome';

interface WidgetLoadedPayload {
  parameters?: { uniqueId?: string } & Record<string, unknown>;
  element?: JQuery;
}

export default defineComponent({
  name: 'WidgetPreview',
  components: {
    Widget,
  },
  props: {
    widget: {
      type: Object as PropType<WidgetType | null>,
      default: null,
    },
  },
  emits: ['select'],
  computed: {
    previewWidget(): WidgetType | null {
      if (!this.widget) {
        return null;
      }

      const result: WidgetContainerType = {
        ...this.widget,
        parameters: this.getPreviewParameters(this.widget.parameters),
      };

      if (this.isContainerWidget(result)) {
        result.widgets = this.getPreviewChildren(result);
      }

      return result;
    },
  },
  mounted() {
    Matomo.on('widget:loaded', this.onWidgetLoaded);
  },
  unmounted() {
    Matomo.off('widget:loaded', this.onWidgetLoaded);
  },
  methods: {
    translate,
    shouldDisableLink(): boolean {
      // disableLink is only forced for Widgetize embeds and the standalone body
      // matching widgetMenu.js.
      const urlFlag = Matomo.broadcast.getValueFromUrl('disableLink');
      if (urlFlag && urlFlag.length) {
        return true;
      }
      return !!document.querySelector('body#standalone');
    },
    getPreviewParameters(parameters: Record<string, unknown> = {}) {
      // Force widget=1 so previews render in widgetized layout, matching legacy widgetMenu.js.
      // showtitle=0 suppresses the server-rendered <h2>
      return {
        ...parameters,
        widget: '1',
        showtitle: '0',
        ...(this.shouldDisableLink() ? { disableLink: '1' } : {}),
      };
    },
    isContainerWidget(widget: WidgetType): widget is WidgetContainerType {
      return !!widget.isContainer && Array.isArray((widget as WidgetContainerType).widgets);
    },
    getPreviewChildren(widget: WidgetContainerType) {
      // Child widgets need the same widgetized parameters as the container preview.
      // Without this, nested widgets render as non-widgetized and may show titles again.
      const containerId = widget.parameters?.containerId as string | undefined;

      return widget.widgets!.map((child) => ({
        ...child,
        parameters: {
          ...child.parameters,
          widget: '1',
          ...(containerId ? { containerId } : {}),
        },
      }));
    },
    onWidgetLoaded(payload: WidgetLoadedPayload) {
      // Only re-fire widget:create for the top-level preview;
      // nested container loads emit widget:loaded too.
      if (!this.widget || payload.parameters?.uniqueId !== this.widget.uniqueId) {
        return;
      }
      const root = this.$el as HTMLElement | null;
      const loadedElement = payload?.element?.[0];
      if (!root || !loadedElement || !root.contains(loadedElement)) {
        return;
      }
      const widget = root.querySelector<HTMLElement>('.widget');
      const widgetContent = widget?.querySelector<HTMLElement>('.widgetContent');
      if (!widget || !widgetContent) {
        return;
      }
      window.$(widgetContent).trigger('widget:create', [{ element: window.$(widget) }]);
    },
  },
});
</script>
