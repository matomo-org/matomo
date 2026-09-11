<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <template
    v-if="plugin.missingRequirements.length === 0 && plugin.isDownloadable && !isAutoUpdatePossible"
  >
    <span onclick="$(this).css('display', 'none')">
      <a tabindex="7"
         :class="['plugin-details', 'download', { 'btn btn-block': showAsButton }]"
         :href="linkTo({
            module: 'Marketplace',
            action: 'download',
            pluginName: plugin.name,
            nonce: plugin.downloadNonce,
          })"
      >{{ translate('General_Download') }}</a></span>
  </template>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl } from 'CoreHome';

export default defineComponent({
  props: {
    plugin: {
      type: Object,
      required: true,
    },
    /** Render as a button, for the card's action row, where a bare link reads as body text. */
    showAsButton: {
      type: Boolean,
      default: false,
    },
    isAutoUpdatePossible: {
      type: Boolean,
      required: true,
    },
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        idSite: MatomoUrl.parsed.value.idSite,
        ...params,
      })}`;
    },
  },
});
</script>
