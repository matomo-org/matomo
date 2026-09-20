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
      :plugin-count="allPlugins.length"
      @update:model-value="updateQuery($event)"
    />

    <!--
      No `v-if` on the count: the button hides itself when there is nothing purchased to install,
      and this wrapper carries no chrome of its own, so an empty one is invisible. The spacing sits
      on the button rather than here for the same reason - see InstallAllPaidPluginsButton.
    -->
    <div class="marketplacePage__installAction" v-if="installAllPaidPluginsVisible">
      <InstallAllPaidPluginsButton :disabled="installDisabled" />
    </div>

    <!--
      Hidden while searching rather than reset: a search covers the whole catalogue, so a tab left
      on screen would either sit highlighted over results it is not filtering, or have to be moved
      to All on the reader's behalf. Out of the way, the open tab survives the search and is still
      there, unchanged, once the query is cleared.
    -->
    <CategoryTabs
      v-if="tabs.length > 1 && !searchQuery.trim()"
      ref="categoryTabs"
      :tabs="tabs"
      :model-value="activeTab"
      @update:model-value="updateTab($event)"
    />

    <!--
      The way out of a promotion's list. A promotion has no tab, so nothing in the bar above is
      highlighted while one is open and the reader would otherwise have no marked way back.
    -->
    <button
      type="button"
      class="marketplacePage__backLink"
      ref="backLink"
      v-if="activePromotion && !searchQuery.trim()"
      @click="closePromotion()"
    >
      <span class="icon-chevron-left marketplacePage__backIcon" aria-hidden="true" />
      <span>{{ translate('Marketplace_BackToMarketplace') }}</span>
    </button>

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
      />
    </div>

    <PluginGrid
      v-if="!showSections && (loading || filteredPlugins.length > 0)"
      :plugins="pagedPlugins"
      :skeleton-count="skeletonCount"
      :context="cardContext"
      @openDetails="openDetailsModal($event)"
      @requestTrial="showRequestTrialForPlugin = $event"
    />

    <EmptyState
      v-if="!loading && !loadFailed && filteredPlugins.length === 0"
      :has-query="!!searchQuery.trim()"
      :can-reset="hasActiveFilters"
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
  Matomo,
  MatomoUrl,
  translate,
} from 'CoreHome';
import { InstallAllPaidPluginsButton } from 'CorePluginsAdmin';
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
  buildPromoSections,
  buildSections,
  buildTabs,
  filterPlugins,
  isPromoSection,
  PluginSection as PluginSectionType,
  PluginTab,
  promotedPlugins,
  SORT_LAST_UPDATED,
  sortPlugins,
  TAB_ALL,
  TYPE_TABS,
  tabFromLegacyPluginType,
} from '../PluginGrid/pluginGrouping';

/**
 * The hash parameter holding the open tab.
 *
 * Namespaced, and deliberately not `category`: the Marketplace is in the reporting menu as well as
 * the admin one, and on a reporting page `category` is CoreHome's own menu category. Writing that
 * one navigates the whole page away and unmounts this component; reading it hands back the
 * reporting category id, which matches no tab.
 */
const CATEGORY_PARAM = 'pluginCategory';

/**
 * The hash parameter holding the open promotion, if any. Its own parameter rather than a value of
 * {@link CATEGORY_PARAM}: a promotion is not a tab, and writing it there would leave the tab bar
 * looking for a tab that does not exist and highlighting none of them.
 */
const PROMOTION_PARAM = 'pluginPromotion';

/** How many cards a filtered view adds at a time. */
const PAGE_SIZE = 15;

/** Placeholder cards to hold the layout on a cold load. */
const INITIAL_SKELETONS = 10;

/** How long the search box waits after the last keystroke before writing the query to the hash. */
const QUERY_DEBOUNCE_MS = 250;

/**
 * How long a catalogue request may hang before the page calls it a failure.
 *
 * `AjaxHelper.send()` neither resolves nor rejects when the request never reaches the server
 * (`xhr.status === 0`), so without this the skeletons would sit there for good.
 */
