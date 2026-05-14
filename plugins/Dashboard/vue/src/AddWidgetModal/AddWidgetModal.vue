<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root" class="modal add-widget-dialog">
    <div class="modal-content">
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
  defineComponent, onMounted, ref, watch,
} from 'vue';
import { Matomo, WidgetType } from 'CoreHome';

const { $ } = window;

function isWidgetAvailable(uniqueId: string) {
  return !$('#dashboardWidgetsArea').find(`[widgetId="${uniqueId}"]`).length;
}

export default defineComponent({
  name: 'AddWidgetModal',
  props: {
    modelValue: {
      type: Boolean,
      required: true,
    },
  },
  emits: ['update:modelValue', 'select'],
  setup(props, { emit }) {
    const root = ref<HTMLElement | null>(null);

    function onSelect(uniqueId: string) {
      window.widgetsHelper.getWidgetObjectFromUniqueId(uniqueId, (widget) => {
        emit('select', widget as WidgetType);
        emit('update:modelValue', false);
      });
    }

    function buildPreview() {
      if (!root.value) {
        return;
      }
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ($(root.value) as any).widgetPreview({
        isWidgetAvailable,
        onSelect,
        resetOnSelect: true,
      });
    }

    function openModal() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ($(root.value!) as any)
        .modal({
          dismissible: true,
          onOpenEnd: buildPreview,
          onCloseEnd: () => emit('update:modelValue', false),
        })
        .modal('open');
    }

    function closeModal() {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ($(root.value!) as any).modal('close');
    }

    watch(() => props.modelValue, (open, wasOpen) => {
      if (open && !wasOpen) {
        openModal();
      } else if (!open && wasOpen) {
        closeModal();
      }
    });

    onMounted(() => {
      Matomo.on('WidgetsStore.reloaded', () => {
        if (props.modelValue) {
          buildPreview();
        }
      });
    });

    return {
      root,
    };
  },
});
</script>
