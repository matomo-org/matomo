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
import { Orderable } from '../ReportingMenu/ReportingMenu.store';
import { WidgetData } from '../Widget/Widgets.store';

// TODO: is there a widget category ID or widget ID

function sortOrderables<T extends Orderable>(orderables: T[]):T[] {
  const sorted = [...orderables];
  sorted.sort((lhs, rhs) => {
    if (lhs.order < rhs.order) {
      return -1;
    }

    if (lhs.order > rhs.order) {
      return 1;
    }

    return 0;
  });
  return sorted;
}

export default defineComponent({
  props: {
    widgets: Array,
  },
  components: {
    WidgetLoader,
  },
  data() {
    const { widgetsSorted } = this;

    return {
      selectedWidget: widgetsSorted[0],
    };
  },
  computed: {
    widgetsSorted(): WidgetData[] {
      return sortOrderables(this.widgets);
    },
    widgetsByCategory() {
      const byCategory = {};

      this.widgetsSorted.forEach((widget) => {
        const category = widget.subcategory.name;

        if (!byCategory[category]) {
          byCategory[category] = { name: category, order: widget.order, widgets: [] };
        }

        byCategory[category].widgets.push(widget);
      });

      const result = sortOrderables(Object.values(byCategory));
      return result;
    },
  },
  methods: {
    selectWidget(widget: WidgetData) {
      // we copy to force rerender if selecting same widget
      this.selectedWidget = { ...widget };
    },
  },
});
</script>
