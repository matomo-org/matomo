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

  <KPICardContainer
      :is-loading="isLoadingKPIs"
      :model-value="kpis"
  />

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
import { defineComponent, watch } from 'vue';
import {
  EnrichedHeadline,
  Matomo,
  MatomoUrl,
  PeriodSelector,
} from 'CoreHome';

import DashboardStore from './AllWebsitesDashboard.store';
import KPICardContainer from './KPICardContainer.vue';
import { KPICardData } from '../types';

export default defineComponent({
  components: {
    EnrichedHeadline,
    KPICardContainer,
    PeriodSelector,
  },
  props: {
    autoRefreshInterval: {
      type: Number,
      required: true,
    },
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
  mounted() {
    watch(() => MatomoUrl.hashParsed.value, () => DashboardStore.refreshData());

    DashboardStore.setAutoRefreshInterval(this.autoRefreshInterval);
    DashboardStore.refreshData();
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
    isLoadingKPIs(): boolean {
      return DashboardStore.state.value.isLoadingKPIs;
    },
    kpis(): KPICardData[] {
      const { dashboardKPIs } = DashboardStore.state.value;

      const kpis: KPICardData[] = [
        {
          icon: 'icon-user',
          title: 'MultiSites_TotalVisits',
          value: dashboardKPIs.visits,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.visitsTrend,
          evolutionValue: dashboardKPIs.visitsEvolution,
        },
        {
          icon: 'icon-show',
          title: 'MultiSites_TotalPageviews',
          value: dashboardKPIs.pageviews,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.pageviewsTrend,
          evolutionValue: dashboardKPIs.pageviewsEvolution,
        },
        {
          icon: 'icon-hits',
          title: 'MultiSites_TotalHits',
          value: dashboardKPIs.hits,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.hitsTrend,
          evolutionValue: dashboardKPIs.hitsEvolution,
        },
      ];

      if (this.displayRevenue) {
        kpis.push({
          icon: 'icon-dollar-sign',
          title: 'General_TotalRevenue',
          value: dashboardKPIs.revenue,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.revenueTrend,
          evolutionValue: dashboardKPIs.revenueEvolution,
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
