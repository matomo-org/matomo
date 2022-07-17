<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div ref="root">
    <h2 class="card-title">
      <EnrichedHeadline
        help-url="https://matomo.org/faq/new-to-piwik/all-websites-dashboard/"
        :feature-name="translate('General_AllWebsitesDashboard')"
      >
        {{ translate('General_AllWebsitesDashboard') }}
        <span
          class="smallTitle"
          v-html="$sanitize(this.smallTitleContent)"
          :title="smallTitleTooltip"
        >
        </span>
      </EnrichedHeadline>
    </h2>
    <table
      id="mt"
      class="dataTable card-table"
      cellspacing="0"
    >
      <thead>
        <tr>
          <th
            id="names"
            class="label"
            @click="sortBy('label')"
            :class="{columnSorted: 'label' === sortColumn}"
          >
            <span class="heading">{{ translate('General_Website') }}</span>
            <span
              class="arrow"
              :class="{
                multisites_asc: !reverse && 'label' === sortColumn,
                multisites_desc: reverse && 'label' === sortColumn,
              }"
              style="margin-left: 3.5px"
            />
          </th>
          <th
            id="visits"
            class="multisites-column"
            @click="sortBy('nb_visits')"
            :class="{columnSorted: 'nb_visits' === sortColumn}"
          >
            <span
              class="arrow"
              :class="{
                multisites_asc: !reverse && 'nb_visits' === sortColumn,
                multisites_desc: reverse && 'nb_visits' === sortColumn,
              }"
              style="margin-right: 3.5px"
            />
            <span class="heading">{{ translate('General_ColumnNbVisits') }}</span>
          </th>
          <th
            id="pageviews"
            class="multisites-column"
            @click="sortBy('nb_pageviews')"
            :class="{columnSorted: 'nb_pageviews' === sortColumn}"
          >
            <span
              class="arrow"
              :class="{
                multisites_asc: !reverse && 'nb_pageviews' === sortColumn,
                multisites_desc: reverse && 'nb_pageviews' === sortColumn,
              }"
              style="margin-right: 3.5px"
            />
            <span class="heading">{{ translate('General_ColumnPageviews') }}</span>
          </th>
          <th
            id="revenue"
            class="multisites-column"
            v-if="displayRevenueColumn"
            @click="sortBy('revenue')"
            :class="{columnSorted: 'revenue' === sortColumn}"
          >
            <span
              class="arrow"
              :class="{
                multisites_asc: !reverse && 'revenue' === sortColumn,
                multisites_desc: reverse && 'revenue' === sortColumn,
              }"
              style="margin-right: 3.5px"
            />
            <span class="heading">{{ translate('General_ColumnRevenue') }}</span>
          </th>
          <th
            id="evolution"
            :class="{columnSorted: evolutionSelector === sortColumn}"
            :colspan="showSparklines ? 2 : 1"
          >
            <span
              class="arrow"
              :class="{
                multisites_asc: !reverse && evolutionSelector === sortColumn,
                multisites_desc: reverse && evolutionSelector === sortColumn,
              }"
              style="margin-right: 3.5px"
            />
            <span
              class="evolution"
              @click="sortBy(evolutionSelector)"
              style="margin-right: 3.5px"
            > {{ translate('MultiSites_Evolution') }}</span>
            <select
              class="selector browser-default"
              id="evolution_selector"
              :value="evolutionSelector"
              @change="evolutionSelector = $event.target.value; sortBy(evolutionSelector)"
            >
              <option value="visits_evolution">{{ translate('General_ColumnNbVisits') }}</option>
              <option value="pageviews_evolution">
                {{ translate('General_ColumnPageviews') }}
              </option>
              <option
                value="revenue_evolution"
                v-if="displayRevenueColumn"
              >
                {{ translate('General_ColumnRevenue') }}
              </option>
            </select>
          </th>
        </tr>
      </thead>
      <tbody v-if="isLoading">
        <tr>
          <td
            colspan="7"
            class="allWebsitesLoading"
          >
            <ActivityIndicator
              :loading-message="loadingMessage"
              :loading="isLoading"
            />
          </td>
        </tr>
      </tbody>
      <tbody v-if="!isLoading">
        <tr v-if="errorLoadingSites">
          <td colspan="7">
            <div class="notification system notification-error">
              {{ translate('General_ErrorRequest', '', '') }}
              <br /><br />
              {{ translate('General_NeedMoreHelp') }}
              <a
                rel="noreferrer noopener"
                target="_blank"
                href="https://matomo.org/faq/troubleshooting/faq_19489/"
              >{{ translate('General_Faq') }}</a>
              &#x2013;
              <a
                rel="noreferrer noopener"
                target="_blank"
                href="https://forum.matomo.org/"
              >{{ translate('Feedback_CommunityHelp') }}</a>
              <span v-show="areAdsForProfessionalServicesEnabled"> &#x2013; </span>
              <a
                rel="noreferrer noopener"
                target="_blank"
                :href="professionalHelpUrl"
                v-show="areAdsForProfessionalServicesEnabled"
              >{{ translate('Feedback_ProfessionalHelp') }}</a>.
            </div>
          </td>
        </tr>
        <MultisitesSite
          v-for="website in sites"
          :key="website.idsite"
          :website="website"
          :evolution-metric="evolutionSelector"
          :date-sparkline="dateSparkline"
          :show-sparklines="showSparklines"
          :metric="sortColumn"
          :display-revenue-column="displayRevenueColumn"
        >
        </MultisitesSite>
      </tbody>
      <tfoot>
        <tr>
          <td
            colspan="8"
            class="paging"
          >
            <div class="row">
              <div class="col s3 add_new_site">
                <a
                  :href="addSiteUrl"
                  v-if="hasSuperUserAccess"
                >
                  <span class="icon-add" /> {{ translate('SitesManager_AddSite') }}
                </a>
              </div>
              <div class="col s6">
                <span
                  id="prev"
                  class="previous dataTablePrevious"
                  @click="previousPage()"
                  v-show="!(currentPage === 0)"
                >
                  <span style="cursor:pointer;">&#xAB; {{ translate('General_Previous') }}</span>
                </span>
                <span class="dataTablePages">
                  <span id="counter">
                    {{ translate(
                      'General_Pagination',
                      paginationLowerBound,
                      paginationUpperBound,
                      numberOfFilteredSites,
                    ) }}
                  </span>
                </span>
                <span
                  id="next"
                  class="next dataTableNext"
                  @click="nextPage()"
                  v-show="!(currentPage >= numberOfPages)"
                >
                  <span
                    style="cursor:pointer;"
                    class="pointer"
                  >{{ translate('General_Next') }} &#xBB;</span>
                </span>
              </div>
              <div class="col s3">&nbsp;</div>
            </div>
          </td>
        </tr>
        <tr row_id="last">
          <td
            colspan="8"
            class="site_search"
          >
            <div class="row">
              <div class="input-field col s12">
                <input
                  type="text"
                  @keydown.enter="searchSite(searchTerm)"
                  v-model="searchTerm"
                  :placeholder="translate('Actions_SubmenuSitesearch')"
                />
                <span
                  class="icon-search search_ico"
                  @click="searchSite(searchTerm)"
                  :title="translate('General_ClickToSearch')"
                />
              </div>
            </div>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import {
  translate,
  Matomo,
  EnrichedHeadline,
  ActivityIndicator,
  MatomoUrl,
  getFormattedEvolution,
} from 'CoreHome';
import MultisitesSite from '../MultisitesSite/MultisitesSite.vue';
import DashboardStore from './Dashboard.store';

