/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount, VueWrapper } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => (await import('../testCoreHomeMock')).coreHomeMock());

/* eslint-disable import/first */
import CategoryTabs from './CategoryTabs.vue';
import { PluginTab } from '../PluginGrid/pluginGrouping';
import { translateStub } from '../testCoreHomeMock';

/** The component's own `(max-width: 1400px)` test; `narrow` means the overflow menu is showing. */
function stubMatchMedia(narrow: boolean) {
  const listeners: (() => void)[] = [];
  window.matchMedia = vi.fn().mockImplementation((query: string) => ({
    matches: narrow,
    media: query,
    addEventListener: (_: string, handler: () => void) => listeners.push(handler),
    removeEventListener: vi.fn(),
  })) as unknown as typeof window.matchMedia;
  return listeners;
}

function tab(id: string, isCategory = false, count = 1): PluginTab {
  return { id, count, isCategory };
}

const TEN_TABS: PluginTab[] = [
  tab('all'), tab('bundles'), tab('themes'),
  tab('acquisition', true), tab('behaviour', true), tab('conversion', true),
  tab('developer', true), tab('insights', true), tab('security', true),
  tab('other', true),
];

async function mountTabs(tabs: PluginTab[], modelValue = 'all') {
  const wrapper = mount(CategoryTabs, {
    props: { tabs, modelValue },
    global: { mocks: { translate: translateStub } },
  });
  await wrapper.vm.$nextTick();
  return wrapper;
}

/** Only the tabs the bar is actually showing, i.e. not pushed into the overflow menu. */
const visibleTabLabels = (wrapper: VueWrapper) => wrapper
  .findAll('.categoryTabs__tab')
  .filter((t) => !t.classes('categoryTabs__tab--overflow')
    && !t.classes('categoryTabs__moreButton'))
  .map((t) => t.text());

describe('Marketplace/CategoryTabs', () => {
  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('on a wide screen', () => {
    beforeEach(() => stubMatchMedia(false));

    it('shows every tab and no overflow menu', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      expect(visibleTabLabels(wrapper)).toHaveLength(10);
      expect(wrapper.find('.categoryTabs__more').exists()).toBe(false);
    });
  });

  describe('on a narrow screen', () => {
    beforeEach(() => stubMatchMedia(true));

    it('keeps the first five tabs on the bar and moves the rest into the menu', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      expect(visibleTabLabels(wrapper)).toHaveLength(5);

      await wrapper.find('.categoryTabs__moreButton').trigger('click');
      expect(wrapper.findAll('.categoryTabs__menuItem')).toHaveLength(5);
    });

    it('offers no overflow menu when everything already fits', async () => {
      const wrapper = await mountTabs(TEN_TABS.slice(0, 4));
      expect(wrapper.find('.categoryTabs__more').exists()).toBe(false);
    });

    it(
      'names the overflow button after the active tab when the active tab is inside it',
      async () => {
        const wrapper = await mountTabs(TEN_TABS, 'security');
        expect(wrapper.find('.categoryTabs__moreButton').text()).toContain('Security');
        expect(wrapper.find('.categoryTabs__moreButton').classes())
          .toContain('categoryTabs__tab--active');
      },
    );

    it('closes the menu and emits when an overflow tab is chosen', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      await wrapper.find('.categoryTabs__moreButton').trigger('click');
      await wrapper.findAll('.categoryTabs__menuItem')[0].trigger('click');

      expect(wrapper.emitted('update:modelValue')).toEqual([['conversion']]);
      expect(wrapper.findAll('.categoryTabs__menuItem')).toHaveLength(0);
    });

    it('closes the menu on Escape', async () => {
      const wrapper = await mountTabs(TEN_TABS, 'all');
      await wrapper.find('.categoryTabs__moreButton').trigger('click');
      expect(wrapper.findAll('.categoryTabs__menuItem').length).toBeGreaterThan(0);

      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
      await wrapper.vm.$nextTick();

      expect(wrapper.findAll('.categoryTabs__menuItem')).toHaveLength(0);
    });
  });

  describe('labels and state', () => {
    beforeEach(() => stubMatchMedia(false));

    it('marks only the active tab with aria-current', async () => {
      const wrapper = await mountTabs(TEN_TABS, 'themes');
      const current = wrapper.findAll('.categoryTabs__tab')
        .filter((t) => t.attributes('aria-current') === 'page');
      expect(current).toHaveLength(1);
      expect(current[0].text()).toBe('CorePluginsAdmin_Themes');
    });

    it('emits the tab id when a tab is clicked', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      await wrapper.findAll('.categoryTabs__tab')[1].trigger('click');
      expect(wrapper.emitted('update:modelValue')).toEqual([['bundles']]);
    });

    it('uses a translation key for a category that has one', async () => {
      expect(visibleTabLabels(await mountTabs([tab('all'), tab('insights', true)])))
        .toEqual(['Marketplace_AllPlugins', 'Insights']);
    });

    it('falls back to a readable label for a category value with no key yet', async () => {
      expect(visibleTabLabels(await mountTabs([tab('all'), tab('somethingNew', true)])))
        .toEqual(['Marketplace_AllPlugins', 'SomethingNew']);
    });

    it('renders whatever tab list it is given, so an empty tab is simply absent', async () => {
      expect(visibleTabLabels(await mountTabs([tab('all'), tab('themes')])))
        .toEqual(['Marketplace_AllPlugins', 'CorePluginsAdmin_Themes']);
    });

    it('mirrors the tabs into a native select for small screens', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      expect(wrapper.findAll('.categoryTabs__select option')).toHaveLength(10);
    });

    it('emits when the native select changes', async () => {
      const wrapper = await mountTabs(TEN_TABS);
      const select = wrapper.find('.categoryTabs__select select');
      (select.element as HTMLSelectElement).value = 'themes';
      await select.trigger('change');
      expect(wrapper.emitted('update:modelValue')).toEqual([['themes']]);
    });
  });
});
