/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  translate: (key: string, ...args: string[]) => [key, ...args].join(' '),
  translateOrDefault: (key: string) => (
    key === 'Marketplace_CategoryInsights' ? 'Insights' : key
  ),
  ucfirst: (value: string) => `${value.charAt(0).toUpperCase()}${value.slice(1)}`,
  MatomoUrl: { hashParsed: { value: {} }, stringify: () => '' },
}));

/* eslint-disable import/first */
import PluginSection from './PluginSection.vue';
import { PluginCard } from '../types';

/**
 * A matchMedia stub driven by a width, so a section can be mounted at a given breakpoint. The
 * counts it produces are `visibleCardCount`'s, which is what decides whether "See all" is there.
 */
function stubViewport(width: number) {
  const handlers: (() => void)[] = [];
  let current = width;

  window.matchMedia = ((query: string) => ({
    get matches() {
      const max = /max-width:\s*(\d+)px/.exec(query);
      return max ? current <= Number(max[1]) : false;
    },
    media: query,
    addEventListener: (_event: string, handler: () => void) => handlers.push(handler),
    removeEventListener: () => undefined,
  })) as unknown as typeof window.matchMedia;

  return (next: number) => {
    current = next;
    handlers.forEach((handler) => handler());
  };
}

function plugins(count: number): PluginCard[] {
  return Array.from({ length: count }, (_unused, index) => ({
    name: `plugin${index}`,
    displayName: `Plugin ${index}`,
    description: '',
    owner: 'someone',
    categories: [],
    coverImage: '',
    isFree: true,
    isPaid: false,
    isTheme: false,
    isInstalled: false,
    isActivated: false,
    isInvalid: false,
    isDownloadable: true,
    canBeUpdated: false,
    hasDownloadLink: true,
    hasExceededLicense: false,
    isMissingLicense: false,
    isEligibleForFreeTrial: false,
    isTrialRequested: false,
    canTrialBeRequested: false,
    missingRequirements: [],
    numDownloads: 0,
    numDownloadsPretty: '0',
    priceFrom: null,
    consumer: {},
    licenseStatus: '',
    lastUpdated: '',
    lastUpdatedRaw: null,
    createdDateTime: null,
  } as unknown as PluginCard));
}

async function mountSection(props: Record<string, unknown>) {
  const wrapper = mount(PluginSection, {
    props: {
      sectionId: 'insights',
      isCategory: true,
      plugins: plugins(3),
      isAutoUpdatePossible: true,
      isSuperUser: true,
      isValidConsumer: true,
      isMultiServerEnvironment: false,
      isPluginsAdminEnabled: true,
      activateNonce: 'a',
      deactivateNonce: 'd',
      installNonce: 'i',
      updateNonce: 'u',
      ...props,
    },
    global: { stubs: { PluginGrid: true } },
  });
  await wrapper.vm.$nextTick();
  return wrapper;
}

const seeAll = '.marketplaceSection__seeAll';

describe('PluginSection', () => {
  const originalMatchMedia = window.matchMedia;

  afterEach(() => {
    window.matchMedia = originalMatchMedia;
  });

  describe('heading', () => {
    beforeEach(() => stubViewport(1900));

    it.each([
      ['themes', false, 'CorePluginsAdmin_Themes'],
      ['bundles', false, 'Marketplace_Bundles'],
      ['insights', true, 'Insights'],
      ['other', true, 'Marketplace_CategoryOther'],
      ['ecommerce', true, 'Ecommerce'],
    ])('names the %s section', async (sectionId, isCategory, expected) => {
      const wrapper = await mountSection({ sectionId, isCategory });
      expect(wrapper.find('.marketplaceSection__heading').text()).toBe(expected);
    });
  });

  describe('see all', () => {
    it('is there when the row is leaving something out', async () => {
      stubViewport(1900);
      expect((await mountSection({ plugins: plugins(6) })).find(seeAll).exists()).toBe(true);
    });

    it('is not there when the row already shows everything', async () => {
      stubViewport(1900);
      expect((await mountSection({ plugins: plugins(5) })).find(seeAll).exists()).toBe(false);
      expect((await mountSection({ plugins: plugins(2) })).find(seeAll).exists()).toBe(false);
    });

    it('counts what this width actually shows, not the widest case', async () => {
      stubViewport(1280);
      expect((await mountSection({ plugins: plugins(5) })).find(seeAll).exists()).toBe(true);

      stubViewport(1900);
      expect((await mountSection({ plugins: plugins(5) })).find(seeAll).exists()).toBe(false);
    });

    it('appears when the window crosses a breakpoint, without a remount', async () => {
      const resizeTo = stubViewport(1900);
      const wrapper = await mountSection({ plugins: plugins(4) });
      expect(wrapper.find(seeAll).exists()).toBe(false);

      resizeTo(760);
      await wrapper.vm.$nextTick();
      expect(wrapper.find(seeAll).exists()).toBe(true);
    });

    it('names the section for a screen reader, since every link reads "See all"', async () => {
      stubViewport(1900);
      const wrapper = await mountSection({ plugins: plugins(6) });
      expect(wrapper.find(seeAll).attributes('aria-label'))
        .toBe('Marketplace_SeeAllInCategory Insights');
    });

    it('emits the section id when clicked', async () => {
      stubViewport(1900);
      const wrapper = await mountSection({ plugins: plugins(6) });
      await wrapper.find(seeAll).trigger('click');
      expect(wrapper.emitted('seeAll')).toEqual([['insights']]);
    });
  });

  describe('grid', () => {
    beforeEach(() => stubViewport(1900));

    it('hands the grid every plugin, and lets it do the cut', async () => {
      const wrapper = await mountSection({ plugins: plugins(9) });
      const grid = wrapper.findComponent({ name: 'PluginGrid' });

      expect(grid.props('singleRow')).toBe(true);
      expect(grid.props('plugins')).toHaveLength(9);
    });

    it.each(['openDetails', 'requestTrial', 'startFreeTrial'])('forwards %s', async (event) => {
      const wrapper = await mountSection({});
      const plugin = wrapper.props('plugins')[0];

      wrapper.findComponent({ name: 'PluginGrid' }).vm.$emit(event, plugin);
      expect(wrapper.emitted(event)).toEqual([[plugin]]);
    });
  });

  it('stops listening for breakpoint changes once unmounted', async () => {
    stubViewport(1900);
    const removeEventListener = vi.fn();
    window.matchMedia = ((query: string) => ({
      matches: false,
      media: query,
      addEventListener: () => undefined,
      removeEventListener,
    })) as unknown as typeof window.matchMedia;

    (await mountSection({})).unmount();

    expect(removeEventListener).toHaveBeenCalled();
  });
});
