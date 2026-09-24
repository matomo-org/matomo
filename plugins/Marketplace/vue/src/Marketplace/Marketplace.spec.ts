/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { enableAutoUnmount, mount, VueWrapper } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports. The hash is a
// real ref so the component's watch() on it fires the way it does in the browser.
vi.mock('CoreHome', async () => {
  const { ref } = await import('vue');
  const { coreHomeMock } = await import('../testCoreHomeMock');
  const hashParsed = ref<Record<string, unknown>>({});

  // replaceHash() goes round MatomoUrl - which has no replace of its own - and reaches the rest of
  // the page through the hashchange CoreHome itself listens for. Mirror that listener here, or the
  // page would never see the hash it just wrote.
  window.addEventListener('hashchange', () => {
    hashParsed.value = Object.fromEntries(
      new URLSearchParams(window.location.hash.replace(/^[#?]+/, '')).entries(),
    );
  });

  return {
    ...coreHomeMock(),
    // the whole tree mounts, so every CoreHome name any descendant imports has to be here
    ActivityIndicator: { name: 'ActivityIndicator', template: '<div/>' },
    ContentBlock: { name: 'ContentBlock', template: '<div><slot/></div>' },
    ContentIntro: { name: 'ContentIntro', template: '<div><slot/></div>' },
    ContentTable: { name: 'ContentTable', template: '<div><slot/></div>' },
    SearchInput: { name: 'SearchInput', template: '<div/>' },
    NotificationsStore: { show: vi.fn(), remove: vi.fn() },
    externalLink: (url: string) => url,
    externalRawLink: (url: string) => url,
    Matomo: { postEvent: vi.fn() },
    AjaxHelper: { post: vi.fn() },
    MatomoUrl: {
      ...coreHomeMock().MatomoUrl,
      hashParsed,
      // drops emptied parameters, as CoreHome's does - the component clears one by writing null
      updateHash: (params: Record<string, unknown>) => {
        hashParsed.value = Object.fromEntries(
          Object.entries(params).filter(([, value]) => value !== null && value !== undefined),
        );
        // CoreHome writes window.location.hash, which pushes a history entry; the page relies on
        // that for browser Back, and on replaceHash() not doing it
        window.history.pushState(null, '', `#?${new URLSearchParams(
          Object.entries(hashParsed.value).map(([key, value]) => [key, String(value)]),
        ).toString()}`);
      },
    },
  };
});

vi.mock('CorePluginsAdmin', () => ({
  Field: { name: 'Field', template: '<div/>' },
  SaveButton: { name: 'SaveButton', template: '<div/>' },
  PluginName: { name: 'PluginName', template: '<div/>' },
  InstallAllPaidPluginsButton: { name: 'InstallAllPaidPluginsButton', template: '<div/>' },
}));

/* eslint-disable import/first */
import { AjaxHelper, MatomoUrl } from 'CoreHome';
import Marketplace from './Marketplace.vue';
import { makePlugin, makePlugins } from '../testMarketplaceFixtures';
import { PluginCard } from '../types';

// every page left mounted keeps watching the one shared hash, so it would react to the next
// spec's navigation as well as its own
enableAutoUnmount(afterEach);

const PAGE_SIZE = 15;
const FETCH_TIMEOUT_MS = 30000;

/** The catalogue is fetched as two requests - plugins, then themes - raced against a timer. */
function respondWith(plugins: PluginCard[], themes: PluginCard[] = []) {
  (AjaxHelper.post as ReturnType<typeof vi.fn>)
    .mockResolvedValueOnce(plugins)
    .mockResolvedValueOnce(themes);
}

/** A request that never settles, which is what AjaxHelper does when the server is unreachable. */
function neverRespond() {
  (AjaxHelper.post as ReturnType<typeof vi.fn>)
    .mockReturnValue(new Promise(() => {}));
}

/**
 * The sentinel's observer, so a spec can say the reader reached the bottom. jsdom has no
 * IntersectionObserver, and without one the component renders every result at once.
 */
function stubIntersectionObserver(): { scrollToBottom: () => void } {
  let callback: ((entries: { isIntersecting: boolean }[]) => void)|null = null;

  window.IntersectionObserver = class {
    constructor(handler: (entries: { isIntersecting: boolean }[]) => void) {
      callback = handler;
    }

    observe() {} // eslint-disable-line class-methods-use-this

    unobserve() {} // eslint-disable-line class-methods-use-this

    disconnect() {} // eslint-disable-line class-methods-use-this
  } as unknown as typeof window.IntersectionObserver;

  return { scrollToBottom: () => callback?.([{ isIntersecting: true }]) };
}

function mountPage(attachTo?: HTMLElement) {
  return mount(Marketplace, {
    attachTo,
    props: {
      defaultSort: 'lastupdated',
      installNonce: 'i',
      activateNonce: 'a',
      deactivateNonce: 'd',
      updateNonce: 'u',
      numUsers: 1,
    },
    global: {
      stubs: {
        MarketplaceHero: true,
        CategoryTabs: true,
        SortMenu: true,
        PluginGrid: true,
        PluginSection: true,
        EmptyState: true,
        RequestTrial: true,
        PluginDetails: true,
      },
    },
  });
}

/**
 * Enough Featured plugins for the row to show, and one category for a row of its own - the page
 * shows the section stack only while it has sections.
 */
function promotedCatalogue(): PluginCard[] {
  return [
    ...makePlugins(4).map((plugin, index) => ({ ...plugin, promotions: { featured: index } })),
    makePlugin({ name: 'Funnels', categories: ['insights'] }),
  ];
}

/** The row with the given id, as the section stack hands it to its PluginSection. */
function section(wrapper: VueWrapper, sectionId: string): VueWrapper {
  const found = wrapper.findAllComponents({ name: 'PluginSection' })
    .find((candidate) => candidate.props('sectionId') === sectionId);

  if (!found) {
    throw new Error(`no ${sectionId} section on the page`);
  }

  return found as VueWrapper;
}

/** How many cards the flat grid is currently handed. */
function gridSize(wrapper: VueWrapper): number {
  const grid = wrapper.findComponent({ name: 'PluginGrid' });
  return grid.exists() ? (grid.props('plugins') as PluginCard[]).length : 0;
}

describe('Marketplace', () => {
  beforeEach(() => {
    vi.useFakeTimers();
    (AjaxHelper.post as ReturnType<typeof vi.fn>).mockReset();
    MatomoUrl.hashParsed.value = {};
    stubIntersectionObserver();
    // jsdom ships no CSS object; the deep-link lookup escapes the plugin name with CSS.escape()
    window.CSS = { escape: (value: string) => value } as unknown as typeof window.CSS;
    // nor scrollIntoView, which opening a deep-linked card calls on it
    Element.prototype.scrollIntoView = vi.fn();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  describe('fetching the catalogue', () => {
    it('shows the catalogue once both requests answer', async () => {
      respondWith([makePlugin({ name: 'Funnels' })], [makePlugin({ name: 'Morpht' })]);

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.allPlugins.map((plugin: PluginCard) => plugin.name))
        .toEqual(['Funnels', 'Morpht']);
      expect(wrapper.vm.loading).toBe(false);
      expect(wrapper.find('.marketplacePage__loadError').exists()).toBe(false);
    });

    it('keeps one card for a plugin both requests return', async () => {
      respondWith([makePlugin({ name: 'Funnels' })], [makePlugin({ name: 'Funnels' })]);

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.allPlugins).toHaveLength(1);
    });

    it('gives up on a request that never answers, rather than leaving the skeletons there', async () => {
      neverRespond();

      const wrapper = mountPage();
      await vi.advanceTimersByTimeAsync(FETCH_TIMEOUT_MS - 1);
      expect(wrapper.vm.loading).toBe(true);
      expect(wrapper.vm.loadFailed).toBe(false);

      await vi.advanceTimersByTimeAsync(1);

      expect(wrapper.vm.loading).toBe(false);
      expect(wrapper.vm.loadFailed).toBe(true);
      expect(wrapper.find('.marketplacePage__loadError').exists()).toBe(true);
    });

    it('reports a request that fails outright', async () => {
      (AjaxHelper.post as ReturnType<typeof vi.fn>).mockRejectedValue(new Error('nope'));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.loadFailed).toBe(true);
    });

    it('lets a superseded fetch neither overwrite nor fail the one that replaced it', async () => {
      neverRespond();

      const wrapper = mountPage();
      await vi.advanceTimersByTimeAsync(1000);

      respondWith([makePlugin({ name: 'Funnels' })]);
      wrapper.vm.refresh();
      await vi.advanceTimersByTimeAsync(1);

      expect(wrapper.vm.allPlugins.map((plugin: PluginCard) => plugin.name)).toEqual(['Funnels']);

      // the abandoned fetch's own timer only fires here, long after the page has painted
      await vi.advanceTimersByTimeAsync(FETCH_TIMEOUT_MS);

      expect(wrapper.vm.allPlugins.map((plugin: PluginCard) => plugin.name)).toEqual(['Funnels']);
      expect(wrapper.vm.loadFailed).toBe(false);
      expect(wrapper.vm.loading).toBe(false);
    });

    it('holds the layout it has while refreshing over a catalogue already on screen', async () => {
      respondWith(makePlugins(3).map((plugin) => ({ ...plugin, categories: ['insights'] })));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();
      expect(wrapper.vm.showSections).toBe(true);

      neverRespond();
      wrapper.vm.refresh();
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.loading).toBe(true);
      expect(wrapper.vm.showSections).toBe(true);
      expect(wrapper.vm.skeletonCount).toBe(0);
    });

    it('holds the layout with skeletons while there is no catalogue yet', () => {
      neverRespond();

      const wrapper = mountPage();

      expect(wrapper.vm.showSections).toBe(false);
      expect(wrapper.vm.skeletonCount).toBeGreaterThan(0);
    });
  });

  describe('opening and closing a plugin', () => {
    beforeEach(() => {
      window.history.replaceState(null, '', '#?');
      MatomoUrl.hashParsed.value = {};
    });

    it('names the plugin in the hash rather than keeping it to itself', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await wrapper.vm.$nextTick();

      // the plugin management screen opens a plugin here by writing this parameter itself, so it
      // has to mean the same thing when this page writes it
      expect(MatomoUrl.hashParsed.value.showPlugin).toBe(wrapper.vm.allPlugins[0].name);
      expect(wrapper.vm.selectedPlugin?.name).toBe(wrapper.vm.allPlugins[0].name);

      // and what is rendered follows once the views have changed over
      await vi.runOnlyPendingTimersAsync();
      expect(wrapper.vm.viewPluginName).toBe(wrapper.vm.allPlugins[0].name);
    });

    it('holds the page blank while the two views change places', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await wrapper.vm.$nextTick();

      // the scroll position moves during this, and behind the blank the jump is not seen
      expect(wrapper.vm.switching).toBe(true);
      expect(wrapper.vm.viewPluginName).toBe('');

      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.switching).toBe(false);
      expect(wrapper.vm.viewPluginName).toBe(wrapper.vm.allPlugins[0].name);
    });

    it('does not leave a half-finished change over when another plugin is opened', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await wrapper.vm.$nextTick();
      wrapper.vm.openDetails(wrapper.vm.allPlugins[1]);
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.switching).toBe(false);
      expect(wrapper.vm.viewPluginName).toBe(wrapper.vm.allPlugins[1].name);
    });

    it('opens the plugin a hash written by someone else names', async () => {
      respondWith(makePlugins(3));
      MatomoUrl.hashParsed.value = { showPlugin: 'plugin1' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.selectedPlugin?.name).toBe('plugin1');
    });

    it('opens the plugin without waiting for the catalogue', () => {
      respondWith(makePlugins(3));
      MatomoUrl.hashParsed.value = { showPlugin: 'plugin1' };

      const wrapper = mountPage();

      // the details request needs the name and nothing else, so a cold deep link must not paint
      // the grid and its skeletons first and replace them once the listing lands - and it opens
      // there rather than fading into it from a catalogue the reader never saw
      expect(wrapper.vm.selectedPlugin).toBe(null);
      expect(wrapper.vm.viewPluginName).toBe('plugin1');
      expect(wrapper.vm.switching).toBe(false);
      expect(wrapper.vm.detailsCard).toEqual({ name: 'plugin1' });
      expect(wrapper.find('.marketplacePage__catalogue').attributes('style'))
        .toContain('display: none');
    });

    it('hands over the card row once the catalogue carries one', async () => {
      respondWith(makePlugins(3));
      MatomoUrl.hashParsed.value = { showPlugin: 'plugin1' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.detailsCard.displayName).toBeDefined();
    });

    it('still opens a plugin the catalogue does not carry, for its own error', async () => {
      respondWith(makePlugins(3));
      MatomoUrl.hashParsed.value = { showPlugin: 'NotAPlugin' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      // delisted since the list was cached: the details request says so, which is more use than
      // dropping the reader on the catalogue with the plugin still named in the hash
      expect(wrapper.vm.selectedPlugin).toBe(null);
      expect(wrapper.vm.detailsCard).toEqual({ name: 'NotAPlugin' });
    });

    it('closes when the hash stops naming a plugin, as browser Back leaves it', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await vi.runOnlyPendingTimersAsync();
      expect(wrapper.vm.selectedPlugin).not.toBe(null);

      MatomoUrl.updateHash({ ...MatomoUrl.hashParsed.value, showPlugin: null });
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.selectedPlugin).toBe(null);
      expect(wrapper.vm.viewPluginName).toBe('');
    });

    it('goes back through its own history entry rather than adding another', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      const back = vi.spyOn(window.history, 'back').mockImplementation(() => {});
      const replace = vi.spyOn(window.history, 'replaceState');

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await vi.runOnlyPendingTimersAsync();
      wrapper.vm.closeDetails();

      expect(back).toHaveBeenCalled();
      expect(replace).not.toHaveBeenCalled();

      back.mockRestore();
      replace.mockRestore();
    });

    it('replaces the entry for a plugin it never navigated to itself', async () => {
      respondWith(makePlugins(3));
      MatomoUrl.hashParsed.value = { showPlugin: 'plugin1' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      const back = vi.spyOn(window.history, 'back').mockImplementation(() => {});

      wrapper.vm.closeDetails();
      await wrapper.vm.$nextTick();

      // history back would leave the page that sent the reader here, not the catalogue
      expect(back).not.toHaveBeenCalled();
      expect(wrapper.vm.selectedPlugin).toBe(null);

      back.mockRestore();
    });

    it('clears a filter hiding the card a deep linked plugin has to come back to', async () => {
      respondWith(makePlugins(3).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'marketing', showPlugin: 'plugin1' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.activeTab).toBe('all');
      expect(wrapper.vm.filteredPlugins.some((p: PluginCard) => p.name === 'plugin1')).toBe(true);
    });

    it('keeps the catalogue mounted behind the plugin page', async () => {
      respondWith(makePlugins(3));

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await vi.runOnlyPendingTimersAsync();

      // v-show, not v-if: coming back has to be a repaint rather than a refetch
      const catalogue = wrapper.find('.marketplacePage__catalogue');
      expect(catalogue.exists()).toBe(true);
      expect(catalogue.attributes('style')).toContain('display: none');
    });

    it('does not page in more cards while a plugin is open', async () => {
      const { scrollToBottom } = stubIntersectionObserver();
      respondWith(makePlugins(40).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openDetails(wrapper.vm.allPlugins[0]);
      await vi.runOnlyPendingTimersAsync();

      // the sentinel is inside the hidden catalogue, so nothing should reach the observer; this
      // asserts the page does not page in behind the reader's back if something does
      scrollToBottom();
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.pageSize).toBe(PAGE_SIZE);
    });
  });

  describe('reading the hash', () => {
    it('opens the tab, sort and query the hash names', async () => {
      respondWith([]);
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights', sort: 'popular', query: 'fun' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.activeTab).toBe('insights');
      expect(wrapper.vm.pluginSort).toBe('popular');
      expect(wrapper.vm.searchQuery).toBe('fun');
    });

    it('starts the list over when the hash changes what is listed', async () => {
      respondWith(makePlugins(40).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.pageSize = 45;
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights', sort: 'popular' };
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.pageSize).toBe(PAGE_SIZE);
    });

    it('leaves how far the reader scrolled alone when the hash change lists the same thing', async () => {
      respondWith(makePlugins(40).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.pageSize = 45;
      // closing the details modal writes the hash without touching tab, sort or query
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights', showPlugin: 'plugin0' };
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.pageSize).toBe(45);
    });
  });

  describe('promotion lists', () => {
    it('opens a promotion from its row in place of the section stack', async () => {
      respondWith(promotedCatalogue());

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      section(wrapper, 'featured').vm.$emit('seeAll', 'featured');
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.activePromotion).toBe('featured');
      expect(MatomoUrl.hashParsed.value).toEqual({ pluginPromotion: 'featured' });
      expect(wrapper.vm.showSections).toBe(false);
      // a promotion is not a tab, so no tab is left highlighted over it
      expect(wrapper.findComponent({ name: 'CategoryTabs' }).exists()).toBe(false);
      expect(wrapper.find('.marketplacePage__backLink').exists()).toBe(true);
      expect(gridSize(wrapper)).toBe(4);
    });

    it('moves focus to the way back, as the button that had it is gone', async () => {
      respondWith(promotedCatalogue());

      const wrapper = mountPage(document.body);
      await vi.runOnlyPendingTimersAsync();

      section(wrapper, 'featured').vm.$emit('seeAll', 'featured');
      await wrapper.vm.$nextTick();
      await wrapper.vm.$nextTick();

      expect(document.activeElement).toBe(wrapper.find('.marketplacePage__backLink').element);
    });

    it('leaves the tab it was opened over, so going back returns to the overview', async () => {
      respondWith(promotedCatalogue());
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      wrapper.vm.openPromotion('featured');
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.activeTab).toBe('all');
      expect(MatomoUrl.hashParsed.value).toEqual({ pluginPromotion: 'featured' });
    });

    it('goes back to the section stack from the back link', async () => {
      respondWith(promotedCatalogue());
      MatomoUrl.hashParsed.value = { pluginPromotion: 'featured' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();
      expect(wrapper.vm.activePromotion).toBe('featured');

      wrapper.vm.pageSize = 45;
      await wrapper.find('.marketplacePage__backLink').trigger('click');

      expect(wrapper.vm.activePromotion).toBe('');
      expect(wrapper.vm.pageSize).toBe(PAGE_SIZE);
      expect(MatomoUrl.hashParsed.value).toEqual({});
      expect(wrapper.vm.showSections).toBe(true);
      expect(wrapper.find('.marketplacePage__backLink').exists()).toBe(false);
    });

    it('opens a category row in its tab rather than as a promotion', async () => {
      respondWith(promotedCatalogue());

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      section(wrapper, 'insights').vm.$emit('seeAll', 'insights');
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.activeTab).toBe('insights');
      expect(wrapper.vm.activePromotion).toBe('');
      expect(MatomoUrl.hashParsed.value).toEqual({ pluginCategory: 'insights' });
    });

    it('ignores a promotion in the hash that the page has no row for', async () => {
      respondWith(promotedCatalogue());
      MatomoUrl.hashParsed.value = { pluginPromotion: 'somethingElse' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.activePromotion).toBe('');
      expect(wrapper.vm.showSections).toBe(true);
    });
  });

  describe('pagination', () => {
    it('renders a page at a time and grows as the reader reaches the bottom', async () => {
      const observer = stubIntersectionObserver();
      respondWith(makePlugins(40).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();
      expect(gridSize(wrapper)).toBe(PAGE_SIZE);

      observer.scrollToBottom();
      await wrapper.vm.$nextTick();
      expect(gridSize(wrapper)).toBe(2 * PAGE_SIZE);

      observer.scrollToBottom();
      await wrapper.vm.$nextTick();
      expect(gridSize(wrapper)).toBe(40);

      // nothing left to add, so the sentinel stops growing the list
      observer.scrollToBottom();
      await wrapper.vm.$nextTick();
      expect(gridSize(wrapper)).toBe(40);
    });

    it('renders every result where no observer can ask for the next page', async () => {
      const realObserver = window.IntersectionObserver;
      delete (window as Partial<Window>).IntersectionObserver;
      respondWith(makePlugins(40).map((plugin) => ({ ...plugin, categories: ['insights'] })));
      MatomoUrl.hashParsed.value = { pluginCategory: 'insights' };

      const wrapper = mountPage();
      await vi.runOnlyPendingTimersAsync();

      expect(wrapper.vm.paginated).toBe(false);
      expect(gridSize(wrapper)).toBe(40);

      window.IntersectionObserver = realObserver;
    });
  });
});
