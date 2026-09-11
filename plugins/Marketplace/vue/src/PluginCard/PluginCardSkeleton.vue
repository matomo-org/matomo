<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <article class="pluginCardSkeleton" aria-hidden="true">
    <div class="pluginCardSkeleton__plate" />
    <div class="pluginCardSkeleton__chipList">
      <span class="pluginCardSkeleton__chipItem" />
      <span class="pluginCardSkeleton__chipItem pluginCardSkeleton__chipItem--short" />
    </div>
    <div class="pluginCardSkeleton__title" />
    <div class="pluginCardSkeleton__textBlock">
      <span class="pluginCardSkeleton__textLine" />
      <span class="pluginCardSkeleton__textLine" />
      <span class="pluginCardSkeleton__textLine pluginCardSkeleton__textLine--short" />
    </div>
    <div class="pluginCardSkeleton__meta" />
    <div class="pluginCardSkeleton__action" />
  </article>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * Holds a card's footprint while the catalogue loads. Its geometry has to match PluginCard, which
 * is why both read the `@marketplace-card-*` variables rather than one styling the other's DOM.
 */
export default defineComponent({});
</script>
