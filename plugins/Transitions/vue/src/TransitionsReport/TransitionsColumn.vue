<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsColumn" :class="`transitionsColumn--${side}`">
    <div class="transitionsColumn__sectionItem" v-for="section in sections" :key="section.key">
      <TransitionsSection
        :section="section"
        :highlighted-keys="highlightedKeys"
        @highlight="$emit('highlight', $event)"
        @unhighlight="$emit('unhighlight')"
        @navigate="$emit('navigate', $event)"
        @open="$emit('open', $event)"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import TransitionsSection from './TransitionsSection.vue';
import { TransitionsSectionData, TransitionsSide } from './types';

export default defineComponent({
  props: {
    side: {
      type: String as PropType<TransitionsSide>,
      required: true,
    },
    sections: {
      type: Array as PropType<TransitionsSectionData[]>,
      required: true,
    },
    highlightedKeys: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  components: {
    TransitionsSection,
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open'],
});
</script>
