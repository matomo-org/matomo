<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="getNewPlugins isAdminPage" ref="root">
    <div class="row">
      <div
           class="col s12 m4"
           v-for="plugin in plugins"
           :key="plugin.name"
      >
        <h3
          class="pluginName"
          :title="plugin.description"
          v-plugin-name="{pluginName: plugin.name}"
        >{{ plugin.displayName }}</h3>

        <p
          class="description"
          :title="plugin.description"
        >{{ plugin.description }}</p>

        <span v-if="plugin.screenshots?.length">
          <br />
          <img
            v-plugin-name="{pluginName: plugin.name}"
            class="screenshot"
            :src="`${plugin.screenshots[0]}?w=600`"
            style="width: 100%"
            alt=""
          />
        </span>
      </div>
    </div>

    <div class="widgetBody">
      <a :href="marketplaceOverviewLink">
        {{ translate('CorePluginsAdmin_ViewAllMarketplacePlugins') }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';
import { PluginName } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    plugins: {
      type: Array,
      required: true,
    },
  },
  directives: {
    PluginName,
  },
  computed: {
    marketplaceOverviewLink() {
      return `?${MatomoUrl.stringify({
        module: 'Marketplace',
        action: 'overview',
      })}`;
    },
  },
});
</script>
