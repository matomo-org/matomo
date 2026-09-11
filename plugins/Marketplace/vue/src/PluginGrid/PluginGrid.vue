<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="pluginGrid">
    <PluginCard
      v-for="plugin in visiblePlugins"
      :key="plugin.name"
      :plugin="plugin"
      :context="context"
      @openDetails="$emit('openDetails', $event)"
      @requestTrial="$emit('requestTrial', $event)"
      @startFreeTrial="$emit('startFreeTrial', $event)"
    />
    <PluginCardSkeleton v-for="index in skeletonCount" :key="`skeleton-${index}`" />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import PluginCard from '../PluginCard/PluginCard.vue';
import PluginCardSkeleton from '../PluginCard/PluginCardSkeleton.vue';
import { MarketplaceContext, PluginCard as PluginCardType } from '../types';

export default defineComponent({
  props: {
    plugins: {
      type: Array as PropType<PluginCardType[]>,
      required: true,
    },
    /** At most this many cards, for a section showing one row; `null` renders every plugin. */
    maxCards: {
      type: Number as PropType<number|null>,
      default: null,
    },
    skeletonCount: {
      type: Number,
      default: 0,
    },
    context: { type: Object as PropType<MarketplaceContext>, required: true },
  },
  components: {
    PluginCard,
    PluginCardSkeleton,
  },
  emits: ['openDetails', 'requestTrial', 'startFreeTrial'],
  computed: {
    visiblePlugins(): PluginCardType[] {
      return this.maxCards === null ? this.plugins : this.plugins.slice(0, this.maxCards);
    },
  },
});
</script>
