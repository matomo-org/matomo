<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="marketplacePage" ref="root">
    <RequestTrial
      v-model="showRequestTrialForPlugin"
      @trialRequested="refresh()"
    />

    <StartFreeTrial
      :current-user-email="currentUserEmail"
      :is-valid-consumer="isValidConsumer"
      v-model="showStartFreeTrialForPlugin"
      @trialStarted="refresh()"
      @startTrialStart="$emit('startTrialStart')"
      @startTrialStop="$emit('startTrialStop')"
    />

    <PluginDetailsModal
      v-model="showPluginDetailsForPlugin"
      :is-super-user="isSuperUser"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :is-multi-server-environment="isMultiServerEnvironment"
      :is-valid-consumer="isValidConsumer"
      :is-auto-update-possible="isAutoUpdatePossible"
      :has-some-admin-access="hasSomeAdminAccess"
      :deactivate-nonce="deactivateNonce"
      :activate-nonce="activateNonce"
      :install-nonce="installNonce"
      :update-nonce="updateNonce"
      :num-users="numUsers"
      @requestTrial="showRequestTrialForPlugin = $event"
      @startFreeTrial="showStartFreeTrialForPlugin = $event"
    />

    <MarketplaceHero
      :model-value="searchQuery"
      @update:model-value="updateQuery($event)"
    />

    <CategoryTabs
      v-if="tabs.length > 1"
      ref="categoryTabs"
      :tabs="tabs"
      :model-value="activeTab"
      @update:model-value="updateTab($event)"
    />

    <div
      class="marketplacePage__resultsBar"
      :class="{ 'marketplacePage__resultsBar--empty': !resultsHeading && !showSort }"
      ref="resultsBar"
    >
      <div class="marketplacePage__resultsCount" aria-live="polite">
        <h2 class="marketplacePage__resultsHeading" v-if="resultsHeading">{{ resultsHeading }}</h2>
      </div>
      <SortMenu
        v-if="showSort"
        :model-value="pluginSort"
        @update:model-value="updateSort($event)"
      />
    </div>

    <div class="marketplacePage__sections" v-if="showSections">
      <PluginSection
        v-for="section in sections"
        :key="section.id"
        :section-id="section.id"
        :is-category="section.isCategory"
        :plugins="section.plugins"
        :context="cardContext"
        @seeAll="seeAllInSection($event)"
        @openDetails="openDetailsModal($event)"
        @requestTrial="showRequestTrialForPlugin = $event"
        @startFreeTrial="showStartFreeTrialForPlugin = $event"
      />
    </div>

    <PluginGrid
      v-if="!showSections && (loading || filteredPlugins.length > 0)"
      :plugins="pagedPlugins"
      :skeleton-count="skeletonCount"
      :context="cardContext"
      @openDetails="openDetailsModal($event)"
      @requestTrial="showRequestTrialForPlugin = $event"
      @startFreeTrial="showStartFreeTrialForPlugin = $event"
    />

    <EmptyState
      v-if="!loading && !loadFailed && filteredPlugins.length === 0"
      @reset="resetFilters()"
    />

    <div class="marketplacePage__loadError" v-if="loadFailed && !loading">
      <div class="alert alert-danger">{{ translate('Marketplace_PluginsNotAvailable') }}</div>
    </div>

    <div class="marketplacePage__sentinel" ref="sentinel" />
  </div>
</template>

<script lang="ts">
import { defineComponent, markRaw, watch } from 'vue';
import {
  AjaxHelper,
  debounce,
  Matomo,
  MatomoUrl,
  translate,
} from 'CoreHome';
import MarketplaceHero from '../MarketplaceHero/MarketplaceHero.vue';
import CategoryTabs from '../CategoryTabs/CategoryTabs.vue';
import SortMenu from '../SortMenu/SortMenu.vue';
import PluginGrid from '../PluginGrid/PluginGrid.vue';
import PluginSection from '../PluginSection/PluginSection.vue';
import EmptyState from '../PluginGrid/EmptyState.vue';
import RequestTrial from '../RequestTrial/RequestTrial.vue';
import StartFreeTrial from '../StartFreeTrial/StartFreeTrial.vue';
import PluginDetailsModal from '../PluginDetailsModal/PluginDetailsModal.vue';
import { MarketplaceContext, PluginCard } from '../types';
import { tabLabel } from '../PluginGrid/categoryLabels';
import {
  buildSections,
  buildTabs,
  filterPlugins,
  PluginSection as PluginSectionType,
  PluginTab,
  SORT_LAST_UPDATED,
  sortPlugins,
  TAB_ALL,
  TYPE_TABS,
  tabFromLegacyPluginType,
} from '../PluginGrid/pluginGrouping';

