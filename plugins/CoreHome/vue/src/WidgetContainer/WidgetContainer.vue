<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div>
    <div
      v-for="(widget, index) in container.widgets"
      :key="index"
    >
      <div>
        <Widget
          v-bind="widget"
          :parameters="getModifiedParameters(widget)"
          :prevent-recursion="true"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';
import { Widget as WidgetData } from '../Widget/Widgets.store';

// TODO: need to test dashboard removal/move/etc.

// since we're recursing, don't import the plugin directly
const Widget = useExternalPluginComponent('CoreHome', 'Widget');

export default defineComponent({
  props: {
    container: Array,
  },
  components: {
    Widget,
  },
  methods: {
    getModifiedParameters(widget: WidgetData) {
      const isWidgetized = widget.parameters.widget === '1';
      const isGraphEvolution = isWidgetized && widget.viewDataTable === 'graphEvolution';
      return {
        ...widget.parameters,
        // we hide the first title for Visits Overview with Graph and Goal Overview
        ...(isGraphEvolution && { showtitle: '0' }),
      };
    },
  },
});
</script>
