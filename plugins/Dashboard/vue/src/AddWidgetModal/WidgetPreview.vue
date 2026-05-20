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
    // The legacy `widgetMenu.js` preview path forced `widget=1` on every
    // preview request (see plugins/Dashboard/javascripts/widgetMenu.js
    // loadWidgetAjax). The Vue Widget component only injects `widget=1` on
    // the containerid branch, so re-add it here so the preview renders in
    // widgetized layout. `disableLink` is mirrored from the legacy logic:
    // only forced on Widgetize embeds (URL carries disableLink) or
    // `body#standalone`. On a normal dashboard page the preview must keep
    // links enabled so it matches the widget the user actually adds.
    previewWidget(): WidgetType | null {
      if (!this.widget) {
        return null;
      }
      const parameters: Record<string, unknown> = {
        ...this.widget.parameters,
        widget: '1',
        // Suppress the server-side title: with `widget=1` the response would
        // otherwise carry a bare `<h2>` (see _dataTable.twig `showOnlyTitleWithoutCard`)
        // that duplicates the modal's own "Widget preview" header above the pane.
        // Scoped to this preview-only parameter object — the widget added to the
        // dashboard uses its original metadata parameters and keeps its title.
        showtitle: '0',
      };
      if (this.shouldDisableLink()) {
        parameters.disableLink = '1';
      }
      return {
        ...this.widget,
        parameters,
      };
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
      // Mirror legacy widgetMenu.js loadWidgetAjax: only set disableLink when
      // the URL already carries it (Widgetize embed) or the page is
      // body#standalone. Otherwise the preview must render with links enabled
      // so it matches what the user will actually add to the dashboard.
      const urlFlag = Matomo.broadcast.getValueFromUrl('disableLink');
      if (urlFlag && urlFlag.length) {
        return true;
      }
      return !!document.querySelector('body#standalone');
    },
    onWidgetLoaded(payload: WidgetLoadedPayload) {
      // Nested widget loads inside a container preview also emit
      // widget:loaded; without this guard widget:create re-fires and
      // dashboard-mode handlers re-initialize.
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
