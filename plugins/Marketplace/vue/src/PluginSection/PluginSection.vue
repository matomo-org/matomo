<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <section class="pluginSection">
    <div class="pluginSection__header">
      <h2 class="pluginSection__heading">{{ heading }}</h2>

      <button
        v-if="showSeeAll"
        type="button"
        class="pluginSection__seeAll"
        :aria-label="translate('Marketplace_SeeAllInCategory', heading)"
        @click="$emit('seeAll', sectionId)"
      >
        <span>{{ translate('Marketplace_SeeAll') }}</span>
        <span class="icon-chevron-right" aria-hidden="true" />
      </button>
    </div>

    <PluginGrid
      :max-cards="visibleCards"
      :plugins="plugins"
      :context="context"
      @openDetails="$emit('openDetails', $event)"
      @requestTrial="$emit('requestTrial', $event)"
      @startFreeTrial="$emit('startFreeTrial', $event)"
    />
  </section>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { translate } from 'CoreHome';
import PluginGrid from '../PluginGrid/PluginGrid.vue';
import { MarketplaceContext, PluginCard as PluginCardType } from '../types';
import { tabLabel } from '../PluginGrid/categoryLabels';
import {
  observeVisibleCardCount,
  SINGLE_ROW_MAX_CARDS,
  visibleCardCount,
} from '../PluginGrid/visibleCardCount';

export interface PluginSectionState {
  visibleCards: number;
  unobserve: (() => void)|null;
}

export default defineComponent({
  props: {
    /** The tab this section links to: `bundles`, `themes`, a category slug, or `other`. */
    sectionId: {
      type: String,
      required: true,
    },
    /** Mirrors `PluginTab.isCategory`, and decides how the heading resolves its label. */
    isCategory: {
      type: Boolean,
      default: false,
    },
    /** Every plugin in the section; the row is cut to {@link visibleCards}, "See all" is not. */
    plugins: {
      type: Array as PropType<PluginCardType[]>,
      required: true,
    },
    context: { type: Object as PropType<MarketplaceContext>, required: true },
  },
  components: {
    PluginGrid,
  },
  emits: ['openDetails', 'requestTrial', 'startFreeTrial', 'seeAll'],
  data(): PluginSectionState {
    return {
      visibleCards: SINGLE_ROW_MAX_CARDS,
      unobserve: null,
    };
  },
  mounted() {
    this.visibleCards = visibleCardCount();
    this.unobserve = observeVisibleCardCount((count: number) => {
      this.visibleCards = count;
    });
  },
  unmounted() {
    if (this.unobserve) {
      this.unobserve();
    }
  },
  computed: {
    heading(): string {
      return tabLabel({ id: this.sectionId, isCategory: this.isCategory });
    },
    /**
     * Only when the row is leaving something out, measured against what this width shows rather
     * than a fixed threshold - the row runs from two to five cards depending on the breakpoint.
     */
    showSeeAll(): boolean {
      return this.plugins.length > this.visibleCards;
    },
  },
  methods: {
    translate,
  },
});
</script>
