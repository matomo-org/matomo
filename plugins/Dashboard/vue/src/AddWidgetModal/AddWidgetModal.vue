<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <matomo-modal
    v-model="isOpen"
    classes="add-widget-modal"
    content-class="add-widget-modal-content"
    :aria-label="translate('Dashboard_AddAWidget')"
    @opened="onOpened"
    @closed="onClosed"
  >
    <button
      type="button"
      class="btn-close modal-close"
      :aria-label="translate('General_Close')"
      @click="close"
    >
      <i class="icon-close"></i>
    </button>
    <h3 class="add-widget-modal-title">{{ translate('Dashboard_AddAWidget') }}</h3>
    <div class="add-widget-modal-body">
      <div class="add-widget-modal-categories">
        <ul class="widgetpreview-categorylist"></ul>
      </div>
      <div class="add-widget-modal-details">
        <ul class="widgetpreview-widgetlist"></ul>
        <div class="widgetpreview-preview"></div>
      </div>
    </div>
  </matomo-modal>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import {
  Matomo,
  MatomoModal,
  translate,
  WidgetType,
} from 'CoreHome';

const { $, widgetsHelper } = window;
const OPEN_EVENT = 'Dashboard.AddWidget.open';

function isWidgetAvailable(uniqueId: string) {
  return !$('#dashboardWidgetsArea').find(`[widgetId="${uniqueId}"]`).length;
}

interface AddWidgetModalState {
  isOpen: boolean;
  // $.widgetPreview stores its state (settings, widgetAjaxRequest) on the
  // jQuery wrapper, so we keep the same one for the component's lifetime —
  // a fresh $(elem) on close would lose settings and crash widgetPreview('reset').
  // markRaw() prevents Vue from trying to make the wrapper reactive.
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  jqRoot: any;
}

export default defineComponent({
  name: 'AddWidgetModal',
  components: { MatomoModal },
  emits: ['select'],
  data(): AddWidgetModalState {
    return {
      isOpen: false,
      jqRoot: null,
    };
  },
  methods: {
    translate,

    open() { this.isOpen = true; },
    close() { this.isOpen = false; },

    onOpened(modalRoot: HTMLElement) {
      if (!this.jqRoot) {
        this.jqRoot = markRaw($(modalRoot));
      }
      this.buildPreview();
    },

    onClosed() {
      if (this.jqRoot) {
        this.jqRoot.widgetPreview('reset');
      }
    },

    onSelect(uniqueId: string) {
      this.close();
      widgetsHelper.getWidgetObjectFromUniqueId(uniqueId, (widget: unknown) => {
        if (widget) {
          this.$emit('select', widget as WidgetType);
        }
      });
    },

    buildPreview() {
      this.jqRoot.widgetPreview({
        isWidgetAvailable,
        onSelect: this.onSelect,
        resetOnSelect: true,
      });
    },

    onWidgetsReloaded() {
      if (this.isOpen) {
        this.buildPreview();
      }
    },
  },
  mounted() {
    Matomo.on(OPEN_EVENT, this.open);
    Matomo.on('WidgetsStore.reloaded', this.onWidgetsReloaded);
  },
  unmounted() {
    Matomo.off(OPEN_EVENT, this.open);
    Matomo.off('WidgetsStore.reloaded', this.onWidgetsReloaded);
  },
});
</script>
