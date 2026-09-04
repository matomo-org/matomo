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
      :tabs="tabs"
      :model-value="activeTab"
      @update:model-value="updateTab($event)"
    />

    <div class="marketplacePage__resultsBar">
      <div class="marketplacePage__resultsCount" aria-live="polite">
        <h2 v-if="resultsHeading">{{ resultsHeading }}</h2>
      </div>
      <SortMenu
        v-if="filteredPlugins.length > 0"
        :model-value="pluginSort"
        @update:model-value="updateSort($event)"
      />
    </div>

    <PluginGrid
      v-if="loading || filteredPlugins.length > 0"
      :plugins="pagedPlugins"
      :skeleton-count="skeletonCount"
      :is-super-user="isSuperUser"
      :is-plugins-admin-enabled="isPluginsAdminEnabled"
      :is-multi-server-environment="isMultiServerEnvironment"
      :is-valid-consumer="isValidConsumer"
      :is-auto-update-possible="isAutoUpdatePossible"
      :activate-nonce="activateNonce"
      :deactivate-nonce="deactivateNonce"
      :install-nonce="installNonce"
      :update-nonce="updateNonce"
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
import EmptyState from '../PluginGrid/EmptyState.vue';
import RequestTrial from '../RequestTrial/RequestTrial.vue';
import StartFreeTrial from '../StartFreeTrial/StartFreeTrial.vue';
import PluginDetailsModal from '../PluginDetailsModal/PluginDetailsModal.vue';
import { PluginCard } from '../types';
import {
  buildTabs,
  filterPlugins,
  PluginTab,
  SORT_LAST_UPDATED,
  sortPlugins,
  TAB_ALL,
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
    EmptyState,
    RequestTrial,
    StartFreeTrial,
    PluginDetailsModal,
  },
  emits: ['triggerUpdate', 'startTrialStart', 'startTrialStop'],
  data(): MarketplaceState {
    return {
      // the catalogue request starts in mounted(), which runs after the first render
      loading: true,
      loadFailed: false,
      allPlugins: [],
      pluginSort: this.defaultSort || SORT_LAST_UPDATED,
      activeTab: TAB_ALL,
      searchQuery: '',
      pageSize: PAGE_SIZE,
      showRequestTrialForPlugin: null,
      showStartFreeTrialForPlugin: null,
      showPluginDetailsForPlugin: null,
      observer: null,
      fetchAbortController: null,
    };
  },
  created() {
    // a keystroke is a re-render now, not a request, but re-sorting the whole catalogue on every
    // one of them is still enough work to feel laggy while typing
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
    this.observer?.disconnect();
    this.fetchAbortController?.abort();
  },
  computed: {
    tabs(): PluginTab[] {
      return buildTabs(this.allPlugins);
    },
    filteredPlugins(): PluginCard[] {
      return sortPlugins(
        filterPlugins(this.allPlugins, this.activeTab, this.searchQuery),
        this.pluginSort,
      );
    },
    pagedPlugins(): PluginCard[] {
      return this.filteredPlugins.slice(0, this.pageSize);
    },
    skeletonCount(): number {
      return this.loading ? INITIAL_SKELETONS : 0;
    },
    resultsHeading(): string {
      if (this.loading) {
        return '';
      }
      if (this.searchQuery.trim()) {
        return translate(
          'Marketplace_ResultsFoundFor',
          this.filteredPlugins.length,
          this.searchQuery,
        );
      }
      return '';
    },
  },
  methods: {
    translate,

    /**
     * The whole catalogue, in the two requests the Marketplace keeps warm.
     *
     * `Api\Client::getWarmedOverviewLists()` holds exactly ['plugins', ALL], ['plugins', PAID] and
     * ['themes', ALL] for 90 minutes, refilled hourly by `Tasks::warmCacheEntries()`. Anything
     * that varies query, sort or purchase type is a different cache key and misses all three, so
     * this page asks for the unfiltered plugin and theme lists and does the rest on the client.
     * ['plugins', PAID] is a subset of ['plugins', ALL], reachable here through `isPaid`.
     *
     * Do not add a parameter to these two requests. Nothing would break loudly: every tab click
     * would just become a cold catalogue download.
     */
    fetchCatalogue() {
      this.loading = true;
      this.loadFailed = false;
      this.fetchAbortController?.abort();

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
          // a terminal state, so the skeletons stop rather than spinning forever
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
        // CorePluginsAdmin still links in with #?pluginType=themes from ThemesIntro.vue and
        // PluginsTable.vue. Nothing writes it any more, but reading it has to keep working.
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
      // render against the new term straight away; the hash catches up on the debounce
      this.searchQuery = query;
      this.pageSize = PAGE_SIZE;
      this.pushQueryToHash(query);
    },

    updateTab(tabId: string) {
      this.updateHash({ category: tabId, pluginType: null });
    },

    updateSort(sort: string) {
      this.updateHash({ sort });
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

      // the plugin exists but the current tab or search may be hiding it, so clear both rather
      // than scrolling to a card that is not rendered
      const isVisible = this.filteredPlugins.some((candidate) => candidate.name === showPlugin);
      if (!isVisible) {
        this.resetFilters();
      }

      this.scrollCardIntoView(showPlugin as string);
    },

    scrollCardIntoView(pluginName: string) {
      this.$nextTick(() => {
        const root = this.$refs.root as HTMLElement|undefined;
        const card = root?.querySelector(`[data-plugin="${CSS.escape(pluginName)}"]`);
        card?.scrollIntoView({ block: 'start', behavior: 'smooth' });
      });
    },

    observeSentinel() {
      const sentinel = this.$refs.sentinel as HTMLElement|undefined;
      if (!sentinel || typeof IntersectionObserver === 'undefined') {
        return;
      }

      this.observer = markRaw(new IntersectionObserver((entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) {
          return;
        }
        if (this.loading || this.pageSize >= this.filteredPlugins.length) {
          return;
        }
        // appending must never move focus - infinite scroll plus a screen reader is the usual break
        this.pageSize += PAGE_SIZE;
      }, { rootMargin: '200px' }));

      this.observer.observe(sentinel);
    },
  },
});
</script>
