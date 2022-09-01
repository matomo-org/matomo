<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root"></div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  Matomo,
  WidgetType,
  MatomoUrl,
} from 'CoreHome';

const { $, widgetsHelper } = window;

export default defineComponent({
  mounted() {
    const element = this.$refs.root as HTMLElement;

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    ($(element) as any).widgetPreview({
      onPreviewLoaded: (widgetUniqueId: string, loadedWidgetElement: HTMLElement) => {
        this.callbackAddExportButtonsUnderWidget(widgetUniqueId, loadedWidgetElement);
      },
    });
  },
  methods: {
    callbackAddExportButtonsUnderWidget(widgetUniqueId: string, loadedWidgetElement: HTMLElement) {
      widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, (widget) => {
        const widgetParameters = (widget as WidgetType).parameters;
        const exportButtonsElement = $('<div id="exportButtons">');

        const urlIframe = this.getEmbedUrl(widgetParameters!, 'iframe');
        const widgetIframeHtml = '<div id="widgetIframe"><iframe width="100%" height="350" '
          + `src="${urlIframe}" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0">`
          + '</iframe></div>';

        const previewIframe = $('<div>')
          .attr('vue-entry', 'Widgetize.WidgetPreviewIframe')
          .attr('widget-iframe-html', JSON.stringify(widgetIframeHtml))
          .attr('url-iframe', JSON.stringify(urlIframe));
        $(exportButtonsElement).append(previewIframe);

        $(loadedWidgetElement).parent().append(exportButtonsElement);
        Matomo.helper.compileVueEntryComponents(exportButtonsElement);
      });
    },
    getEmbedUrl(parameters: NonNullable<WidgetType['parameters']>, exportFormat: string) {
      const finalParams: QueryParameters = {
        ...parameters,
        moduleToWidgetize: parameters.module as string,
        actionToWidgetize: parameters.action as string,
        module: 'Widgetize',
        action: exportFormat,
        idSite: Matomo.idSite,
        period: Matomo.period,
        date: MatomoUrl.urlParsed.value.date,
        disableLink: 1,
        widget: 1,
      };

      const { protocol, hostname } = window.location;
      const port = window.location.port === '' ? '' : `:${window.location.port}`;
      const path = window.location.pathname;
      const query = MatomoUrl.stringify(finalParams);

      return `${protocol}//${hostname}${port}${path}?${query}`;
    },
  },
});
</script>