const FETCH_TIMEOUT_MS = 30000;

export interface MarketplaceState {
  loading: boolean;
  loadFailed: boolean;
  allPlugins: PluginCard[];
  pluginSort: string;
  activeTab: string;
  activePromotion: string;
  searchQuery: string;
  pageSize: number;
  paginated: boolean;
  showRequestTrialForPlugin: PluginCard|null;
  showStartFreeTrialForPlugin: PluginCard|null;
  showPluginDetailsForPlugin: PluginCard|null;
  observer: IntersectionObserver|null;
  fetchAbortController: AbortController|null;
  fetchTimeout: ReturnType<typeof setTimeout>|null;
  queryHashTimeout: ReturnType<typeof setTimeout>|null;
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
    installAllPaidPluginsVisible: Boolean,
    installDisabled: Boolean,
  },
  components: {
    InstallAllPaidPluginsButton,
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
      activePromotion: '',
      searchQuery: '',
      pageSize: PAGE_SIZE,
      paginated: true,
      showRequestTrialForPlugin: null,
      showStartFreeTrialForPlugin: null,
      showPluginDetailsForPlugin: null,
      observer: null,
      fetchAbortController: null,
      fetchTimeout: null,
      queryHashTimeout: null,
    };
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

    if (this.fetchTimeout) {
      clearTimeout(this.fetchTimeout);
      this.fetchTimeout = null;
    }

    this.cancelQueryHashWrite();
  },
  computed: {
    tabs(): PluginTab[] {
      return buildTabs(this.allPlugins, tabLabel);
    },
    /**
     * The section stack, each row sorted the way the whole catalogue is, so that a row is the
     * first cards of the category it links to rather than a differently ordered sample.
     */
    sections(): PluginSectionType[] {
      // The promoted rows keep the order the Marketplace gave them - promoting a plugin is
      // pointless if the page's sort can move it to the end of the row - so only the stack below
      // them is re-sorted. Sorting is hidden on this view anyway; see showSort().
      return [
        ...buildPromoSections(this.allPlugins),
        ...buildSections(this.allPlugins, tabLabel).map((section) => ({
          ...section,
          plugins: sortPlugins(section.plugins, this.pluginSort),
        })),
      ];
    },
    /**
     * Whether the page shows the section stack rather than one flat grid. Only on All plugins with
     * nothing searched for: a tab or a query asks for one list, and ten rows would bury it.
     *
     * Keyed off the catalogue rather than off `loading`: the first load has no plugins and so no
     * sections, and the flat grid holds the layout with its skeletons; but refresh() loads again
     * over a catalogue that is still there, and reading `loading` would collapse the stack to a
     * flat grid for the length of that request and then build it back.
     */
    showSections(): boolean {
      return this.activeTab === TAB_ALL
        && !this.activePromotion
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
    /**
     * Whether resetFilters() has anything left to clear. An empty catalogue reaches the empty
     * state with nothing filtered, and a reset button there would do nothing when pressed.
     */
    hasActiveFilters(): boolean {
      return !!this.searchQuery.trim() || this.activeTab !== TAB_ALL;
    },
    filteredPlugins(): PluginCard[] {
      // A search spans the whole catalogue: the tab is dropped rather than intersected, so a
      // query typed while a category is open still finds everything. The tab itself is left set -
      // its row is hidden for the duration - so clearing the query returns to it. An open
      // promotion is set aside the same way, and comes back with the query cleared.
      if (this.activePromotion && !this.searchQuery.trim()) {
        // Sorted like any other list on this view, promotion order and all: the sort control is
        // on screen here, and a list that ignored it would look broken. The row on the overview
        // is the one that keeps the Marketplace's order - see buildPromoSections().
        return sortPlugins(
          promotedPlugins(this.allPlugins, this.activePromotion),
          this.pluginSort,
        );
      }

      const tab = this.searchQuery.trim() ? TAB_ALL : this.activeTab;

      return sortPlugins(
        filterPlugins(this.allPlugins, tab, this.searchQuery),
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
    /**
     * Skeletons stand in for a catalogue that is not there yet. A refresh() over one already on
     * screen shows the cards it has instead - see showSections().
     */
    skeletonCount(): number {
      return this.loading && this.allPlugins.length === 0 ? INITIAL_SKELETONS : 0;
    },
    /**
     * What the list below is: a search's result count, or the name of the open category. All
     * plugins names nothing, since its rows carry their own headings. A category names itself
     * while still loading - the label comes from the tab, so waiting would shift the skeletons.
     */
    resultsHeading(): string {
      if (this.searchQuery.trim()) {
        if (this.loading) {
          return '';
        }

        const found = this.filteredPlugins.length;

        return found === 1
          ? translate('Marketplace_OneResultFoundFor', this.searchQuery)
          : translate('Marketplace_ResultsFoundFor', found, this.searchQuery);
      }
      if (this.activePromotion) {
        return tabLabel({ id: this.activePromotion, isCategory: false });
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

      // A request that never reaches the server settles neither way in AjaxHelper, so the pair is
      // raced against a timer rather than awaited on its own - see FETCH_TIMEOUT_MS.
      let timedOut = false;
      const givesUp = new Promise<never>((resolve, reject) => {
        this.fetchTimeout = setTimeout(() => {
          timedOut = true;
          abortController.abort();
          reject(new Error('The Marketplace catalogue request timed out.'));
        }, FETCH_TIMEOUT_MS);
      });
      const timeoutHandle = this.fetchTimeout;

      return Promise.race([
        Promise.all([warmedRequest(false), warmedRequest(true)]),
        givesUp,
      ])
        .then(([plugins, themes]) => {
          // a fetch this component has already replaced must not write over the newer one's state
          if (this.fetchAbortController !== abortController) {
            return;
          }

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
          // a fetch this component itself replaced or abandoned leaves the page as it is - an
          // aborted request never settles in AjaxHelper, so a superseded fetch reaches here only
          // once its own timer fires, long after the fetch that replaced it has painted the page
          if (this.fetchAbortController !== abortController) {
            return;
          }

          // the timeout aborts too, and is the one abort that does mean the catalogue is gone
          if (abortController.signal.aborted && !timedOut) {
            return;
          }
          this.loading = false;
          this.loadFailed = true;
        })
        .finally(() => {
          if (timeoutHandle) {
            clearTimeout(timeoutHandle);
          }

          if (this.fetchTimeout === timeoutHandle) {
            this.fetchTimeout = null;
          }

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

      const searchQuery = (hash.query || '') as string;
      const pluginSort = (hash.sort || this.defaultSort || SORT_LAST_UPDATED) as string;
      const category = (hash[CATEGORY_PARAM] || '') as string;
      const activeTab = category
        || tabFromLegacyPluginType((hash.pluginType || '') as string)
        || TAB_ALL;
      const promotion = (hash[PROMOTION_PARAM] || '') as string;
      const activePromotion = isPromoSection(promotion) ? promotion : '';

      // Only a change to what is listed starts the list over. This runs on every hash write, and
      // some of them leave the list alone - closing the details modal clears `showPlugin` - so
      // resetting unconditionally would throw away however far the reader had scrolled.
      const listChanged = searchQuery !== this.searchQuery
        || pluginSort !== this.pluginSort
        || activeTab !== this.activeTab
        || activePromotion !== this.activePromotion;

      this.searchQuery = searchQuery;
      this.pluginSort = pluginSort;
      this.activeTab = activeTab;
      this.activePromotion = activePromotion;

      if (listChanged) {
        this.pageSize = PAGE_SIZE;
      }
    },

    updateHash(changes: Record<string, unknown>) {
      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, ...changes });
    },

    /**
     * Writes the query to the hash once the typing stops. Its own timer rather than CoreHome's
     * `debounce`, which hands back no way to call a pending write off - and resetFilters() has to,
     * or a write scheduled by the last keystroke lands after the reset and puts the query back.
     */
    pushQueryToHash(query: string) {
      this.cancelQueryHashWrite();
      this.queryHashTimeout = setTimeout(() => {
        this.queryHashTimeout = null;
        this.updateHash({ query });
      }, QUERY_DEBOUNCE_MS);
    },

    cancelQueryHashWrite() {
      if (this.queryHashTimeout) {
        clearTimeout(this.queryHashTimeout);
        this.queryHashTimeout = null;
      }
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
      // a tab and a promotion are two views of their own, so opening one closes the other
      this.activePromotion = '';
      // set here as well as in readStateFromHash(), which only resets what it sees change and is
      // handed a tab this has already applied
      this.pageSize = PAGE_SIZE;
      // Home is the default the hash is read back as, so it is dropped rather than written out
      this.updateHash({
        [CATEGORY_PARAM]: tabId === TAB_ALL ? null : tabId,
        [PROMOTION_PARAM]: null,
        pluginType: null,
      });
    },

    /**
     * Sets the sort before writing it to the hash, for the reason given on updateTab(), and lets a
     * query the search box has typed but not yet written go first: updateHash() merges into the
     * hash as it stands, so writing during the debounce would put the outgoing query back and the
     * search box - bound to `searchQuery` - would revert until the pending write landed.
     */
    updateSort(sort: string) {
      this.cancelQueryHashWrite();
      this.pluginSort = sort;
      this.pageSize = PAGE_SIZE;
      this.updateHash({ sort, query: this.searchQuery || null });
    },

    /**
     * Opens one section's category, as its tab would. Unlike a tab click this can fire most of a
     * screen down the page, so it also scrolls the new list into view and moves focus - the
     * button that had it is removed by the re-render, which would drop focus to <body>.
     */
    seeAllInSection(sectionId: string) {
      if (isPromoSection(sectionId)) {
        this.openPromotion(sectionId);
        return;
      }

      this.updateTab(sectionId);

      this.scrollIntoView(this.$refs.resultsBar as HTMLElement|undefined);

      this.$nextTick(() => {
        const tabs = this.$refs.categoryTabs as { focusActiveTab?: () => void }|undefined;
        if (tabs && tabs.focusActiveTab) {
          tabs.focusActiveTab();
        }
      });
    },

    /**
     * Opens a promotion's own list, the way seeAllInSection() opens a category's tab. Written to
     * the hash so the view survives a reload and the browser's Back leaves it, and focus moves to
     * the way out: the button that had it is removed by the re-render, and no tab is highlighted
     * for this view, so focus would otherwise drop to <body>.
     */
    openPromotion(sectionId: string) {
      this.activePromotion = sectionId;
      this.activeTab = TAB_ALL;
      this.pageSize = PAGE_SIZE;
      this.updateHash({
        [PROMOTION_PARAM]: sectionId,
        [CATEGORY_PARAM]: null,
        pluginType: null,
      });

      this.scrollIntoView(this.$refs.resultsBar as HTMLElement|undefined);

      this.$nextTick(() => (this.$refs.backLink as HTMLElement|undefined)?.focus());
    },

    /** Leaves a promotion's list for the overview it was opened from. */
    closePromotion() {
      this.activePromotion = '';
      this.pageSize = PAGE_SIZE;
      this.updateHash({ [PROMOTION_PARAM]: null });
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

    /** Sets the state before writing the hash, for the reason given on updateTab(). */
    resetFilters() {
      this.cancelQueryHashWrite();
      this.searchQuery = '';
      this.activeTab = TAB_ALL;
      this.activePromotion = '';
      this.pageSize = PAGE_SIZE;
      this.updateHash({
        query: null,
        [CATEGORY_PARAM]: null,
        [PROMOTION_PARAM]: null,
        pluginType: null,
      });
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
