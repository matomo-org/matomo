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
    // Passed through from the backend for the upcoming card-body work; not used yet.
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
    // getSortedSparklines() groups entries by key and the backend already sorts them by
    // `order`. The keys are numeric (the metric index), and Object.values() iterates
    // numeric keys in ascending order rather than insertion order, which would silently
    // undo that sort - so flatten and then re-sort by `order` to restore display order.
    const flatSparklines = computed<SparklineEntry[]>(
      () => ([] as SparklineEntry[])
        .concat(...Object.values(props.sparklines || {}))
        .sort((a, b) => a.order - b.order),
    );

    // Dashboard/embedded widgets render a single column; reporting pages use the
    // responsive 4/3/2/1 Materialize grid (xl3/l4/m6/s12).
    const columnClasses = computed(() => (props.isWidget ? 'col s12' : 'col s12 m6 l3 xl3'));

    onMounted(() => {
      // The grid mounts asynchronously, so re-run the global wiring that links each
      // sparkline to its evolution graph once the cards exist in the DOM. The handler
      // unbinds before binding, so re-invoking it is safe. initializeSparklines is always
      // present in the browser — CoreHome loads sparkline.js on every page.
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
