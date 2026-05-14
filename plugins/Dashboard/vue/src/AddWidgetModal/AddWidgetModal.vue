<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="modal add-widget-dialog">
    <div class="modal-content">
      <h2>Add a widget to this dashboard</h2>
      <div class="add-widget-dialog__categories">
        <ul class="widgetpreview-categorylist"></ul>
      </div>
      <div class="add-widget-dialog__details">
        <ul class="widgetpreview-widgetlist"></ul>
        <div class="widgetpreview-preview"></div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent, onMounted, onUnmounted, ref,
} from 'vue';
import { Matomo, WidgetType } from 'CoreHome';

const { $ } = window;
const OPEN_EVENT = 'Dashboard.AddWidget.open';
const CLOSE_EVENT = 'Dashboard.AddWidget.close';

function isWidgetAvailable(uniqueId: string) {
  return !$('#dashboardWidgetsArea').find(`[widgetId="${uniqueId}"]`).length;
}

export default defineComponent({
  name: 'AddWidgetModal',
  emits: ['select'],
  setup(_, { emit }) {
    const root = ref<HTMLElement | null>(null);
    // $.widgetPreview stores state on the jQuery object instance, so we must
    // keep and reuse the same wrapper for open/close/reset calls.
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const rootJQuery = ref<any>(null);
    let isOpen = false;

    function closeModal() {
      if (!rootJQuery.value) {
        return;
      }
      rootJQuery.value.modal('close');
    }

    function openModal() {
      if (!rootJQuery.value) {
        return;
      }
      rootJQuery.value.modal('open');
    }

    function onSelect(uniqueId: string) {
      window.widgetsHelper.getWidgetObjectFromUniqueId(uniqueId, (widget) => {
        emit('select', widget as WidgetType);
        closeModal();
      });
    }

    function buildPreview() {
      if (!rootJQuery.value) {
        return;
      }
      rootJQuery.value.widgetPreview({
        isWidgetAvailable,
        onSelect,
        resetOnSelect: true,
      });
    }

    function resetPreview() {
      if (!rootJQuery.value || !rootJQuery.value.settings) {
        return;
      }
      rootJQuery.value.widgetPreview('reset');
    }

    function onOpenRequested() {
      openModal();
    }

    function onCloseRequested() {
      closeModal();
    }

    function onWidgetsReloaded() {
      if (isOpen) {
        buildPreview();
      }
    }

    onMounted(() => {
      rootJQuery.value = $(root.value!);

      rootJQuery.value.modal({
        dismissible: true,
        onOpenEnd: () => {
          isOpen = true;
          buildPreview();
        },
        onCloseEnd: () => {
          isOpen = false;
          resetPreview();
        },
      });

      Matomo.on(OPEN_EVENT, onOpenRequested);
      Matomo.on(CLOSE_EVENT, onCloseRequested);
      Matomo.on('WidgetsStore.reloaded', onWidgetsReloaded);
    });

    onUnmounted(() => {
      Matomo.off(OPEN_EVENT, onOpenRequested);
      Matomo.off(CLOSE_EVENT, onCloseRequested);
      Matomo.off('WidgetsStore.reloaded', onWidgetsReloaded);
    });

    return {
      root,
    };
  },
});
</script>
