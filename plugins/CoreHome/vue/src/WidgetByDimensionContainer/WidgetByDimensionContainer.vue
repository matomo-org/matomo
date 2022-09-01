<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="reportsByDimensionView">
    <div class="entityList">
      <div
        class="dimensionCategory"
        v-for="category in widgetsByCategory"
        :key="category.name"
      >
        {{ category.name }}
        <ul class="listCircle">
          <li
            class="reportDimension"
            v-for="widget in category.widgets"
            :key="widget.uniqueId"
            :class="{ activeDimension: selectedWidget.uniqueId === widget.uniqueId }"
            @click="selectWidget(widget)"
          >
            <span class="dimension">{{ widget.name }}</span>
          </li>
        </ul>
      </div>
    </div>
    <div class="reportContainer">
      <WidgetLoader
        v-if="selectedWidget.parameters"
        :widget-params="selectedWidget.parameters"
        class="dimensionReport"
      />
    </div>
    <div class="clear" />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import WidgetLoader from '../WidgetLoader/WidgetLoader.vue';
import { Widget } from '../Widget/types';
import { sortOrderables } from '../Orderable';

interface WidgetByDimensionContainerState {
  selectedWidget: Widget|null;
}

interface WidgetCategory {
  name: string;
  order: number;
  widgets: Widget[];
}

export default defineComponent({
  props: {
    widgets: Array,
  },
  components: {
    WidgetLoader,
  },
  data(): WidgetByDimensionContainerState {
    return {
      selectedWidget: null,
    };
  },
  created() {
    [this.selectedWidget] = this.widgetsSorted;
  },
  computed: {
    widgetsSorted(): Widget[] {
      return sortOrderables(this.widgets as Widget[]);
    },
    widgetsByCategory() {
      const byCategory: Record<string, WidgetCategory> = {};

      this.widgetsSorted.forEach((widget) => {
        const category = widget.subcategory?.name;
        if (!category) {
          return;
        }

        if (!byCategory[category]) {
          byCategory[category] = { name: category, order: widget.order, widgets: [] };
        }

        byCategory[category].widgets!.push(widget);
      });

      return sortOrderables(Object.values(byCategory));
    },
  },
  methods: {
    selectWidget(widget: Widget) {
      // we copy to force rerender if selecting same widget
      this.selectedWidget = { ...widget };
    },
  },
});
</script>
