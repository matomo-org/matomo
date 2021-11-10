<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="siteSelector piwikSelector borderedControl"
    :class="{'expanded': showSitesList, 'disabled': !model.hasMultipleSites()}"
    v-focus-anywhere-but-here="{ blur: onBlur.bind(this) }"
  >
    <input
      v-if="inputName"
      type="hidden"
      :value="selectedSite.id"
      :name="inputName"
    />
    <a
      @click="model.hasMultipleSites() && view.showSitesList = !view.showSitesList && !model.isLoading && model.loadInitialSites()"
      piwik-onenter="view.showSitesList=!view.showSitesList; view.showSitesList && !model.isLoading && model.loadInitialSites();"
      href="javascript:void(0)"
      :class="{'loading': model.isLoading}"
      class="title"
      tabindex="4"
      ::title="model.hasMultipleSites() ? _pk_translate('CoreHome_ChangeCurrentWebsite', selectedSite.name || model.firstSiteName) : ''"
    >
      <span
        class="icon icon-arrow-bottom"
        :class="{'iconHidden': model.isLoading, 'collapsed': !view.showSitesList}"
      />
      <span>
        <span
          v-text="selectedSite.name || model.firstSiteName"
          v-if="selectedSite.name || !placeholder"
        >?</span>
        <span
          v-if="!selectedSite.name && placeholder"
          class="placeholder"
        >{{ placeholder }}</span>
      </span>
    </a>
    <div
      v-show="view.showSitesList"
      class="dropdown"
    >
      <div
        class="custom_select_search"
        v-show="autocompleteMinSites &lt;= model.sites.length || view.searchTerm"
      >
        <input
          type="text"
          @click="view.searchTerm = ''"
          v-model="view.searchTerm"
          @change="model.searchSite(view.searchTerm)"
          tabindex="4"
          class="websiteSearch inp browser-default"
          v-focus-if="view.showSitesList && autocompleteMinSites &lt;= model.sites.length || view.searchTerm"
          :placeholder="translate('General_Search')"
        />
        <img
          title="Clear"
          v-show="view.searchTerm"
          @click="view.searchTerm = '';model.loadInitialSites()"
          class="reset"
          src="plugins/CoreHome/images/reset_search.png"
        />
      </div>
      <div v-if="allSitesLocation == 'top' && showAllSitesItem">
        <AllSitesLink
          :href="urlAllSites"
          :all-sites-text="allSitesText"
          @click="onAllSitesClick($event)"
        />
      </div>
      <div class="custom_select_container">
        <ul
          class="custom_select_ul_list"
          @click="view.showSitesList = false"
        >
          <li
            @click="switchSite(site, $event)"
            v-show="!(!showSelectedSite && activeSiteId == site.idsite)"
            v-for="site in model.sites"
          >
            <a
              piwik-ignore-click=""
              piwik-autocomplete-matched="view.searchTerm"
              v-text="site.name"
              tabindex="4"
              :href="getUrlForSiteId(site.idsite)"
              :title="site.name"
            />
          </li>
        </ul>
        <ul
          v-show="!model.sites.length && view.searchTerm"
          class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all siteSelect"
        >
          <li class="ui-menu-item">
            <a
              class="ui-corner-all"
              tabindex="-1"
            >{{ translate('SitesManager_NotFound') + ' ' + view.searchTerm }}</a>
          </li>
        </ul>
      </div>
      <div v-if="allSitesLocation == 'bottom' && showAllSitesItem">
        <AllSitesLink
          :href="urlAllSites"
          :all-sites-text="allSitesText"
          @click="onAllSitesClick($event)"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FocusAnywhereButHere from '../FocusAnywhereButHere/FocusAnywhereButHere';
import FocusIf from '../FocusIf/FocusIf';
import AllSitesLink from './AllSitesLink.vue';

interface SiteRef {
  idsite: string;
  name: string;
}

export default defineComponent({
  props: {
    showSelectedSite: String,
    showAllSitesItem: String,
    switchSiteOnSelect: String,
    onlySitesWithAdminAccess: String,
    inputName: String,
    allSitesText: String,
    allSitesLocation: String,
    placeholder: String,
  },
  components: {
    AllSitesLink,
  },
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  data() {
    return {
      showSitesList: false,
    };
  },
  methods: {
    onAllSitesClick(event: MouseEvent) {
      this.switchSite({ idsite: 'all', name: this.allSitesText }, event);
      this.showSitesList = false
    },
    switchSite(site: SiteRef, event: MouseEvent) {
      // TODO
    },
    onBlur() {
      this.showSitesList = false;
    },
  },
  computed: {
    urlAllSites() {
      // TODO
    },
  },
});
</script>
