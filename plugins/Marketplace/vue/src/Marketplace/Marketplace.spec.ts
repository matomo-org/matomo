/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports. The hash is a
// real ref so the component's watch() on it fires the way it does in the browser.
vi.mock('CoreHome', async () => {
  const { ref } = await import('vue');
  const { coreHomeMock } = await import('../testCoreHomeMock');
  const hashParsed = ref<Record<string, unknown>>({});

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
          Object.entries(params).filter(([, value]) => null !== value && undefined !== value),
        );
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

function mountPage() {
  return mount(Marketplace, {
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
        StartFreeTrial: true,
        PluginDetailsModal: true,
      },
    },
  });
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
