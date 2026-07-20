<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="alert alert-warning bot-tracking-no-recent-requests-message"
    v-html="$sanitize(messageHtml)"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, MatomoUrl, translate } from 'CoreHome';

export default defineComponent({
  computed: {
    noDataUrl(): string {
      const { period, date } = MatomoUrl.parsed.value;
      const query = MatomoUrl.stringify({
        module: 'BotTracking',
        action: 'siteWithoutData',
        idSite: Matomo.idSite ?? MatomoUrl.parsed.value.idSite,
        period,
        date,
      });

      return `index.php?${query}`;
    },
    messageHtml(): string {
      const linkOpen = `<a href="${this.noDataUrl}">`;
      return translate(
        'BotTracking_NoRecentAIBotRequests',
        '<strong>',
        '</strong>',
        linkOpen,
        '</a>',
      );
    },
  },
});
</script>
