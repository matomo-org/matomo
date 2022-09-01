<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div
      v-for="(widget, index) in actualContainer"
      :key="index"
    >
      <div>
        <Widget
          :widget="widget"
          :prevent-recursion="true"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import { Widget as WidgetData } from '../Widget/types';

// since we're recursing, don't import the plugin directly
const Widget = useExternalPluginComponent('CoreHome', 'Widget');

export default defineComponent({
  props: {
    container: {
      type: Array,
      required: true,
    },
  },
  components: {
    Widget,
  },
  computed: {
    actualContainer() {
      const container = this.container as WidgetData[];

      if (!container?.[0]?.parameters) {
        return container;
      }

      const [widget] = container;
      const isWidgetized = widget.parameters?.widget === '1' || widget.parameters?.widget === 1;

      const isGraphEvolution = isWidgetized && widget.viewDataTable === 'graphEvolution';

      // we hide the first title for Visits Overview with Graph and Goal Overview
      const firstWidget = isGraphEvolution
        ? { ...widget, parameters: { ...widget.parameters, showtitle: '0' } }
        : widget;

      return [
        firstWidget,
        ...container.slice(1),
      ];
    },
  },
});
</script>
