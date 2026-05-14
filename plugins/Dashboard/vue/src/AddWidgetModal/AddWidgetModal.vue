<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="modal add-widget-modal">
    <div class="modal-content add-widget-modal-content">
      <span class="btn-close modal-close"><i class="icon-close"></i></span>
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
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw } from 'vue';
import { Matomo, translate, WidgetType } from 'CoreHome';

const { $ } = window;
const OPEN_EVENT = 'Dashboard.AddWidget.open';
const CLOSE_EVENT = 'Dashboard.AddWidget.close';

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
  emits: ['select'],
  methods: {
    translate,
    openModal() { this.jqRoot.modal('open'); },
    closeModal() { this.jqRoot.modal('close'); },

    onSelect(uniqueId: string) {
      window.widgetsHelper.getWidgetObjectFromUniqueId(uniqueId, (widget) => {
        this.$emit('select', widget as WidgetType);
        this.closeModal();
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
  data(): AddWidgetModalState {
    return {
      isOpen: false,
      jqRoot: null,
    };
  },
  mounted() {
    this.jqRoot = markRaw($(this.$refs.root as HTMLElement));

    this.jqRoot.modal({
      dismissible: true,
      onOpenEnd: () => { this.isOpen = true; this.buildPreview(); },
      onCloseEnd: () => { this.isOpen = false; this.jqRoot.widgetPreview('reset'); },
    });

    Matomo.on(OPEN_EVENT, this.openModal);
    Matomo.on(CLOSE_EVENT, this.closeModal);
    Matomo.on('WidgetsStore.reloaded', this.onWidgetsReloaded);
  },
  unmounted() {
    Matomo.off(OPEN_EVENT, this.openModal);
    Matomo.off(CLOSE_EVENT, this.closeModal);
    Matomo.off('WidgetsStore.reloaded', this.onWidgetsReloaded);
  },
});
</script>
