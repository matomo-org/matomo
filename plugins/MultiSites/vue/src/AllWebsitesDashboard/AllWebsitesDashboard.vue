<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="dashboardHeader">
    <h1 class="card-title">
      <EnrichedHeadline
          :feature-name="translate('MultiSites_AllWebsitesDashboardTitle')"
      >
        {{ translate('MultiSites_AllWebsitesDashboardTitle') }}
      </EnrichedHeadline>
    </h1>

    <div v-if="!isWidgetized"
         id="periodString"
         class="borderedControl"
    >
      <PeriodSelector :periods="selectablePeriods" />
    </div>
  </div>

  <div class="dashboardControls">
    <div class="siteSearch">
      <input
          type="text"
          :placeholder="translate('Actions_SubmenuSitesearch')"
      />

      <span
          class="icon-search"
          :title="translate('General_ClickToSearch')"
      />
    </div>

    <a v-if="!isWidgetized && hasSuperUserAccess"
       class="btn"
       :href="addSiteUrl"
    >
      {{ translate('SitesManager_AddSite') }}
    </a>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  EnrichedHeadline,
  Matomo,
  MatomoUrl,
  PeriodSelector,
} from 'CoreHome';

export default defineComponent({
  components: {
    EnrichedHeadline,
    PeriodSelector,
  },
  props: {
    isWidgetized: {
      type: Boolean,
      required: true,
    },
    selectablePeriods: {
      type: Array,
      required: true,
    },
  },
  computed: {
    addSiteUrl(): string {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...MatomoUrl.hashParsed.value,
        module: 'SitesManager',
        action: 'index',
        showaddsite: '1',
      })}`;
    },
    hasSuperUserAccess(): boolean {
      return Matomo.hasSuperUserAccess;
    },
  },
});
</script>
