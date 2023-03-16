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
      :value="modelValue?.id"
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
          v-text="modelValue?.name || firstSiteName"
          v-if="modelValue?.name || !placeholder"
        />
        <span
          v-if="!modelValue?.name && placeholder"
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
          tabindex="4"
          class="websiteSearch inp browser-default"
          v-focus-if="{ focused: shouldFocusOnSearch }"
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
            @click="switchSite({ ...site, id: site.idsite }, $event)"
            v-show="!(!showSelectedSite && `${activeSiteId}` === `${site.idsite}`)"
            v-for="(site, index) in sites"
            :key="index"
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
import { DeepReadonly, defineComponent } from 'vue';
import FocusAnywhereButHere from '../FocusAnywhereButHere/FocusAnywhereButHere';
import FocusIf from '../FocusIf/FocusIf';
import AllSitesLink from './AllSitesLink.vue';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';
import { translate } from '../translate';
import SitesStore from './SitesStore';
import debounce from '../debounce';
import SiteRef from './SiteRef';
import Site from './Site';

interface SiteSelectorState {
  searchTerm: string;
  showSitesList: boolean;
  activeSiteId: string;
  isLoading: boolean;
  sites: DeepReadonly<Site[]>;
  autocompleteMinSites: number;
}

export default defineComponent({
  props: {
    modelValue: Object,
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
    defaultToFirstSite: Boolean,
    sitesToExclude: {
      type: Array,
      default: () => [] as number[],
    },
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
    searchTerm() {
      this.onSearchTermChanged();
    },
  },
  data(): SiteSelectorState {
    return {
      searchTerm: '',
      activeSiteId: `${Matomo.idSite}`,
      showSitesList: false,
      isLoading: false,
      sites: [],
      autocompleteMinSites: parseInt(Matomo.config.autocomplete_min_sites as string, 10),
    };
  },
  created() {
    this.searchSite = debounce(this.searchSite);

    if (!this.modelValue && Matomo.idSite) {
      this.$emit('update:modelValue', {
        id: Matomo.idSite,
        name: Matomo.helper.htmlDecode(Matomo.siteName),
      });
    }
  },
  mounted() {
    window.initTopControls();

    this.loadInitialSites().then(() => {
      if ((!this.modelValue || !this.modelValue.id)
        && (!this.hasMultipleSites || this.defaultToFirstSite)
        && this.sites[0]
      ) {
        this.$emit('update:modelValue', { id: this.sites[0].idsite, name: this.sites[0].name });
      }
    });

    const shortcutTitle = translate('CoreHome_ShortcutWebsiteSelector');
    Matomo.helper.registerShortcut('w', shortcutTitle, (event: KeyboardEvent) => {
      if (event.altKey) {
        return;
      }
      if (event.preventDefault) {
        event.preventDefault();
      } else {
        event.returnValue = false; // IE
      }

      const selectorLink = this.$refs.selectorLink as HTMLElement;
      if (selectorLink) {
        selectorLink.click();
        selectorLink.focus();
      }
    });
  },
  computed: {
    shouldFocusOnSearch() {
      return (this.showSitesList && this.autocompleteMinSites <= this.sites.length)
        || this.searchTerm;
    },
    selectorLinkTitle() {
      return this.hasMultipleSites
        ? translate('CoreHome_ChangeCurrentWebsite', this.modelValue?.name || this.firstSiteName)
        : '';
    },
    hasMultipleSites() {
      const initialSites = SitesStore.initialSitesFiltered.value
        && SitesStore.initialSitesFiltered.value.length
        ? SitesStore.initialSitesFiltered.value : SitesStore.initialSites.value;
      return initialSites && initialSites.length > 1;
    },
    firstSiteName() {
      const initialSites = SitesStore.initialSitesFiltered.value
        && SitesStore.initialSitesFiltered.value.length
        ? SitesStore.initialSitesFiltered.value : SitesStore.initialSites.value;
      return initialSites && initialSites.length > 0 ? initialSites[0].name : '';
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
    onSearchTermChanged() {
      if (!this.searchTerm) {
        this.isLoading = false;
        this.loadInitialSites();
      } else {
        this.isLoading = true;
        this.searchSite(this.searchTerm);
      }
    },
    onAllSitesClick(event: MouseEvent) {
      this.switchSite({ id: 'all', name: this.$props.allSitesText }, event);
      this.showSitesList = false;
    },
    switchSite(site: SiteRef, event: KeyboardEvent|MouseEvent) {
      // for Mac OS cmd key needs to be pressed, ctrl key on other systems
      const controlKey = navigator.userAgent.indexOf('Mac OS X') !== -1 ? event.metaKey : event.ctrlKey;

      if (event && controlKey && event.target && (event.target as HTMLLinkElement).href) {
        window.open((event.target as HTMLLinkElement).href, '_blank');
        return;
      }

      this.$emit('update:modelValue', { id: site.id, name: site.name });

      if (!this.switchSiteOnSelect || this.activeSiteId === site.id) {
        return;
      }

      SitesStore.loadSite(site.id);
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
    getMatchedSiteName(siteName: string) {
      const index = siteName.toUpperCase().indexOf(this.searchTerm.toUpperCase());
      if (index === -1
        || this.isLoading // only highlight when we know the displayed results are for a search
      ) {
        return Matomo.helper.htmlEntities(siteName);
      }

      const previousPart = Matomo.helper.htmlEntities(siteName.substring(0, index));
      const lastPart = Matomo.helper.htmlEntities(
        siteName.substring(index + this.searchTerm.length),
      );

      return `${previousPart}<span class="autocompleteMatched">${this.searchTerm}</span>${lastPart}`;
    },
    loadInitialSites() {
      return SitesStore.loadInitialSites(this.onlySitesWithAdminAccess,
        (this.sitesToExclude ? this.sitesToExclude : []) as number[]).then((sites) => {
        this.sites = sites || [];
      });
    },
    searchSite(term: string) {
      this.isLoading = true;

      SitesStore.searchSite(term, this.onlySitesWithAdminAccess,
        (this.sitesToExclude ? this.sitesToExclude : []) as number[]).then((sites) => {
        if (term !== this.searchTerm) {
          return; // search term changed in the meantime
        }

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
