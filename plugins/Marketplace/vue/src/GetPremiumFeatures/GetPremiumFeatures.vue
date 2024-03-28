<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="getNewPlugins getPremiumFeatures widgetBody">
    <div
      class="row"
      v-for="(rowOfPlugins, index) in pluginRows"
      :key="index"
    >
      <div class="col s12 m12" v-if="index === 0">
        <h3 style="font-weight: bold;color: #5bb75b" v-html="$sanitize(trialHintsText)"></h3>
        <h3 style="margin-bottom: 28px;color: #5bb75b">
          {{ translate('Marketplace_SupportMatomoThankYou') }} <i class='icon-heart red-text'></i>
        </h3>
      </div>

      <div
        class="col s12 m4"
        v-for="plugin in rowOfPlugins"
        :key="plugin.name"
      >
        <h3 class="pluginName" v-plugin-name="{pluginName: plugin.name}">
          {{ plugin.displayName }}
        </h3>
        <span class="pluginSubtitle" v-if="plugin.specialOffer">
          <span>{{ translate('Marketplace_SpecialOffer') }}:</span> {{ plugin.specialOffer }}
        </span>
        <span class="pluginBody">
          {{ plugin.isBundle
            ? `${translate('Marketplace_SpecialOffer')}: `
            : '' }}{{ plugin.description }}
          <br />
          <a class="pluginMoreDetails" v-plugin-name="{pluginName: plugin.name}">
            {{ translate('General_MoreDetails') }}
          </a>
        </span>
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
import { MatomoUrl, translate, externalRawLink } from 'CoreHome';
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
    trialHintsText() {
      const link = externalRawLink('https://shop.matomo.org/free-trial/');
      const linkStyle = 'color:#5bb75b;text-decoration: underline;';
      return translate(
        'Marketplace_TrialHints',
        `<a style="${linkStyle}" href="${link}" target="_blank" rel="noreferrer noopener">`,
        '</a>',
      );
    },
    pluginRows() {
      // divide plugins array into rows of 3
      const result: unknown[][] = [];
      this.plugins.forEach((plugin, index) => {
        const row = Math.floor(index / 3);
        result[row] = result[row] || [];
        result[row].push(plugin);
      });
      return result;
    },
    overviewLink() {
      const query = MatomoUrl.stringify({ module: 'Marketplace', action: 'overview' });
      const hash = MatomoUrl.stringify({ pluginType: 'premium' });

      return `?${query}#?${hash}`;
    },
  },
});
</script>