interface DashboardState {
  evolutionSelector: string;
  searchTerm: string;
}

export default defineComponent({
  props: {
    displayRevenueColumn: Boolean,
    showSparklines: Boolean,
    dateSparkline: String,
    pageSize: Number,
    autoRefreshTodayReport: Number,
  },
  components: {
    EnrichedHeadline,
    ActivityIndicator,
    MultisitesSite,
  },
  data(): DashboardState {
    return {
      evolutionSelector: 'visits_evolution',
      searchTerm: '',
    };
  },
  created() {
    if (this.pageSize) {
      DashboardStore.setPageSize(this.pageSize);
    }
    this.refresh(this.autoRefreshTodayReport);
  },
  methods: {
    refresh(interval?: number) {
      DashboardStore.setRefreshInterval(interval);
      DashboardStore.fetchAllSites();
    },
    sortBy(column: string) {
      DashboardStore.sortBy(column);
    },
    previousPage() {
      DashboardStore.previousPage();
    },
    nextPage() {
      DashboardStore.nextPage();
    },
    searchSite() {
      DashboardStore.searchSite(this.searchTerm);
    },
  },
  computed: {
    hasSuperUserAccess() {
      return Matomo.hasSuperUserAccess;
    },
    date() {
      return MatomoUrl.urlParsed.value.date as string;
    },
    idSite() {
      return MatomoUrl.urlParsed.value.idSite as string;
    },
    url() {
      return Matomo.piwik_url;
    },
    period() {
      return Matomo.period;
    },
    areAdsForProfessionalServicesEnabled() {
      return Matomo.config && Matomo.config.are_ads_enabled;
    },
    sortColumn() {
      return DashboardStore.state.value.sortColumn;
    },
    reverse() {
      return DashboardStore.state.value.reverse;
    },
    smallTitleContent() {
      const state = DashboardStore.state.value;
      return translate(
        'General_TotalVisitsPageviewsActionsRevenue',
        `<strong>${state.totalVisits}</strong>`,
        `<strong>${state.totalPageviews}</strong>`,
        `<strong>${state.totalActions}</strong>`,
        `<strong>${state.totalRevenue}</strong>`,
      );
    },
    smallTitleTooltip() {
      const state = DashboardStore.state.value;
      return translate(
        'General_EvolutionSummaryGeneric',
        translate('General_NVisits', `${state.totalVisits}`),
        this.date,
        `${state.lastVisits}`,
        state.lastVisitsDate,
        getFormattedEvolution(state.totalVisits, state.lastVisits),
      );
    },
    loadingMessage() {
      return DashboardStore.state.value.loadingMessage;
    },
    isLoading() {
      return DashboardStore.state.value.isLoading;
    },
    errorLoadingSites() {
      return DashboardStore.state.value.errorLoadingSites;
    },
    sites() {
      return DashboardStore.state.value.sites;
    },
    numberOfPages() {
      return DashboardStore.numberOfPages.value;
    },
    currentPage() {
      return DashboardStore.state.value.currentPage;
    },
    paginationLowerBound() {
      return DashboardStore.paginationLowerBound.value;
    },
    paginationUpperBound() {
      return DashboardStore.paginationUpperBound.value;
    },
    numberOfFilteredSites() {
      return DashboardStore.numberOfFilteredSites.value;
    },
    professionalHelpUrl() {
      return 'https://matomo.org/support-plans/?pk_campaign=Help&pk_medium=AjaxError&pk_content='
        + 'MultiSites&pk_source=Matomo_App';
    },
    addSiteUrl() {
      return `index.php?module=SitesManager&action=index&showaddsite=1&period=${this.period}&`
        + `date=${this.date}&idSite=${this.idSite}`;
    },
  },
});
</script>
