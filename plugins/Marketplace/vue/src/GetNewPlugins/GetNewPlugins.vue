<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="getNewPlugins">
    <div class="row">
      <div
        class="col s12"
        v-for="(plugin, index) in plugins"
        :key="plugin.name"
      >
        <h3
          class="pluginName"
          v-plugin-name="{pluginName: plugin.name}"
        >{{ plugin.displayName }}</h3>

        <span>
          {{ plugin.description }}
          <br />
          <a v-plugin-name="{pluginName: plugin.name}">{{ translate('General_MoreDetails') }}</a>
        </span>

        <span v-if="index < plugins.length - 1"><br /><br /></span>
      </div>
    </div>

    <div class="widgetBody">
      <a :href="overviewLink">
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
    overviewLink() {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'overview',
      })}`;
    },
  },
});
</script>
