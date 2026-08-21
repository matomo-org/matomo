<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <svg
    class="transitionsRibbons"
    ref="layer"
    aria-hidden="true"
    focusable="false"
  >
    <defs>
      <linearGradient :id="gradientId" :x1="gradientX1" y1="0" :x2="gradientX2" y2="0">
        <stop class="transitionsRibbons__stopOuter" :class="outerStopClass" offset="0" />
        <stop class="transitionsRibbons__stopInner" :class="innerStopClass" offset="1" />
      </linearGradient>
    </defs>
    <path
      class="transitionsRibbons__band"
      :class="bandClasses(path.key)"
      v-for="path in paths"
      :key="path.key"
      :d="path.d"
      :fill="`url(#${gradientId})`"
    />
  </svg>
</template>

<script lang="ts">
import {
  defineComponent,
  PropType,
  ref,
  toRef,
} from 'vue';
import {
  ElementSource,
  RibbonSource,
  useRibbonGeometry,
} from './useRibbonGeometry';
import { TransitionsSide } from './types';

// Gradient ids must be unique per mounted layer; two layers share every page, and a page may hold
// more than one report.
let gradientSequence = 0;

export default defineComponent({
  props: {
    side: {
      type: String as PropType<TransitionsSide>,
      required: true,
    },
    rows: {
      type: Array as PropType<RibbonSource[]>,
      required: true,
    },
    /** Resolves the column holding the rows these ribbons connect to. */
    column: {
      type: Function as PropType<ElementSource>,
      required: true,
    },
    /** Resolves the center card the ribbons converge on. */
    center: {
      type: Function as PropType<ElementSource>,
      required: true,
    },
    highlightedKeys: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  setup(props) {
    const layer = ref<SVGSVGElement|null>(null);

    gradientSequence += 1;
    const gradientId = `transitionsRibbonsGradient-${props.side}-${gradientSequence}`;

    const { paths, recompute, teardown } = useRibbonGeometry({
      side: props.side,
      layer,
      column: () => props.column(),
      center: () => props.center(),
      rows: toRef(props, 'rows'),
    });

    return {
      layer,
      paths,
      gradientId,
      recompute,
      teardown,
    };
  },
  computed: {
    isOutgoing(): boolean {
      return this.side === 'outgoing';
    },
    /** The deep end of the gradient sits on the column side, which flips with the side. */
    gradientX1(): number {
      return this.isOutgoing ? 1 : 0;
    },
    gradientX2(): number {
      return this.isOutgoing ? 0 : 1;
    },
    outerStopClass(): string {
      return this.isOutgoing ? 'transitionsRibbons__stopOuter--outgoing' : '';
    },
    innerStopClass(): string {
      return this.isOutgoing ? 'transitionsRibbons__stopInner--outgoing' : '';
    },
  },
  methods: {
    bandClasses(key: string): Record<string, boolean> {
      return {
        'transitionsRibbons__band--outgoing': this.side === 'outgoing',
        'transitionsRibbons__band--highlighted': this.highlightedKeys.includes(key),
      };
    },
  },
});
</script>
