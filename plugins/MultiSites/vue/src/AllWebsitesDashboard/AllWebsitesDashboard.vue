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
          @keydown.enter="searchSite(searchTerm)"
          v-model="searchTerm"
          :placeholder="translate('Actions_SubmenuSitesearch')"
      />

      <span
          class="icon-search"
          @click="searchSite(searchTerm)"
          :title="translate('General_ClickToSearch')"
      />
    </div>

    <a v-if="!isWidgetized && isUserAllowedToAddSite"
       class="btn"
       :href="addSiteUrl"
    >
      {{ translate('SitesManager_AddSite') }}
    </a>
  </div>

  <SitesTable
      :display-revenue="displayRevenue"
      :display-sparklines="displaySparklines"
  />
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
import SitesTable from './SitesTable.vue';
import { KPICardData } from '../types';

interface DashboardState {
  searchTerm: string;
}

export default defineComponent({
  components: {
    EnrichedHeadline,
    KPICardContainer,
    PeriodSelector,
    SitesTable,
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
    displaySparklines: {
      type: Boolean,
      required: true,
    },
    isWidgetized: {
      type: Boolean,
      required: true,
    },
    pageSize: {
      type: Number,
      required: true,
    },
    selectablePeriods: {
      type: Array,
      required: true,
    },
  },
  data(): DashboardState {
    return {
      searchTerm: '',
    };
  },
  mounted() {
    watch(() => MatomoUrl.hashParsed.value, () => DashboardStore.refreshData());

    DashboardStore.setAutoRefreshInterval(this.autoRefreshInterval);
    DashboardStore.setPageSize(this.pageSize);
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
          badge: dashboardKPIs.visitsBadge,
          icon: 'icon-user',
          title: 'MultiSites_TotalVisits',
          value: dashboardKPIs.visits,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.visitsTrend,
          evolutionValue: dashboardKPIs.visitsEvolution,
        },
        {
          badge: dashboardKPIs.pageviewsBadge,
          icon: 'icon-show',
          title: 'MultiSites_TotalPageviews',
          value: dashboardKPIs.pageviews,
          evolutionPeriod: dashboardKPIs.evolutionPeriod,
          evolutionTrend: dashboardKPIs.pageviewsTrend,
          evolutionValue: dashboardKPIs.pageviewsEvolution,
        },
        {
          badge: dashboardKPIs.hitsBadge,
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
          badge: dashboardKPIs.revenueBadge,
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
    isUserAllowedToAddSite(): boolean {
      return Matomo.hasSuperUserAccess;
    },
  },
  methods: {
    searchSite(term: string) {
      DashboardStore.searchSite(term);
    },
  },
});
</script>
