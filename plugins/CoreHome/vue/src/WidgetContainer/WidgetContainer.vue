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
          :allow-recursion="false"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import useExternalPluginComponent from '../useExternalPluginComponent';

// TODO: need to test dashboard removal/move/etc.
// TODO: allow-recursion

// since we're recursing, don't import the plugin directly
const Widget = useExternalPluginComponent('CoreHome', 'Widget');

interface WidgetData {
  viewDataTable: string;
  parameters: Record<string, unknown>;
}

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