/** How many cards a filtered view adds at a time. */
const PAGE_SIZE = 15;

/** Placeholder cards to hold the layout on a cold load. */
const INITIAL_SKELETONS = 10;

export interface MarketplaceState {
  loading: boolean;
  loadFailed: boolean;
  allPlugins: PluginCard[];
  pluginSort: string;
  activeTab: string;
  searchQuery: string;
  pageSize: number;
  paginated: boolean;
  showRequestTrialForPlugin: PluginCard|null;
  showStartFreeTrialForPlugin: PluginCard|null;
  showPluginDetailsForPlugin: PluginCard|null;
  observer: IntersectionObserver|null;
  fetchAbortController: AbortController|null;
}

export default defineComponent({
  props: {
    defaultSort: {
      type: String,
      required: true,
    },
    currentUserEmail: {
      type: String,
      default: '',
    },
    isValidConsumer: Boolean,
    isSuperUser: Boolean,
    isAutoUpdatePossible: Boolean,
    isPluginsAdminEnabled: Boolean,
    isMultiServerEnvironment: Boolean,
    hasSomeAdminAccess: Boolean,
    installNonce: { type: String, required: true },
    activateNonce: { type: String, required: true },
    deactivateNonce: { type: String, required: true },
    updateNonce: { type: String, required: true },
    numUsers: { type: Number, required: true },
  },
  components: {
    MarketplaceHero,
    CategoryTabs,
    SortMenu,
    PluginGrid,
    PluginSection,
    EmptyState,
    RequestTrial,
    StartFreeTrial,
    PluginDetailsModal,
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  data(): MarketplaceState {
    return {
      loading: true,
      loadFailed: false,
      allPlugins: [],
      pluginSort: this.defaultSort || SORT_LAST_UPDATED,
      activeTab: TAB_ALL,
      searchQuery: '',
      pageSize: PAGE_SIZE,
      paginated: true,
      showRequestTrialForPlugin: null,
      showStartFreeTrialForPlugin: null,
      showPluginDetailsForPlugin: null,
      observer: null,
      fetchAbortController: null,
    };
  },
  created() {
    this.pushQueryToHash = debounce(this.pushQueryToHash.bind(this), 250);
  },
  mounted() {
    Matomo.postEvent('Marketplace.Marketplace.mounted', { element: this.$refs.root });

    this.readStateFromHash();
    watch(() => MatomoUrl.hashParsed.value, () => this.readStateFromHash());
    watch(() => MatomoUrl.hashParsed.value.showPlugin, () => this.openDeepLinkedPlugin());

    this.fetchCatalogue();
    this.observeSentinel();
  },
  unmounted() {
    Matomo.postEvent('Marketplace.Marketplace.unmounted', { element: this.$refs.root });
    if (this.observer) {
      this.observer.disconnect();
    }

    if (this.fetchAbortController) {
      this.fetchAbortController.abort();
    }
  },
  computed: {
    tabs(): PluginTab[] {
      return buildTabs(this.allPlugins);
    },
    /**
     * The section stack, each row sorted the way the whole catalogue is, so that a row is the
     * first cards of the category it links to rather than a differently ordered sample.
     */
    sections(): PluginSectionType[] {
      return buildSections(this.allPlugins).map((section) => ({
        ...section,
        plugins: sortPlugins(section.plugins, this.pluginSort),
      }));
    },
    /**
     * Whether the page shows the section stack rather than one flat grid. Only on All plugins with
     * nothing searched for: a tab or a query asks for one list, and ten rows would bury it. While
     * loading there are no sections, so the flat grid holds the layout with its skeletons.
     */
    showSections(): boolean {
      return !this.loading
        && this.activeTab === TAB_ALL
        && !this.searchQuery.trim()
        && this.sections.length > 0;
    },
    /** What every grid on the page needs to render a card, gathered once. */
    cardContext(): MarketplaceContext {
      return {
        isSuperUser: this.isSuperUser,
        isPluginsAdminEnabled: this.isPluginsAdminEnabled,
        isMultiServerEnvironment: this.isMultiServerEnvironment,
        isValidConsumer: this.isValidConsumer,
        isAutoUpdatePossible: this.isAutoUpdatePossible,
        activateNonce: this.activateNonce,
        deactivateNonce: this.deactivateNonce,
        installNonce: this.installNonce,
        updateNonce: this.updateNonce,
      };
    },
    filteredPlugins(): PluginCard[] {
      return sortPlugins(
        filterPlugins(this.allPlugins, this.activeTab, this.searchQuery),
        this.pluginSort,
      );
    },
    /**
     * A page at a time, but only while the sentinel can say the reader reached the bottom. Without
     * an IntersectionObserver nothing would ever ask for the next page, so the grid renders every
     * result instead of stopping at the first fifteen for good.
     */
    pagedPlugins(): PluginCard[] {
      if (!this.paginated) {
        return this.filteredPlugins;
      }

      return this.filteredPlugins.slice(0, this.pageSize);
    },
    skeletonCount(): number {
      return this.loading ? INITIAL_SKELETONS : 0;
    },
    /**
     * What the list below is: a search's result count, or the name of the open category. All
     * plugins names nothing, since its rows carry their own headings. A category names itself
     * while still loading - the label comes from the tab, so waiting would shift the skeletons.
     */
    resultsHeading(): string {
      if (this.searchQuery.trim()) {
        return this.loading ? '' : translate(
          'Marketplace_ResultsFoundFor',
          this.filteredPlugins.length,
          this.searchQuery,
        );
      }
      if (this.activeTab === TAB_ALL) {
        return '';
      }

      const tab = this.tabs.find((candidate) => candidate.id === this.activeTab);
      return tabLabel(tab ?? {
        id: this.activeTab,
        isCategory: !TYPE_TABS.includes(this.activeTab),
      });
    },
    /**
     * Sorting is offered over a single list only. The section stack is ten lists at once, each cut
     * to a row, so sorting there would change what the rows hold with no visible reordering.
     */
    showSort(): boolean {
      return !this.showSections && this.filteredPlugins.length > 0;
    },
  },
  methods: {
    translate,

    /**
     * The whole catalogue, in the two requests the Marketplace keeps warm.
     *
     * `Api\Client::getWarmedOverviewLists()` holds only ['plugins', ALL], ['plugins', PAID] and
     * ['themes', ALL] for 90 minutes. Anything varying query, sort or purchase type is a different
     * cache key and misses all three, so do not add a parameter here: nothing breaks loudly, every
     * tab click just becomes a cold catalogue download.
     */
    fetchCatalogue() {
      this.loading = true;
      this.loadFailed = false;
      if (this.fetchAbortController) {
        this.fetchAbortController.abort();
      }

      const abortController = markRaw(new AbortController());
      this.fetchAbortController = abortController;

      const warmedRequest = (themesOnly: boolean) => AjaxHelper.post(
        { module: 'Marketplace', action: 'searchPlugins', format: 'JSON' },
        {
          query: '',
          sort: SORT_LAST_UPDATED,
          purchaseType: '',
          themesOnly,
        },
        { withTokenInUrl: true, abortController },
      );

      return Promise.all([warmedRequest(false), warmedRequest(true)])
        .then(([plugins, themes]) => {
          const merged = new Map<string, PluginCard>();
          ([] as PluginCard[]).concat(plugins ?? [], themes ?? []).forEach((plugin) => {
            if (plugin && plugin.name) {
              merged.set(plugin.name, plugin);
            }
          });
          this.allPlugins = [...merged.values()];
          this.loading = false;
          this.openDeepLinkedPlugin();
        })
        .catch(() => {
          if (abortController.signal.aborted) {
            return;
          }
          this.loading = false;
          this.loadFailed = true;
        })
        .finally(() => {
          if (this.fetchAbortController === abortController) {
            this.fetchAbortController = null;
          }
        });
    },

    refresh() {
      this.fetchCatalogue().then(() => this.$emit('triggerUpdate'));
    },

    readStateFromHash() {
      const hash = MatomoUrl.hashParsed.value;

      this.searchQuery = (hash.query || '') as string;
      this.pluginSort = (hash.sort || this.defaultSort || SORT_LAST_UPDATED) as string;

      const category = (hash.category || '') as string;
      if (category) {
        this.activeTab = category;
      } else {
        this.activeTab = tabFromLegacyPluginType((hash.pluginType || '') as string) ?? TAB_ALL;
      }

      this.pageSize = PAGE_SIZE;
    },

    updateHash(changes: Record<string, unknown>) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, ...changes });
    },

    pushQueryToHash(query: string) {
      this.updateHash({ query });
    },

    updateQuery(query: string) {
      this.searchQuery = query;
      this.pageSize = PAGE_SIZE;
      this.pushQueryToHash(query);
    },

    /**
     * Sets the tab before writing it to the hash, the way updateQuery does. The hash round trip is
     * what normally feeds `activeTab` back, but `hashchange` only fires once the current task ends,
     * so anything reading the rendered tabs on the next tick - `seeAllInSection` below - would
     * otherwise still find the outgoing tab marked active and move focus onto it.
     */
    updateTab(tabId: string) {
      this.activeTab = tabId;
      this.updateHash({ category: tabId, pluginType: null });
    },

    updateSort(sort: string) {
      this.updateHash({ sort });
    },

    /**
     * Opens one section's category, as its tab would. Unlike a tab click this can fire most of a
     * screen down the page, so it also scrolls the new list into view and moves focus - the
     * button that had it is removed by the re-render, which would drop focus to <body>.
     */
    seeAllInSection(sectionId: string) {
      this.updateTab(sectionId);

      this.scrollIntoView(this.$refs.resultsBar as HTMLElement|undefined);

      this.$nextTick(() => {
        const tabs = this.$refs.categoryTabs as { focusActiveTab?: () => void }|undefined;
        if (tabs && tabs.focusActiveTab) {
          tabs.focusActiveTab();
        }
      });
    },

    /** Scrolls without animating for readers who have asked for less motion. */
    scrollIntoView(element?: Element|null) {
      const reduceMotion = typeof window !== 'undefined'
        && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      if (element) {
        element.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
      }
    },

    resetFilters() {
      this.searchQuery = '';
      this.updateHash({ query: null, category: TAB_ALL, pluginType: null });
    },

    openDetailsModal(plugin: PluginCard) {
      this.showPluginDetailsForPlugin = plugin;
    },

    openDeepLinkedPlugin() {
      const { showPlugin } = MatomoUrl.hashParsed.value;
      if (!showPlugin || this.loading) {
        return;
      }

      const plugin = this.allPlugins.find((candidate) => candidate.name === showPlugin);
      if (!plugin) {
        return;
      }

      this.openDetailsModal(plugin);

      const isVisible = this.filteredPlugins.some((candidate) => candidate.name === showPlugin);
      if (!isVisible) {
        this.resetFilters();
      }

      this.scrollCardIntoView(showPlugin as string);
    },

    scrollCardIntoView(pluginName: string) {
      this.$nextTick(() => {
        const root = this.$refs.root as HTMLElement|undefined;

        const cards = [...(root?.querySelectorAll(`[data-plugin="${CSS.escape(pluginName)}"]`) ?? [])];
        const card = cards.find((element) => (element as HTMLElement).offsetParent !== null)
          ?? cards[0];

        this.scrollIntoView(card);
      });
    },

    observeSentinel() {
      const sentinel = this.$refs.sentinel as HTMLElement|undefined;
      if (!sentinel || typeof IntersectionObserver === 'undefined') {
        this.paginated = false;
        return;
      }

      this.observer = markRaw(new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) {
          return;
        }
        if (this.loading || this.showSections || this.pageSize >= this.filteredPlugins.length) {
          return;
        }
        this.pageSize += PAGE_SIZE;
      }, { rootMargin: '200px' }));

      this.observer.observe(sentinel);
    },
  },
});
</script>
