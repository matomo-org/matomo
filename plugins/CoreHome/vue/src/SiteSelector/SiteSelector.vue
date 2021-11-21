<!--
  Matomo - free/libre analytics platform
  @link https://matomo.org
  @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="siteSelector piwikSelector borderedControl"
    :class="{'expanded': showSitesList, 'disabled': !hasMultipleSites}"
    v-focus-anywhere-but-here="{ blur: onBlur }"
  >
    <input
      v-if="name"
      type="hidden"
      :value="selectedSite?.id"
      :name="name"
    />
    <a
      ref="selectorLink"
      @click="onClickSelector"
      @keydown="onPressEnter($event)"
      href="javascript:void(0)"
      :class="{'loading': isLoading}"
      class="title"
      tabindex="4"
      :title="selectorLinkTitle"
    >
      <span
        class="icon icon-arrow-bottom"
        :class="{'iconHidden': isLoading, 'collapsed': !showSitesList}"
      />
      <span>
        <span
          v-text="decodedName || firstSiteNameDecoded"
          v-if="selectedSite?.name || !placeholder"
        />
        <span
          v-if="!selectedSite?.name && placeholder"
          class="placeholder"
        >{{ placeholder }}</span>
      </span>
    </a>
    <div
      v-show="showSitesList"
      class="dropdown"
    >
      <div
        class="custom_select_search"
        v-show="autocompleteMinSites <= sites.length || searchTerm"
      >
        <input
          type="text"
          @click="searchTerm = '';loadInitialSites()"
          v-model="searchTerm"
          @keydown="onSearchInputKeydown()"
          tabindex="4"
          class="websiteSearch inp browser-default"
          v-focus-if:[shouldFocusOnSearch]="{}"
          :placeholder="translate('General_Search')"
        />
        <img
          title="Clear"
          v-show="searchTerm"
          @click="searchTerm = '';loadInitialSites()"
          class="reset"
          src="plugins/CoreHome/images/reset_search.png"
        />
      </div>
      <div v-if="allSitesLocation === 'top' && showAllSitesItem">
        <AllSitesLink
          :href="urlAllSites"
          :all-sites-text="allSitesText"
          @click="onAllSitesClick($event)"
        />
      </div>
      <div class="custom_select_container">
        <ul
          class="custom_select_ul_list"
          @click="showSitesList = false"
        >
          <li
            @click="switchSite(site, $event)"
            v-show="!(!showSelectedSite && activeSiteId === site.idsite)"
            v-for="site in sites"
            :key="site.idsite"
          >
            <a
              @click="$event.preventDefault()"
              v-html="$sanitize(getMatchedSiteName(site.name))"
              tabindex="4"
              :href="getUrlForSiteId(site.idsite)"
              :title="site.name"
            />
          </li>
        </ul>
        <ul
          v-show="!sites.length && searchTerm"
          class="ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all
                 siteSelect"
        >
          <li class="ui-menu-item">
            <a
              class="ui-corner-all"
              tabindex="-1"
            >
              {{ translate('SitesManager_NotFound') + ' ' + searchTerm }}
            </a>
          </li>
        </ul>
      </div>
      <div v-if="allSitesLocation === 'bottom' && showAllSitesItem">
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
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import translate from '../translate';
import SitesStore, { Site } from './SitesStore';
import debounce from '../debounce';

interface SiteRef {
  id: string|number;
  name: string;
}

interface SiteSelectorState {
  searchTerm: string;
  showSitesList: boolean;
  isLoading: boolean;
  sites: Site[];
  selectedSite: SiteRef;
  autocompleteMinSites: null|number;
}

