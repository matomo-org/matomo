<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="wrapper" class="widgetpreview-preview"></div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import {
  AjaxHelper,
  translate,
  WidgetType,
} from 'CoreHome';

interface ActiveRequest {
  controller: AbortController;
  uniqueId: string;
}

function escapeAttribute(value: string): string {
  return window.piwikHelper.htmlEntities(value);
}

function buildWidgetShell(uniqueId: string): HTMLElement {
  const widget = document.createElement('div');
  widget.id = uniqueId;
  widget.className = 'widget';

  widget.innerHTML = `
    <div class="widgetTop">
      <div class="buttons">
        <div class="button" id="close">
          <span class="icon-close" title="${escapeAttribute(translate('General_Close'))}"></span>
        </div>
        <div class="button" id="maximise">
          <span class="icon-fullscreen" title="${escapeAttribute(translate('Dashboard_Maximise'))}"></span>
        </div>
        <div class="button" id="minimise">
          <span class="icon-minimise" title="${escapeAttribute(translate('Dashboard_Minimise'))}"></span>
        </div>
        <div class="button" id="refresh">
          <span class="icon-reload" title="${escapeAttribute(translate('General_Refresh'))}"></span>
        </div>
      </div>
      <h3 class="widgetName"><span title="${escapeAttribute(translate('Dashboard_AddPreviewedWidget'))}">${escapeAttribute(translate('Dashboard_WidgetPreview'))}</span>
        <div class="widgetNameOffScreen">${escapeAttribute(translate('General_Widget'))}</div>
      </h3>
    </div>
    <div class="widgetContent">
      <div class="widgetLoading">${escapeAttribute(translate('Dashboard_LoadingWidget'))}</div>
    </div>
  `;

  return widget;
}

export default defineComponent({
  name: 'WidgetPreview',
  props: {
    widget: {
      type: Object as PropType<WidgetType | null>,
      default: null,
    },
  },
  emits: ['select', 'update:loading'],
  data() {
    return {
      activeRequest: null as ActiveRequest | null,
    };
  },
  methods: {
    setLoading(loading: boolean) {
      this.$emit('update:loading', loading);
    },

    abortActive() {
      if (this.activeRequest) {
        this.activeRequest.controller.abort();
        this.activeRequest = null;
      }
    },

    clearWrapper() {
      const wrapper = this.$refs.wrapper as HTMLElement | undefined;
      if (wrapper) {
        wrapper.replaceChildren();
      }
    },

    mountShell(widget: WidgetType): HTMLElement | null {
      const wrapper = this.$refs.wrapper as HTMLElement | undefined;
      if (!wrapper || !widget.uniqueId) {
        return null;
      }

      const shell = buildWidgetShell(widget.uniqueId);
      wrapper.replaceChildren(shell);

      const widgetTop = shell.querySelector<HTMLElement>('.widgetTop');
      if (widgetTop) {
        widgetTop.style.cursor = 'pointer';
        widgetTop.addEventListener('click', (event) => {
          event.preventDefault();
          // Loading state lives in the DOM via .widgetLoading, matching the legacy guard.
          if (shell.querySelector('.widgetLoading') || !widget.uniqueId) {
            return;
          }
          this.$emit('select', widget.uniqueId);
        });
      }

      return shell;
    },

    renderClientComponent(widget: WidgetType) {
      const shell = this.mountShell(widget);
      const content = shell?.querySelector<HTMLElement>('.widgetContent');
      if (!shell || !content) {
        return;
      }

      const clientWidget = {
        ...widget,
        parameters: { ...(widget.parameters ?? {}) },
      };

      content.innerHTML = '<div vue-entry="CoreHome.Widget"'
        + ` widget="${window.piwikHelper.htmlEntities(JSON.stringify(clientWidget))}"`
        + ' widgetized="true"></div>';
      window.piwikHelper.compileVueEntryComponents(content);
      this.setLoading(false);
    },

    async renderLegacy(widget: WidgetType) {
      const shell = this.mountShell(widget);
      const content = shell?.querySelector<HTMLElement>('.widgetContent');
      if (!shell || !content || !widget.uniqueId) {
        return;
      }

      const controller = new AbortController();
      this.activeRequest = { controller, uniqueId: widget.uniqueId };
      this.setLoading(true);

      const disableLinkParam = window.broadcast.getValueFromUrl('disableLink');
      const params = {
        ...(widget.parameters ?? {}),
        widget: 1,
        disableLink: (disableLinkParam && disableLinkParam.length)
          || document.querySelector('body#standalone')
          ? 1
          : 0,
      };

      try {
        const response = await AjaxHelper.fetch<string>(params, {
          format: 'html',
          abortController: controller,
          createErrorNotification: false,
        });

        if (this.activeRequest?.uniqueId !== widget.uniqueId) {
          return;
        }

        content.innerHTML = response ?? '';
        window.piwikHelper.compileVueEntryComponents(content);
        content.dispatchEvent(new CustomEvent('widget:create', { bubbles: true }));
        this.activeRequest = null;
        this.setLoading(false);
      } catch (err) {
        if (this.activeRequest?.uniqueId !== widget.uniqueId) {
          return;
        }
        this.activeRequest = null;
        this.setLoading(false);
      }
    },

    refresh(widget: WidgetType | null) {
      this.abortActive();

      if (!widget) {
        this.clearWrapper();
        this.setLoading(false);
        return;
      }

      if (widget.clientComponent) {
        this.renderClientComponent(widget);
        return;
      }

      this.renderLegacy(widget);
    },
  },
  watch: {
    widget: {
      handler(newWidget: WidgetType | null) {
        this.refresh(newWidget);
      },
    },
  },
  mounted() {
    if (this.widget) {
      this.refresh(this.widget);
    }
  },
  beforeUnmount() {
    this.abortActive();
  },
});
</script>
