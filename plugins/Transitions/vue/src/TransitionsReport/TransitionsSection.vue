<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="transitionsSection">
    <div class="transitionsSection__header">
      <span class="transitionsSection__title" :title="section.title">{{ section.title }}</span>
      <span class="transitionsSection__badge" v-if="section.badge">{{ section.badge }}</span>
    </div>

    <div class="transitionsSection__rowList">
      <div class="transitionsSection__rowItem" v-for="row in section.rows" :key="row.key">
        <TransitionsRow
          :row="row"
          :side="section.side"
          :highlighted="highlightedKeys.includes(row.key)"
          @highlight="$emit('highlight', row)"
          @unhighlight="$emit('unhighlight')"
          @navigate="$emit('navigate', $event)"
          @open="$emit('open', $event)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import TransitionsRow from './TransitionsRow.vue';
import { TransitionsSectionData } from './types';

export default defineComponent({
  props: {
    section: {
      type: Object as PropType<TransitionsSectionData>,
      required: true,
    },
    highlightedKeys: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  components: {
    TransitionsRow,
  },
  emits: ['highlight', 'unhighlight', 'navigate', 'open'],
});
</script>