export default defineComponent({
  props: {
    modelValue: {
      Object,
      default: {
        id: Matomo.idSite,
        name: Matomo.siteName,
      },
    },
    showSelectedSite: {
      type: Boolean,
      default: false,
    },
    showAllSitesItem: {
      type: Boolean,
      default: true,
    },
    switchSiteOnSelect: {
      type: Boolean,
      default: true,
    },
    onlySitesWithAdminAccess: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: '',
    },
    allSitesText: {
      type: String,
      default: translate('General_MultiSitesSummary'),
    },
    allSitesLocation: {
      type: String,
      default: 'bottom',
    },
    placeholder: String,
  },
  emits: ['update:modelValue', 'blur'],
  components: {
    AllSitesLink,
  },
  directives: {
    FocusAnywhereButHere,
    FocusIf,
  },
  watch: {
    modelValue: {
      handler(newValue) {
        this.selectedSite = { ...newValue };
      },
      deep: true,
    },
  },
  data(): SiteSelectorState {
    return {
      searchTerm: '',
      activeSiteId: Matomo.idSite,
      showSitesList: false,
      isLoading: false,
      sites: [],
      selectedSite: {
        id: Matomo.idSite,
        name: Matomo.siteName,
      },
      autocompleteMinSites: parseInt(Matomo.config.autocomplete_min_sites as string, 10),
    };
  },
  mounted() {
    window.initTopControls();

    this.loadInitialSites().then(() => {
      if ((!this.selectedSite || !this.selectedSite.id) && this.sites[0]) {
        this.selectedSite = { id: this.sites[0].idsite, name: this.sites[0].name };
        this.$emit('update:modelValue', { ...this.selectedSite });
      }
    });

    const shortcutTitle = translate('CoreHome_ShortcutWebsiteSelector');
    Matomo.helper.registerShortcut('w', shortcutTitle, (event) => {
      if (event.altKey) {
        return;
      }
      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false; // IE
      }
      this.$refs.selectorLink.click();
      this.$refs.selectorLink.focus();
    });
  },
  created() {
    this.onSearchInputKeydown = debounce(this.onSearchInputKeydown.bind(this));
  },
  computed: {
    decodedName() {
      if (!this.selectedSite) {
        return null;
      }

      return Matomo.helper.htmlDecode(this.selectedSite.name);
    },
    shouldFocusOnSearch() {
      return (this.showSitesList && this.autocompleteMinSites <= this.sites.length)
        || this.searchTerm;
    },
    selectorLinkTitle() {
      return this.hasMultipleSites
        ? translate('CoreHome_ChangeCurrentWebsite', this.selectedSite?.name || this.firstSiteName)
        : '';
    },
    hasMultipleSites() {
      return SitesStore.initialSites.value && SitesStore.initialSites.value.length > 1;
    },
    firstSiteName() {
      return this.sites && this.sites.length > 0 ? this.sites[0].name : '';
    },
    firstSiteNameDecoded() {
      return Matomo.helper.htmlDecode(this.selectedSite.name);
    },
    urlAllSites() {
      const newQuery = MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        module: 'MultiSites',
        action: 'index',
        date: MatomoUrl.parsed.value.date,
        period: MatomoUrl.parsed.value.period,
      });
      return `?${newQuery}`;
    },
  },
  methods: {
    onAllSitesClick(event: MouseEvent) {
      this.switchSite({ idsite: 'all', name: this.allSitesText }, event);
      this.showSitesList = false;
    },
    switchSite(site: SiteRef, event: KeyboardEvent|MouseEvent) {
      // for Mac OS cmd key needs to be pressed, ctrl key on other systems
      const controlKey = navigator.userAgent.indexOf('Mac OS X') !== -1 ? event.metaKey : event.ctrlKey;

      if (event && controlKey && event.target && (event.target as HTMLLinkElement).href) {
        window.open((event.target as HTMLLinkElement).href, '_blank');
        return;
      }

      this.selectedSite = { id: site.idsite, name: site.name };
      this.$emit('update:modelValue', { ...this.selectedSite });

      if (!this.switchSiteOnSelect || this.activeSiteId === site.idsite) {
        return;
      }

      SitesStore.loadSite(site.idsite);
    },
    onBlur() {
      this.showSitesList = false;
      this.$emit('blur');
    },
    onClickSelector() {
      if (this.hasMultipleSites) {
        this.showSitesList = !this.showSitesList;

        if (!this.isLoading && !this.searchTerm) {
          this.loadInitialSites();
        }
      }
    },
    onPressEnter(event: KeyboardEvent) {
      if (event.key !== 'Enter') {
        return;
      }

      event.preventDefault();

      this.showSitesList = !this.showSitesList;
      if (this.showSitesList && !this.isLoading) {
        this.loadInitialSites();
      }
    },
    onSearchInputKeydown() {
      setTimeout(() => {
        this.searchSite(this.searchTerm);
      });
    },
    getMatchedSiteName(siteName: string) {
      const index = siteName.toUpperCase().indexOf(this.searchTerm.toUpperCase());
      if (index === -1) {
        return Matomo.helper.htmlEntities(siteName);
      }

      const previousPart = Matomo.helper.htmlEntities(siteName.substring(0, index));
      const lastPart = Matomo.helper.htmlEntities(
        siteName.substring(index + this.searchTerm.length),
      );

      return `${previousPart}<span class="autocompleteMatched">${this.searchTerm}</span>${lastPart}`;
    },
    loadInitialSites() {
      return SitesStore.loadInitialSites().then((sites) => {
        this.sites = sites || [];
      });
    },
    searchSite(term: string) {
      this.isLoading = true;

      SitesStore.searchSite(term, this.onlySitesWithAdminAccess).then((sites) => {
        if (sites) {
          this.sites = sites;
        }
      }).finally(() => {
        this.isLoading = false;
      });
    },
    getUrlForSiteId(idSite: string|number) {
      const newQuery = MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        segment: '',
        idSite,
      });

      const newHash = MatomoUrl.stringify({
        ...MatomoUrl.hashParsed.value,
        segment: '',
        idSite,
      });

      return `?${newQuery}#?${newHash}`;
    },
  },
});
</script>
