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
    <PluginCardSkeleton v-for="index in skeletonCount" :key="`skeleton-${index}`" />
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import PluginCard from '../PluginCard/PluginCard.vue';
import PluginCardSkeleton from '../PluginCard/PluginCardSkeleton.vue';
import { PluginCard as PluginCardType } from '../types';

export default defineComponent({
  props: {
    plugins: {
      type: Array as PropType<PluginCardType[]>,
      required: true,
    },
    /**
     * Render at most this many cards, for a curated section that shows one row. `null` renders
     * every plugin it was handed.
     */
    maxCards: {
      type: Number as PropType<number|null>,
      default: null,
    },
    skeletonCount: {
      type: Number,
      default: 0,
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
    PluginCard,
    PluginCardSkeleton,
  },
  emits: ['openDetails', 'requestTrial', 'startFreeTrial'],
  computed: {
    visiblePlugins(): PluginCardType[] {
      return null === this.maxCards ? this.plugins : this.plugins.slice(0, this.maxCards);
    },
  },
});
</script>
