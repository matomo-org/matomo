<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="pluginGrid"
    :class="{ 'pluginGrid--singleRow': singleRow }"
  >
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
import { SINGLE_ROW_MAX_CARDS } from './visibleCardCount';

export default defineComponent({
  props: {
    plugins: {
      type: Array as PropType<PluginCardType[]>,
      required: true,
    },
    /** Render at most one row, for a curated section. */
    singleRow: {
      type: Boolean,
      default: false,
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
      return this.singleRow ? this.plugins.slice(0, SINGLE_ROW_MAX_CARDS) : this.plugins;
    },
  },
});
</script>
