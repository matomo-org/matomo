<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <article class="pluginCard pluginCard--skeleton" aria-hidden="true">
    <div class="pluginCard__plate pluginCardSkeleton__block" />
    <div class="pluginCard__chips">
      <span class="pluginCardSkeleton__chip" />
      <span class="pluginCardSkeleton__chip pluginCardSkeleton__chip--short" />
    </div>
    <div class="pluginCardSkeleton__block pluginCardSkeleton__title" />
    <div class="pluginCardSkeleton__textBlock">
      <span class="pluginCardSkeleton__textLine" />
      <span class="pluginCardSkeleton__textLine" />
      <span class="pluginCardSkeleton__textLine pluginCardSkeleton__textLine--short" />
    </div>
    <div class="pluginCardSkeleton__block pluginCardSkeleton__meta" />
    <div class="pluginCardSkeleton__block pluginCardSkeleton__action" />
  </article>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * Holds a card's footprint while the catalogue loads. Its outer geometry has to match PluginCard
 * or the grid reflows the moment real cards arrive.
 */
export default defineComponent({});
</script>
