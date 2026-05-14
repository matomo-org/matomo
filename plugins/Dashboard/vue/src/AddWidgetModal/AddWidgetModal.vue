<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="modal-overlay add-widget-modal-overlay open"
      @click="closeModal"
    />
    <div
      v-show="isOpen"
      ref="root"
      class="modal add-widget-modal"
      :class="{ open: isOpen }"
      role="dialog"
      aria-modal="true"
      :aria-label="translate('Dashboard_AddAWidget')"
      tabindex="-1"
    >
      <div class="modal-content add-widget-modal-content">
        <span
          class="btn-close modal-close"
          role="button"
          tabindex="0"
          @click="closeModal"
          @keydown.enter="closeModal"
          @keydown.space.prevent="closeModal"
        >
          <i class="icon-close"></i>
        </span>
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
  </Teleport>
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
  previousBodyOverflow: string;
}

export default defineComponent({
  name: 'AddWidgetModal',
  emits: ['select'],
  data(): AddWidgetModalState {
    return {
      isOpen: false,
      jqRoot: null,
      previousBodyOverflow: '',
    };
  },
  methods: {
    translate,

    openModal() {
      if (this.isOpen) {
        return;
      }
      this.isOpen = true;
      this.previousBodyOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', this.onKeydown);
      // The modal element is kept mounted (v-show), so widgetPreview can
      // attach to it as soon as Vue has flipped its display style.
      this.$nextTick(() => { this.buildPreview(); });
    },

    closeModal() {
      if (!this.isOpen) {
        return;
      }
      this.jqRoot.widgetPreview('reset');
      this.isOpen = false;
      document.body.style.overflow = this.previousBodyOverflow;
      this.previousBodyOverflow = '';
      document.removeEventListener('keydown', this.onKeydown);
    },

    onKeydown(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        this.closeModal();
      }
    },

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
  mounted() {
    this.jqRoot = markRaw($(this.$refs.root as HTMLElement));

    Matomo.on(OPEN_EVENT, this.openModal);
    Matomo.on(CLOSE_EVENT, this.closeModal);
    Matomo.on('WidgetsStore.reloaded', this.onWidgetsReloaded);
  },
  unmounted() {
    Matomo.off(OPEN_EVENT, this.openModal);
    Matomo.off(CLOSE_EVENT, this.closeModal);
    Matomo.off('WidgetsStore.reloaded', this.onWidgetsReloaded);
    if (this.isOpen) {
      document.body.style.overflow = this.previousBodyOverflow;
      document.removeEventListener('keydown', this.onKeydown);
    }
  },
});
</script>
