/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { flushPromises, mount } from '@vue/test-utils';

type TestWindow = {
  piwik: Record<string, unknown>;
  initTopControls: () => void;
  Mousetrap: { bind: () => void };
};

const mocked = vi.hoisted(() => {
  // MatomoUrl reads window.piwik directly and updates the page title while being imported, so the
  // globals it relies on have to exist before SiteSelector.vue pulls it in.
  const testWindow = window as unknown as TestWindow;
  testWindow.piwik.updateTitle = () => undefined;
  testWindow.piwik.idSite = '1';
  testWindow.piwik.siteName = 'Site One';
  testWindow.initTopControls = () => undefined;
  // the component registers the "w" keyboard shortcut on mount
  testWindow.Mousetrap = { bind: () => undefined };

  return {
    sites: [
      { idsite: '1', name: 'Site One' },
      { idsite: '2', name: 'Another Site' },
    ],
    loadSite: vi.fn(),
  };
});

vi.mock('./SitesStore', async () => {
  const { computed, ref } = await import('vue');
  const initialSites = ref(mocked.sites);
  const initialSitesFiltered = ref([]);

  return {
    default: {
      initialSites: computed(() => initialSites.value),
      initialSitesFiltered: computed(() => initialSitesFiltered.value),
      matchesCurrentFilteredState: () => true,
      loadInitialSites: () => Promise.resolve(mocked.sites),
      searchSite: () => Promise.resolve(mocked.sites),
      loadSite: mocked.loadSite,
    },
  };
});

// eslint-disable-next-line import/first
import SiteSelector from './SiteSelector.vue';

function setAutocompleteMinSites(value: number) {
  (window as unknown as TestWindow).piwik.config = { autocomplete_min_sites: `${value}` };
}

describe('CoreHome/SiteSelector', () => {
  beforeEach(() => {
    setAutocompleteMinSites(1);
    mocked.loadSite.mockClear();
  });

  async function mountSelector(props: Record<string, unknown> = {}) {
    const wrapper = mount(SiteSelector, {
      props,
      global: {
        mocks: {
          translate: (id: string) => id,
          $sanitize: (value: string) => value,
        },
      },
    });

    await flushPromises();

    return wrapper;
  }

  it('renders the dropdown as a generic dropdown panel', async () => {
    const wrapper = await mountSelector();

    expect(wrapper.find('.piwikSelector__dropdown .mtm-dropdownPanel').exists()).toBe(true);
    expect(wrapper.find('.mtm-dropdownPanel__menu--scrollable').exists()).toBe(true);
    expect(wrapper.find('.mtm-dropdownPanel__menu--gutter').exists()).toBe(true);
    expect(wrapper.find('.dropdown').exists()).toBe(false);
  });

  it('renders one panel menu item per site', async () => {
    const wrapper = await mountSelector({ showSelectedSite: true, showAllSitesItem: false });

    const labels = wrapper.findAll('.mtm-dropdownPanel__menuLabel').map((el) => el.text());

    expect(labels).toEqual(['Site One', 'Another Site']);
  });

  it('hosts the generic search input in the panel search nest element', async () => {
    const wrapper = await mountSelector();

    const search = wrapper.find('.mtm-dropdownPanel__search');

    expect(search.exists()).toBe(true);
    expect(search.find('.mtm-searchInput__input').exists()).toBe(true);
  });

  it('hides the search when there are fewer sites than the configured minimum', async () => {
    setAutocompleteMinSites(10);

    const wrapper = await mountSelector();

    expect(wrapper.find('.mtm-dropdownPanel__search').attributes('style')).toContain('display: none');
  });

  it('renders the all websites entry last by default', async () => {
    const wrapper = await mountSelector({ showSelectedSite: true, allSitesText: 'All Websites' });

    const labels = wrapper.findAll('.mtm-dropdownPanel__menuLabel').map((el) => el.text());

    expect(labels[labels.length - 1]).toBe('All Websites');
  });

  it('switches to the clicked site', async () => {
    const wrapper = await mountSelector({ showSelectedSite: true, showAllSitesItem: false });

    await wrapper.findAll('.mtm-dropdownPanel__menuLabel')[1].trigger('click');

    expect(mocked.loadSite).toHaveBeenCalledWith('2');
    expect(wrapper.emitted('update:modelValue')?.pop()).toEqual([{ id: '2', name: 'Another Site' }]);
  });

  // The label is a child element of the link, so the handler has to walk up to the anchor to find
  // the href. Reading event.target.href directly would silently break opening in a new tab.
  it('opens the clicked site in a new tab on ctrl-click', async () => {
    const openSpy = vi.spyOn(window, 'open').mockImplementation(() => null);
    const wrapper = await mountSelector({ showSelectedSite: true, showAllSitesItem: false });

    try {
      await wrapper.findAll('.mtm-dropdownPanel__menuLabel')[1].trigger('click', { ctrlKey: true });

      expect(openSpy).toHaveBeenCalledTimes(1);
      expect(openSpy.mock.calls[0][0]).toContain('idSite=2');
      expect(openSpy.mock.calls[0][1]).toBe('_blank');
      expect(mocked.loadSite).not.toHaveBeenCalled();
    } finally {
      openSpy.mockRestore();
    }
  });

  it('keeps the panel open when the no-result row is clicked', async () => {
    const wrapper = await mountSelector();

    await wrapper.find('.title').trigger('click');
    await flushPromises();
    expect(wrapper.classes()).toContain('expanded');

    await wrapper.find('.mtm-dropdownPanel__noResult').trigger('click');

    expect(wrapper.classes()).toContain('expanded');
  });

  it('renders the all websites entry first when placed at the top', async () => {
    const wrapper = await mountSelector({
      showSelectedSite: true,
      allSitesLocation: 'top',
      allSitesText: 'All Websites',
    });

    const labels = wrapper.findAll('.mtm-dropdownPanel__menuLabel').map((el) => el.text());

    expect(labels[0]).toBe('All Websites');
  });
});
