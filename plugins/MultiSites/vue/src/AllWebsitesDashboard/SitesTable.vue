<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="sitesTableContainer">
    <table class="card-table dataTable sitesTable">
      <thead>
        <tr>
          <th
              @click="sortBy('label')"
              class="label"
          >
            {{ translate('General_Website') }}
            <span
                v-if="sortColumn === 'label'"
                :class="sortColumnClass"
            />
          </th>

          <th @click="sortBy('nb_visits')">
            <span
                v-if="sortColumn === 'nb_visits'"
                :class="sortColumnClass"
            />
            {{ translate('General_ColumnNbVisits') }}
          </th>

          <th @click="sortBy('nb_pageviews')">
            <span
                v-if="sortColumn === 'nb_pageviews'"
                :class="sortColumnClass"
            />
            {{ translate('General_ColumnPageviews') }}
          </th>

          <th @click="sortBy('hits')">
            <span
                v-if="sortColumn === 'hits'"
                :class="sortColumnClass"
            />
            {{ translate('General_ColumnHits') }}
          </th>

          <th v-if="displayRevenue"
              @click="sortBy('revenue')"
          >
            <span
                v-if="sortColumn === 'revenue'"
                :class="sortColumnClass"
            />
            {{ translate('General_ColumnRevenue') }}
          </th>

          <th @click="sortBy(evolutionSelector)">
            <span
                v-if="sortColumn === evolutionSelector"
                :class="sortColumnClass"
            />
            {{ translate('MultiSites_Evolution') }}
          </th>

          <th class="sitesTableEvolutionSelector">
            <select
                class="browser-default"
                :value="evolutionSelector"
                @change="changeEvolutionSelector($event.target.value)"
            >
              <option value="hits_evolution">
                {{ translate('General_ColumnHits')}}
              </option>
              <option value="visits_evolution">
                {{ translate('General_ColumnNbVisits') }}
              </option>
              <option value="pageviews_evolution">
                {{ translate('General_ColumnPageviews') }}
              </option>
              <option
                  value="revenue_evolution"
                  v-if="displayRevenue"
              >
                {{ translate('General_ColumnRevenue') }}
              </option>
            </select>
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="isLoading">
          <td class="sitesTableLoading" colspan="7">
            <MatomoLoader />
          </td>
        </tr>

        <SitesTableSite
            v-else
            v-for="site in sites"
            :display-revenue="displayRevenue"
            :evolution-metric="evolutionMetric"
            :key="`site-${site.idsite}`"
            :model-value="site"
            :display-sparkline="displaySparklines"
            :sparkline-metric="sparklineMetric"
        />
      </tbody>
    </table>
  </div>

  <div v-if="!isLoading || paginationUpperBound > 0"
       class="sitesTablePagination">
    <span
        class="dataTablePrevious"
        @click="navigatePreviousPage()"
        v-show="paginationCurrentPage !== 0"
    >
      &#xAB; {{ translate('General_Previous') }}
    </span>

    <span class="dataTablePages">
      {{ translate(
        'General_Pagination',
        paginationLowerBound,
        paginationUpperBound,
        numberOfFilteredSites,
      ) }}
    </span>

    <span
        class="dataTableNext"
        @click="navigateNextPage()"
        v-show="paginationCurrentPage < paginationMaxPage"
    >
      {{ translate('General_Next') }} &#xBB;
    </span>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Matomo, MatomoLoader } from 'CoreHome';

import DashboardStore from './AllWebsitesDashboard.store';
import SitesTableSite from './SitesTableSite.vue';
import { DashboardSiteData, DashboardSortOrder } from '../types';

interface SitesTableState {
  evolutionSelector: string;
}

export default defineComponent({
  components: {
    MatomoLoader,
    SitesTableSite,
  },
  props: {
    displayRevenue: {
      type: Boolean,
      required: true,
    },
    displaySparklines: {
      type: Boolean,
      required: true,
    },
  },
  data(): SitesTableState {
    return {
      evolutionSelector: 'visits_evolution',
    };
  },
  computed: {
    errorLoading(): boolean {
      return DashboardStore.state.value.errorLoading;
    },
    errorShowProfessionalHelp() {
      return Matomo.config && Matomo.config.are_ads_enabled;
    },
    evolutionMetric(): string {
      return this.evolutionSelector;
    },
    isLoading(): boolean {
      return DashboardStore.state.value.isLoadingSites;
    },
    numberOfFilteredSites() {
      return DashboardStore.state.value.numSites;
    },
    paginationCurrentPage(): number {
      return DashboardStore.state.value.paginationCurrentPage;
    },
    paginationLowerBound() {
      return DashboardStore.paginationLowerBound.value;
    },
    paginationUpperBound() {
      return DashboardStore.paginationUpperBound.value;
    },
    paginationMaxPage(): number {
      return DashboardStore.numberOfPages.value;
    },
    sites(): DashboardSiteData[] {
      return DashboardStore.state.value.dashboardSites as DashboardSiteData[];
    },
    sortColumn(): string {
      return DashboardStore.state.value.sortColumn;
    },
    sortColumnClass(): Record<string, boolean> {
      return {
        sitesTableSort: true,
        sitesTableSortAsc: this.sortOrder === 'asc',
        sitesTableSortDesc: this.sortOrder === 'desc',
      };
    },
    sortOrder(): DashboardSortOrder {
      return DashboardStore.state.value.sortOrder;
    },
    sparklineMetric(): string {
      switch (this.evolutionMetric) {
        case 'hits_evolution':
          return 'hits';
        case 'pageviews_evolution':
          return 'nb_pageviews';
        case 'revenue_evolution':
          return 'revenue';
        case 'visits_evolution':
          return 'nb_visits';
        default:
          return '';
      }
    },
  },
  methods: {
    changeEvolutionSelector(metric: string) {
      this.evolutionSelector = metric;

      this.sortBy(metric);
    },
    navigateNextPage() {
      DashboardStore.navigateNextPage();
    },
    navigatePreviousPage() {
      DashboardStore.navigatePreviousPage();
    },
    sortBy(column: string) {
      DashboardStore.sortBy(column);
    },
  },
});
</script>
