<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-content-intro>
    <h2>
      <EnrichedHeadline>{{ translate('CorePluginsAdmin_PluginsManagement') }}</EnrichedHeadline>
    </h2>

    <p>
      {{ translate('CorePluginsAdmin_PluginsExtendPiwik') }}
      {{ translate('CorePluginsAdmin_OncePluginIsInstalledYouMayActivateHere') }}

      <span
        v-if="isMarketplaceEnabled || isPluginUploadEnabled"
        v-html="$sanitize(teaserExtendMatomoByPluginText)"
        style="margin-right:3.5px"
      ></span>

      <span v-if="!isPluginsAdminEnabled" style="margin-right:3.5px">
        <br/>{{ translate('CorePluginsAdmin_DoMoreContactPiwikAdmins') }}
      </span>

      <span v-html="$sanitize(changeLookByManageThemesText)"></span>
    </p>
  </div>
  <InstallAllPaidPluginsButton
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  ContentIntro,
  EnrichedHeadline,
  translate,
  MatomoUrl,
} from 'CoreHome';
import InstallAllPaidPluginsButton
  from '../InstallAllPaidPluginsButton/InstallAllPaidPluginsButton.vue';

export default defineComponent({
  props: {
    isMarketplaceEnabled: Boolean,
    isPluginUploadEnabled: Boolean,
    isPluginsAdminEnabled: Boolean,
  },
  components: {
    EnrichedHeadline,
    InstallAllPaidPluginsButton,
  },
  directives: {
    ContentIntro,
  },
  computed: {
    teaserExtendMatomoByPluginText() {
      const link = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'Marketplace',
        action: 'overview',
        sort: null,
        activated: null,
      })}`;

      return translate(
        'CorePluginsAdmin_TeaserExtendPiwikByPlugin',
        `<a href="${link}">`,
        '</a>',
        '<a href="#" class="uploadPlugin">',
        '</a>',
      );
    },
    changeLookByManageThemesText() {
      const link = `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        action: 'themes',
        activated: null,
      })}`;

      return translate(
        'CorePluginsAdmin_ChangeLookByManageThemes',
        `<a href="${link}">`,
        '</a>',
      );
    },
  },
});
</script>
