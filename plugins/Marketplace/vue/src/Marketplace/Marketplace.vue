<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div
    class="marketplacePage"
    :class="{ 'marketplacePage--details': !!viewPluginName }"
    ref="root"
  >
    <RequestTrial
      v-model="showRequestTrialForPlugin"
      @trialRequested="refresh()"
    />

    <!--
      Both views share one grid cell so that neither is laid out below the other while they are
      changing over. Only ever one of them is on screen - see switchView(), which fades the outgoing
      view out, swaps them and moves the scroll position while nothing is showing, then fades the
      incoming one in. The two sit at different scroll offsets, so cross-fading them showed the
      wrong part of each. The trial dialogs stay outside it - Materialize renders them where they
      sit, and they are not views of this page.
    -->
    <div
      class="marketplacePage__views"
      :class="{ 'marketplacePage__views--switching': switching }"
      ref="views"
    >
      <div class="marketplacePage__detailsView" v-if="viewPluginName">
        <PluginDetails
          :plugin-card="detailsCard"
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
          @back="closeDetails()"
          @requestTrial="showRequestTrialForPlugin = $event"
        />
      </div>

      <!--
      v-show rather than v-if: the details page is a view of this one, and the AC asks for the
      return trip to cost nothing. Hidden, the grid keeps its DOM, its pagination and its scroll
      position, so coming back is a repaint rather than a re-render and a refetch. The sentinel
      goes dark with it, so nothing pages in behind the reader's back.

    -->
      <div class="marketplacePage__catalogue" v-show="!viewPluginName">
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

      An open promotion is held back for the same reason: it is not one of the tabs, so any tab
      left highlighted would claim results it is not filtering. The back link below replaces it.
    -->
        <CategoryTabs
          v-if="tabs.length > 1 && !searchQuery.trim() && !activePromotion"
          ref="categoryTabs"
          :tabs="tabs"
          :model-value="activeTab"
          @update:model-value="updateTab($event)"
        />

        <!--
      The way out of a promotion's list. A promotion has no tab of its own, so the tab bar above is
      hidden while one is open and the reader would otherwise have no marked way back.
    -->
        <button
          type="button"
          class="marketplacePage__backLink"
          ref="backLink"
          v-if="showBackLink"
          @click="closePromotion()"
        >
          <span class="icon-chevron-left marketplacePage__backIcon" aria-hidden="true" />
          <span>{{ translate('Marketplace_BackToMarketplace') }}</span>
        </button>

        <!--
      The bar and the list below it, faded in together when the open tab or promotion changes - see
      fadeInResults(). Animated in place rather than keyed and remounted: the bar's heading is an
      aria-live region, and one mounted anew is not announced.
    -->
        <div class="marketplacePage__results" ref="results">
          <div
            class="marketplacePage__resultsBar"
            :class="{
              'marketplacePage__resultsBar--empty': !resultsHeading && !showSort,
              'marketplacePage__resultsBar--underBackLink': showBackLink,
            }"
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
              @openDetails="openDetails($event)"
              @requestTrial="showRequestTrialForPlugin = $event"
            />
          </div>

          <PluginGrid
            v-if="!showSections && (loading || filteredPlugins.length > 0)"
            :plugins="pagedPlugins"
            :skeleton-count="skeletonCount"
            :context="cardContext"
            @openDetails="openDetails($event)"
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
        </div>

        <div class="marketplacePage__sentinel" ref="sentinel" />
      </div>
    </div>
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
import PluginDetails from '../PluginDetails/PluginDetails.vue';
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
  sortTabPlugins,
  TAB_ALL,
  TAB_BUNDLES,
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

/**
 * How long each half of the change between the catalogue and a plugin's page takes.
 *
 * Kept in step with the transition on `.marketplacePage__views` in Marketplace.less: the fade is
 * CSS, but the swap in between has to wait for it, and nothing tells JavaScript when a class-driven
 * transition on a subtree has finished.
 */
const VIEW_FADE_MS = 220;

