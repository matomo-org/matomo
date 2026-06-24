<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="row sparklinesGrid">
    <div
      v-for="(sparkline, index) in flatSparklines"
      :key="index"
      :class="columnClasses"
    >
      <SparklineCard
        :sparkline="sparkline"
        :are-sparklines-linkable="areSparklinesLinkable"
      />
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  nextTick,
  onMounted,
  PropType,
} from 'vue';
import SparklineCard from './SparklineCard.vue';
import { SparklineEntry } from './types';

export default defineComponent({
  name: 'SparklinesGrid',
  components: {
    SparklineCard,
  },
  props: {
    sparklines: {
      type: Object as PropType<Record<string, SparklineEntry[]>>,
      required: true,
    },
    // From the backend for upcoming card-body work; not used yet.
    allMetricsDocumentation: {
      type: Object,
      default: () => ({}),
    },
    areSparklinesLinkable: {
      type: Boolean,
      default: true,
    },
    isWidget: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    // Object.values() iterates numeric keys in ascending order, not insertion order,
    // so flatten and re-sort by `order` to keep the backend's display order.
    const flatSparklines = computed<SparklineEntry[]>(
      () => ([] as SparklineEntry[])
        .concat(...Object.values(props.sparklines || {}))
        .sort((a, b) => a.order - b.order),
    );

    // Widgets show one column; reporting pages use a responsive grid (2/4/5 cols).
    // Keep xl3 so SparklinesGrid.less can widen it to 5 cols above 1920px.
    const columnClasses = computed(() => (props.isWidget ? 'col s12' : 'col s6 m6 l3 xl3'));

    onMounted(() => {
      // Re-wire each sparkline to its evolution graph once the cards are in the DOM.
      // Safe to re-run (it unbinds first); CoreHome ships sparkline.js in the global JS bundle.
      nextTick(() => {
        window.initializeSparklines();
      });
    });

    return {
      flatSparklines,
      columnClasses,
    };
  },
});
</script>
