<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="pluginDetailsSkeleton" aria-hidden="true">
    <div class="pluginDetailsSkeleton__head">
      <div class="pluginDetailsSkeleton__cover" />
      <div class="pluginDetailsSkeleton__info">
        <div class="pluginDetailsSkeleton__title" />
        <div class="pluginDetailsSkeleton__textBlock">
          <span class="pluginDetailsSkeleton__textLine" />
          <span class="pluginDetailsSkeleton__textLine pluginDetailsSkeleton__textLine--short" />
        </div>
        <div class="pluginDetailsSkeleton__facts">
          <span class="pluginDetailsSkeleton__chipItem" />
          <span class="pluginDetailsSkeleton__chipItem pluginDetailsSkeleton__chipItem--short" />
          <span class="pluginDetailsSkeleton__fact" />
          <span class="pluginDetailsSkeleton__fact" />
        </div>
      </div>
    </div>

    <div class="pluginDetailsSkeleton__columns">
      <div class="pluginDetailsSkeleton__main">
        <div class="pluginDetailsSkeleton__card">
          <div class="pluginDetailsSkeleton__cardTitle" />
          <div class="pluginDetailsSkeleton__textBlock">
            <span
              class="pluginDetailsSkeleton__textLine"
              v-for="line in 6"
              :key="`line-${line}`"
            />
            <span class="pluginDetailsSkeleton__textLine pluginDetailsSkeleton__textLine--short" />
          </div>
        </div>

        <div class="pluginDetailsSkeleton__card">
          <div class="pluginDetailsSkeleton__cardTitle" />
          <div class="pluginDetailsSkeleton__shots">
            <span
              class="pluginDetailsSkeleton__shot"
              v-for="shot in 3"
              :key="`shot-${shot}`"
            />
          </div>
        </div>
      </div>

      <div class="pluginDetailsSkeleton__aside">
        <div class="pluginDetailsSkeleton__card pluginDetailsSkeleton__buy">
          <div class="pluginDetailsSkeleton__price" />
          <div class="pluginDetailsSkeleton__action" />
        </div>

        <div class="pluginDetailsSkeleton__card">
          <div class="pluginDetailsSkeleton__cardTitle" />
          <div class="pluginDetailsSkeleton__meta">
            <span
              class="pluginDetailsSkeleton__metaRow"
              v-for="row in 5"
              :key="`row-${row}`"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

/**
 * Holds the plugin page's shape while its details are fetched. Its geometry has to match
 * PluginDetails, which is why both read the `@marketplace-details-*` variables rather than one
 * styling the other's DOM - the same arrangement PluginCard and PluginCardSkeleton use.
 */
export default defineComponent({});
</script>
