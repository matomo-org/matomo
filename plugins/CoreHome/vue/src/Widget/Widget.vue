<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    v-if="actualWidget"
    v-show="showWidget"
    class="matomo-widget"
    :class="{'isFirstWidgetInPage': actualWidget.isFirstInPage}"
    :id="actualWidget.uniqueId"
    v-tooltips="{ content: tooltipContent }"
  >
    <WidgetLoader
      v-if="!actualWidget.isContainer && actualWidget.parameters"
      :widget-params="actualWidget.parameters"
      :widget-name="actualWidget.name"
    />
    <div v-if="actualWidget.isContainer
      && actualWidget.layout !== 'ByDimension'
      && !this.preventRecursion"
    >
      <div>
        <WidgetContainer :container="actualWidget.widgets" />
      </div>
    </div>
    <div v-if="actualWidget.isContainer && actualWidget.layout === 'ByDimension'">
      <div>
        <WidgetByDimensionContainer :widgets="actualWidget.widgets" />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { DeepReadonly, defineComponent } from 'vue';
import WidgetLoader from '../WidgetLoader/WidgetLoader.vue';
import WidgetContainer from '../WidgetContainer/WidgetContainer.vue';
import WidgetByDimensionContainer from '../WidgetByDimensionContainer/WidgetByDimensionContainer.vue';
import WidgetsStoreInstance, { getWidgetChildren } from './Widgets.store';
import {
  Widget as WidgetData,
  WidgetContainer as WidgetDataContainer,
} from './types';
import AjaxHelper from '../AjaxHelper/AjaxHelper';
import ReportMetadataStoreInstance from '../ReportMetadata/ReportMetadata.store';
import Tooltips from '../Tooltips/Tooltips';

function findContainer(
  widgetsByCategory: typeof WidgetsStoreInstance.widgets.value,
  containerId: string,
): DeepReadonly<WidgetData>|undefined {
  let widget: DeepReadonly<WidgetData>|undefined = undefined;
  Object.values(widgetsByCategory || {}).some((widgets: DeepReadonly<WidgetData[]>) => {
    widget = widgets.find((w) => w && w.isContainer && w.parameters?.containerId === containerId);
    return widget;
  });
  return widget;
}

/**
 * Renders any kind of widget. If you have a widget and you want to have it rendered, use this
 * directive. It will display a name on top and the actual widget below. It can handle any kind
 * of widget, no matter whether it is a regular widget or a container.
 *
 * @param {Object} piwikWidget  A widget object as returned by the WidgetMetadata API.
 * @param {Object} piwikWidget.middlewareParameters   If present, we will request a URL using the
 *                                                    given parameters and only if this URL
 *                                                    returns a JSON `true` the widget will be
 *                                                    shown. Otherwise the widget won't be shown.
 * @param {String} containerId  If you do not have a widget object but a containerId we will find
 *                              the correct widget object based on the given containerId. Be aware
 *                              that we might not find the widget if it is for example not
 *                              available for the current user or period/date.
 * @param {Boolean} widgetized  true if the widget is widgetized (eg in Dashboard or exported).
 *                              In this case we will add a URL parameter widget=1 to all widgets.
 *                              Eg sparklines will be then displayed one after another
 *                              (vertically aligned) instead of two next to each other.
 *
 * Example:
 * <Widget :widget="widget"></Widget>
 * // in this case we will find the correct widget automatically
 * <Widget :containerid="widgetGoalsOverview"></Widget>
 * // disables rating feature, no initial headline
 * <Widget :widget="widget" :widetized="true"></Widget>
 */
export default defineComponent({
  props: {
    widget: Object,
    widgetized: Boolean,
    containerid: String,
    preventRecursion: Boolean,
  },
  components: {
    WidgetLoader,
    WidgetContainer,
    WidgetByDimensionContainer,
  },
  directives: {
    Tooltips,
  },
  data() {
    return {
      showWidget: false,
    };
  },
  setup() {
    function tooltipContent(this: HTMLElement) {
      const $this = window.$(this) as JQuery;
      if ($this.attr('piwik-field') === '' || $this.hasClass('matomo-form-field')) {
        // do not show it for form fields
        return '';
      }

      const title = window.$(this).attr('title') || '';
      return window.vueSanitize(title.replace(/\n/g, '<br />'));
    }

    return {
      tooltipContent,
    };
  },
  created() {
    const { actualWidget } = this;

    if (actualWidget && actualWidget.middlewareParameters) {
      const params = actualWidget.middlewareParameters as unknown as QueryParameters;
      AjaxHelper.fetch(params).then((response) => {
        this.showWidget = !!response;
      });
    } else {
      this.showWidget = true;
    }
  },
  computed: {
    allWidgets() {
      return WidgetsStoreInstance.widgets.value;
    },
    actualWidget() {
      const widget = this.widget as WidgetData;

      if (widget) {
        const result = { ...widget };

        if (widget && widget.isReport && !widget.documentation) {
          const report = ReportMetadataStoreInstance.findReport(widget.module, widget.action);
          if (report && report.documentation) {
            result.documentation = report.documentation;
          }
        }

        return widget;
      }

      if (this.containerid) {
        const containerWidget = findContainer(this.allWidgets, this.containerid);
        if (containerWidget) {
          const result = { ...containerWidget };

          if (this.widgetized) {
            result.isFirstInPage = true;
            result.parameters = { ...result.parameters, widget: '1' };

            const widgets = getWidgetChildren(result);
            if (widgets) {
              (result as WidgetDataContainer).widgets = widgets.map((w) => ({
                ...w,
                parameters: {
                  ...w.parameters,
                  widget: '1',
                  containerId: this.containerid!,
                },
              }));
            }
          }

          return result;
        }
      }

      return null;
    },
  },
});
</script>
