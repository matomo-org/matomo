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

  <KPICardContainer :model-value="kpis" />

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

import KPICardContainer from './KPICardContainer.vue';
import { KPICardData } from '../types';

export default defineComponent({
  components: {
    EnrichedHeadline,
    KPICardContainer,
    PeriodSelector,
  },
  props: {
    displayRevenue: {
      type: Boolean,
      required: true,
    },
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
    kpis(): KPICardData[] {
      const kpis: KPICardData[] = [
        {
          icon: 'icon-user',
          title: 'MultiSites_TotalVisits',
          value: '2,345',
          evolutionPeriod: 'last time',
          evolutionTrend: 1,
          evolutionValue: '1,234%',
        },
        {
          icon: 'icon-show',
          title: 'MultiSites_TotalPageviews',
          value: '3,456',
          evolutionPeriod: 'last time',
          evolutionTrend: 0,
          evolutionValue: '0,0%',
        },
        {
          icon: 'icon-hits',
          title: 'MultiSites_TotalHits',
          value: '2,345',
          evolutionPeriod: 'last time',
          evolutionTrend: -1,
          evolutionValue: '3,456%',
        },
      ];

      if (this.displayRevenue) {
        kpis.push({
          icon: 'icon-dollar-sign',
          title: 'General_TotalRevenue',
          value: '2,345',
          evolutionPeriod: 'last time',
          evolutionTrend: 0,
          evolutionValue: '0,0%',
        });
      }

      return kpis;
    },
    hasSuperUserAccess(): boolean {
      return Matomo.hasSuperUserAccess;
    },
  },
});
</script>
