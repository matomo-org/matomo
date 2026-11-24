<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsColumn">
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
    <div class="transitionsColumn__loader" v-if="loading">
      <ActivityIndicator :loading="true" />
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { ActivityIndicator } from 'CoreHome';
import TransitionsSection from './TransitionsSection.vue';
import { TransitionsSectionData } from './types';

export default defineComponent({
  props: {
    sections: {
      type: Array as PropType<TransitionsSectionData[]>,
      required: true,
    },
    highlightedKeys: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    /** Set while some of the column's groups are still being requested. */
    loading: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    ActivityIndicator,
    TransitionsSection,
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open'],
});
</script>
