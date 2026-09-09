<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <section class="marketplaceSection">
    <div class="marketplaceSection__header">
      <h2 class="marketplaceSection__heading">{{ heading }}</h2>

      <button
        v-if="showSeeAll"
        type="button"
        class="marketplaceSection__seeAll"
        :aria-label="translate('Marketplace_SeeAllInCategory', heading)"
        @click="$emit('seeAll', sectionId)"
      >
        <span>{{ translate('Marketplace_SeeAll') }}</span>
        <span class="icon-chevron-right" aria-hidden="true" />
      </button>
    </div>

    <PluginGrid
      single-row
      :plugins="plugins"
      :is-super-user="isSuperUser"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :is-multi-server-environment="isMultiServerEnvironment"
      :is-valid-consumer="isValidConsumer"
      :is-auto-update-possible="isAutoUpdatePossible"
      :activate-nonce="activateNonce"
      :deactivate-nonce="deactivateNonce"
      :install-nonce="installNonce"
      :update-nonce="updateNonce"
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
import { PluginCard as PluginCardType } from '../types';
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
    /**
     * Every plugin in the section, not only the ones the row has room for. The row does its own
     * cut, and the count is what decides whether "See all" is worth showing.
     */
    plugins: {
      type: Array as PropType<PluginCardType[]>,
      required: true,
    },
    isAutoUpdatePossible: { type: Boolean, required: true },
    isSuperUser: { type: Boolean, required: true },
    isValidConsumer: { type: Boolean, required: true },
    isMultiServerEnvironment: { type: Boolean, required: true },
    isPluginsAdminEnabled: { type: Boolean, required: true },
    activateNonce: { type: String, required: true },
    deactivateNonce: { type: String, required: true },
    installNonce: { type: String, required: true },
    updateNonce: { type: String, required: true },
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
     * Only when the row is leaving something out.
     *
     * Compared against what the row is showing at this width, not against the list the grid was
     * handed: below three columns the stylesheet shows two rows, and above them as few as two
     * cards, so a fixed threshold would hide the link on a section with plugins still cut off.
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