/** How long the fade between two categories' lists takes - see fadeInResults(). */
const LIST_FADE_MS = 280;

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
  previousScrollRestoration: ScrollRestoration|null;
  viewPluginName: string;
  switching: boolean;
  viewSwitchTimeout: ReturnType<typeof setTimeout>|null;
  detailsEntryPushed: boolean;
  returnToPlugin: string;
  returnScrollTop: number;
  hasReturnScroll: boolean;
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
    PluginDetails,
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
      previousScrollRestoration: null,
      viewPluginName: '',
      switching: false,
      viewSwitchTimeout: null,
      detailsEntryPushed: false,
      returnToPlugin: '',
      returnScrollTop: 0,
      hasReturnScroll: false,
      observer: null,
      fetchAbortController: null,
      fetchTimeout: null,
      queryHashTimeout: null,
    };
  },
  created() {
    // a page loaded on a plugin's URL starts there rather than fading into it from a catalogue
    // the reader never saw - set before the first render, or that render is the catalogue
    this.viewPluginName = this.selectedPluginName;
  },
  mounted() {
    Matomo.postEvent('Marketplace.Marketplace.mounted', { element: this.$refs.root });

    this.readStateFromHash();
    watch(() => MatomoUrl.hashParsed.value, () => this.readStateFromHash());

    this.takeOverScrollRestoration();

    this.fetchCatalogue();
    this.observeSentinel();
  },
  watch: {
    // what is on screen follows the name, but a step behind it - see switchView()
    selectedPluginName(name: string) {
      this.switchView(name);
    },
    // a change of category patches the list in place, then fades it in - see fadeInResults()
    listKey() {
      this.$nextTick(() => this.fadeInResults());
    },
    // where to come back to follows the row, which a cold deep link only gets once it lands
    selectedPlugin(plugin: PluginCard|null) {
      if (plugin) {
        this.prepareReturnToCard(plugin);
      }
    },
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
    this.cancelViewSwitch();
    this.releaseScrollRestoration();
  },
  computed: {
    /**
     * The plugin the hash asks to show.
     *
     * The hash is the one source of truth for this and not a convenience over local state: the
     * plugin management screen opens a plugin here by writing `showPlugin` itself - see
     * CorePluginsAdmin's PluginName directive - so the parameter is set by callers this page does
     * not control. Keeping a second copy in `data` only creates two things that can disagree.
     *
     * Deliberately not resolved against the catalogue: what is shown must not wait on a listing
     * the details view does not need. A cold deep link would otherwise paint the grid, skeletons
     * and all, and replace it with the plugin a moment later.
     */
    selectedPluginName(): string {
      return (MatomoUrl.hashParsed.value.showPlugin || '') as string;
    },
    /**
     * The catalogue's row for that plugin, once there is one. Only the return trip needs it - see
     * prepareReturnToCard() - since the details request carries everything the view renders.
     */
    selectedPlugin(): PluginCard|null {
      if (!this.selectedPluginName) {
        return null;
      }

      return this.allPlugins.find(
        (candidate) => candidate.name === this.selectedPluginName,
      ) ?? null;
    },
    /**
     * What the details view starts from. The card row where the catalogue has already been
     * fetched, so the name and description paint immediately; otherwise the name alone, which is
     * all its own request needs. Everything else arrives with that response.
     *
     * Keyed off the name being rendered rather than the one in the hash, so a page on its way out
     * keeps showing the plugin it was opened for until it has gone.
     */
    detailsCard(): PluginCard {
      const card = this.allPlugins.find(
        (candidate) => candidate.name === this.viewPluginName,
      );

      return card ?? ({ name: this.viewPluginName } as PluginCard);
    },
    tabs(): PluginTab[] {
      return buildTabs(this.allPlugins, tabLabel);
    },
    /**
     * The section stack, each row sorted the way the whole catalogue is, so that a row is the
     * first cards of the category it links to rather than a differently ordered sample. Bundles
     * are the exception on both counts - see sortTabPlugins().
     */
    sections(): PluginSectionType[] {
      // The promoted rows keep the order the Marketplace gave them - promoting a plugin is
      // pointless if the page's sort can move it to the end of the row - so only the stack below
      // them is re-sorted. Sorting is hidden on this view anyway; see showSort().
      return [
        ...buildPromoSections(this.allPlugins),
        ...buildSections(this.allPlugins, tabLabel).map((section) => ({
          ...section,
          plugins: sortTabPlugins(section.plugins, this.pluginSort, section.id),
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
    /**
     * Whether the way out of a promotion's list is on screen. A search sets the promotion aside
     * rather than closing it - see filteredPlugins() - so there is nothing to go back from while
     * a query is typed.
     */
    showBackLink(): boolean {
      return !!this.activePromotion && !this.searchQuery.trim();
    },
    /** Which list is open, for the fade between categories - see fadeInResults(). */
    listKey(): string {
      return `${this.activeTab}|${this.activePromotion}`;
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

      return sortTabPlugins(
        filterPlugins(this.allPlugins, tab, this.searchQuery),
        this.pluginSort,
        tab,
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
     *
     * Bundles are left out for the same reason: they carry a seat order of their own - see
     * sortTabPlugins() - and a control that reordered nothing would read as a broken one. Only
     * while that tab is the list on screen, though: a search or a promotion sets the tab aside,
     * and what those show does sort.
     */
    showSort(): boolean {
      const showingBundles = this.activeTab === TAB_BUNDLES
        && !this.activePromotion
        && !this.searchQuery.trim();

      return !this.showSections && !showingBundles && this.filteredPlugins.length > 0;
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
      // some of them leave the list alone - opening and closing a plugin only moves `showPlugin` -
      // so resetting unconditionally would throw away however far the reader had scrolled.
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
     * Writes the hash without leaving a history entry behind.
     *
     * MatomoUrl.updateHash() assigns window.location.hash, which pushes one. That is what we want
     * when opening a plugin - browser Back then closes it - but not when closing one the reader
     * never navigated to, which would otherwise need two Backs to escape. replaceState changes the
     * URL without notifying anyone, so the hashchange MatomoUrl listens for is raised by hand, the
     * way MatomoUrl itself does when the hash it is asked for is the one already set.
     */
    replaceHash(changes: Record<string, unknown>) {
      const oldURL = window.location.href;
      const params = { ...MatomoUrl.hashParsed.value, ...changes };

      window.history.replaceState(null, '', `#?${MatomoUrl.stringify(params)}`);
      window.dispatchEvent(new HashChangeEvent('hashchange', {
        newURL: window.location.href,
        oldURL,
      }));
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
      if (element) {
        element.scrollIntoView({
          block: 'start',
          behavior: this.prefersReducedMotion() ? 'auto' : 'smooth',
        });
      }
    },

    /**
     * Sets the state before writing the hash, for the reason given on updateTab().
     *
     * `replace` is for the reset a deep link forces on the catalogue behind the details page: the
     * reader is not on the catalogue to see it happen, and an entry for it would sit between the
     * details page and wherever they actually came from.
     */
    resetFilters(replace = false) {
      this.cancelQueryHashWrite();
      this.searchQuery = '';
      this.activeTab = TAB_ALL;
      this.activePromotion = '';
      this.pageSize = PAGE_SIZE;

      const cleared = {
        query: null,
        [CATEGORY_PARAM]: null,
        [PROMOTION_PARAM]: null,
        pluginType: null,
      };

      if (replace) {
        this.replaceHash(cleared);
        return;
      }

      this.updateHash(cleared);
    },

    /**
     * Stops the browser putting the scroll position back by itself.
     *
     * Leaving a plugin's page goes back through the history entry that opened it, and on a history
     * navigation the browser restores the offset it recorded for that entry - immediately, and
     * before the change between the two views has even begun. That is the jump this page spends
     * switchView() hiding, so it has to own the scroll position outright rather than share it.
     */
    takeOverScrollRestoration() {
      if (typeof window === 'undefined' || !('scrollRestoration' in window.history)) {
        return;
      }

      this.previousScrollRestoration = window.history.scrollRestoration;
      window.history.scrollRestoration = 'manual';
    },

    releaseScrollRestoration() {
      if (!this.previousScrollRestoration) {
        return;
      }

      window.history.scrollRestoration = this.previousScrollRestoration;
      this.previousScrollRestoration = null;
    },

    /**
     * Changes the view over: fade what is on screen out, swap the two and move the scroll position
     * while nothing is showing, then fade the new one in.
     *
     * Sequenced rather than cross-faded because the two views sit at different scroll offsets - a
     * plugin's page opens at the top, the catalogue comes back where the reader left it - so
     * dissolving one into the other showed the top of the grid against a page most of a screen
     * further down. Behind the blank, the jump is not there to be seen.
     */
    switchView(name: string) {
      this.cancelViewSwitch();

      if (this.prefersReducedMotion()) {
        this.applyView(name);
        return;
      }

      this.switching = true;
      this.viewSwitchTimeout = setTimeout(() => {
        this.viewSwitchTimeout = null;
        this.applyView(name);
        // the new view is mounted but still transparent; showing it in the same frame would
        // start its fade from wherever the outgoing one's had got to
        this.$nextTick(() => {
          this.switching = false;
        });
      }, VIEW_FADE_MS);
    },

    /** Renders the named plugin's page, or the catalogue, and puts the scroll position with it. */
    applyView(name: string) {
      this.viewPluginName = name;

      this.$nextTick(() => {
        if (name) {
          this.scrollDetailsIntoView();
          return;
        }

        this.restoreCataloguePosition();
      });
    },

    /**
     * Brings the top of a plugin's page into view, if it is not there already.
     *
     * Not a jump to the top every time: opening a card from near the top of the catalogue would
     * then move the page for no reason the reader can see. Hiding the catalogue collapses the
     * document, so the browser has usually clamped the offset down to something sensible by the
     * time this runs, and there is nothing left to do.
     */
    scrollDetailsIntoView() {
      const views = this.$refs.views as HTMLElement|undefined;

      if (!views) {
        return;
      }

      const top = views.getBoundingClientRect().top + window.scrollY;

      if (window.scrollY <= top) {
        return;
      }

      // to the top of the document rather than to the page's own top: any other offset can stop
      // being reachable when the skeleton is replaced by content shorter than it, and the browser
      // pulling the reader back up at that point is a jump long after the change is over
      window.scrollTo({ top: 0, behavior: 'auto' });
    },

    cancelViewSwitch() {
      if (this.viewSwitchTimeout) {
        clearTimeout(this.viewSwitchTimeout);
        this.viewSwitchTimeout = null;
      }
    },

    /**
     * Fades the results bar and the list in together, once the new category has been rendered.
     *
     * The bar is in it as well as the list: Home has no heading and no sort, so the bar collapses
     * there and opens up again on any other tab. Left out, it jumped the list by its own height at
     * the start of every change to or from Home, and only those. Search and sort leave the key
     * alone, so typing does not fade the list on every keystroke.
     *
     * Web Animations rather than a class, since restarting a CSS transition means taking the
     * class off and forcing a reflow. jsdom has no `animate`, hence the check.
     */
    fadeInResults() {
      const results = this.$refs.results as HTMLElement|undefined;

      // behind a plugin's page the change is not on screen, and switchView() fades the way back
      if (!results || typeof results.animate !== 'function'
        || this.viewPluginName || this.prefersReducedMotion()) {
        return;
      }

      results.animate(
        [
          { opacity: 0, transform: 'translateY(6px)' },
          { opacity: 1, transform: 'none' },
        ],
        { duration: LIST_FADE_MS, easing: 'ease-out' },
      );
    },

    prefersReducedMotion(): boolean {
      return typeof window !== 'undefined'
        && typeof window.matchMedia === 'function'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    },

    /**
     * Opens a plugin's page. Pushes a history entry, so browser Back closes it again;
     * closeDetails() reuses that entry rather than adding a second one.
     */
    openDetails(plugin: PluginCard) {
      // a query typed but not yet written would otherwise land after this one and push a second
      // entry, this time with showPlugin already in it - see updateSort() for the same hazard
      this.cancelQueryHashWrite();

      this.returnScrollTop = window.scrollY;
      this.hasReturnScroll = true;
      this.returnToPlugin = plugin.name;
      this.detailsEntryPushed = true;

      this.updateHash({ query: this.searchQuery || null, showPlugin: plugin.name });
    },

    /**
     * Leaves the details page. Goes back through the entry openDetails() pushed where there is one,
     * so the reader is not left with a forward entry pointing at the page they just dismissed. A
     * plugin opened by URL - a deep link, or the plugin management screen writing showPlugin - has
     * no such entry, and its history belongs to whoever sent the reader here, so that one is
     * replaced instead.
     */
    closeDetails() {
      if (this.detailsEntryPushed) {
        this.detailsEntryPushed = false;
        window.history.back();
        return;
      }

      this.replaceHash({ showPlugin: null });
    },

    /**
     * Makes sure the catalogue behind the details page holds the card the reader will come back to.
     * Only has anything to do for a plugin opened by URL: one opened from a card was on screen by
     * definition, and openDetails() has already noted where.
     */
    prepareReturnToCard(plugin: PluginCard) {
      if (this.returnToPlugin === plugin.name) {
        return;
      }

      this.returnToPlugin = plugin.name;
      this.hasReturnScroll = false;

      if (!this.filteredPlugins.some((candidate) => candidate.name === plugin.name)) {
        this.resetFilters(true);
      }

      // Being in the results is not enough to be on the page: the grid renders the first
      // `pageSize` of them and the rest wait on the sentinel, so a card ranked further down has no
      // element for restoreCataloguePosition() to find. Grow the page by whole pages until it
      // does. The section stack renders its own cards and pages nothing, so it is left alone.
      if (!this.showSections) {
        const position = this.filteredPlugins.findIndex((c) => c.name === plugin.name) + 1;
        if (position > this.pageSize) {
          this.pageSize = Math.ceil(position / PAGE_SIZE) * PAGE_SIZE;
        }
      }
    },

    /**
     * Puts the reader back where they left the catalogue: the scroll position they opened the
     * plugin from, and focus on its card, which the details page took focus away from. A plugin
     * opened by URL was never scrolled to, so its card is brought into view instead.
     */
    restoreCataloguePosition() {
      const card = this.findCard(this.returnToPlugin);

      if (this.hasReturnScroll) {
        // straight there, behind the blank, and the catalogue fades in already in place
        window.scrollTo({ top: this.returnScrollTop, behavior: 'auto' });
      } else {
        this.scrollIntoView(card);
      }

      const link = card?.querySelector('.pluginCard__titleLink') as HTMLElement|undefined;
      link?.focus({ preventScroll: true });

      this.returnToPlugin = '';
      this.hasReturnScroll = false;
    },

    /**
     * The visible card for a plugin. A section stack lists the same plugin in more than one row.
     */
    findCard(pluginName: string): Element|null {
      if (!pluginName) {
        return null;
      }

      const root = this.$refs.root as HTMLElement|undefined;
      const cards = [...(root?.querySelectorAll(`[data-plugin="${CSS.escape(pluginName)}"]`) ?? [])];

      return cards.find((element) => (element as HTMLElement).offsetParent !== null)
        ?? cards[0]
        ?? null;
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
        // the sentinel sits inside the hidden catalogue while a plugin page is open, so a browser
        // reports it as not intersecting; asserted rather than assumed, since paging in behind the
        // reader would move the cards out from under them on the way back
        if (this.viewPluginName) {
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
