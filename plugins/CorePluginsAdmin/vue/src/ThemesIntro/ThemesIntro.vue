<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline>{{ translate('CorePluginsAdmin_ThemesManagement') }}</EnrichedHeadline>
    </h2>

    <p>
      {{ translate('CorePluginsAdmin_ThemesDescription') }}

      <span v-if="isMarketplaceEnabled" v-html="$sanitize(teaserExtendByThemeText)"></span>

      <span v-if="otherUsersCount > 0">
        <br/>
        {{ translate(
          'CorePluginsAdmin_InfoThemeIsUsedByOtherUsersAsWell',
          otherUsersCount,
          themeEnabled,
        ) }}
      </span>

      <span v-if="!isPluginsAdminEnabled">
        <br/>{{ translate('CorePluginsAdmin_DoMoreContactPiwikAdmins') }}
      </span>
    </p>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  EnrichedHeadline,
  ContentIntro,
  MatomoUrl,
} from 'CoreHome';

export default defineComponent({
  props: {
    isMarketplaceEnabled: Boolean,
    otherUsersCount: Number,
    themeEnabled: Boolean,
    isPluginsAdminEnabled: Boolean,
  },
  components: {
    EnrichedHeadline,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    teaserExtendByThemeText() {
      const query = MatomoUrl.stringify({ module: 'Marketplace', action: 'overview' });
      const hash = MatomoUrl.stringify({ pluginType: 'themes' });
      const link = `?${query}#?${hash}`;

      return translate(
        'CorePluginsAdmin_TeaserExtendPiwikByTheme',
        `<a href="${link}">`,
        '</a>',
      );
    },
  },
});
</script>
