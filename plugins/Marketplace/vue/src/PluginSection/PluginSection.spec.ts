/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports.
vi.mock('CoreHome', async () => (await import('../testCoreHomeMock')).coreHomeMock());

/* eslint-disable import/first */
import PluginSection from './PluginSection.vue';
import { CARD_CONTEXT, makePlugins, stubViewport } from '../testMarketplaceFixtures';

async function mountSection(props: Record<string, unknown>) {
  const wrapper = mount(PluginSection, {
    props: {
      sectionId: 'insights',
      isCategory: true,
      plugins: makePlugins(3),
      context: CARD_CONTEXT,
      ...props,
    },
    global: { stubs: { PluginGrid: true } },
  });
  await wrapper.vm.$nextTick();
  return wrapper;
}

const seeAll = '.pluginSection__seeAll';

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
      ['other', true, 'Other'],
      ['ecommerce', true, 'Ecommerce'],
    ])('names the %s section', async (sectionId, isCategory, expected) => {
      const wrapper = await mountSection({ sectionId, isCategory });
      expect(wrapper.find('.pluginSection__heading').text()).toBe(expected);
    });
  });

  describe('see all', () => {
    it('is there when the row is leaving something out', async () => {
      stubViewport(1900);
      expect((await mountSection({ plugins: makePlugins(6) })).find(seeAll).exists()).toBe(true);
    });

    it('is not there when the row already shows everything', async () => {
      stubViewport(1900);
      expect((await mountSection({ plugins: makePlugins(5) })).find(seeAll).exists()).toBe(false);
      expect((await mountSection({ plugins: makePlugins(2) })).find(seeAll).exists()).toBe(false);
    });

    it('counts what this width actually shows, not the widest case', async () => {
      stubViewport(1280);
      expect((await mountSection({ plugins: makePlugins(5) })).find(seeAll).exists()).toBe(true);

      stubViewport(1900);
      expect((await mountSection({ plugins: makePlugins(5) })).find(seeAll).exists()).toBe(false);
    });

    it('appears when the window crosses a breakpoint, without a remount', async () => {
      const { resizeTo } = stubViewport(1900);
      const wrapper = await mountSection({ plugins: makePlugins(4) });
      expect(wrapper.find(seeAll).exists()).toBe(false);

      resizeTo(760);
      await wrapper.vm.$nextTick();
      expect(wrapper.find(seeAll).exists()).toBe(true);
    });

    it('names the section for a screen reader, since every link reads "See all"', async () => {
      stubViewport(1900);
      const wrapper = await mountSection({ plugins: makePlugins(6) });
      expect(wrapper.find(seeAll).attributes('aria-label'))
        .toBe('Marketplace_SeeAllInCategory:Insights');
    });

    it('emits the section id when clicked', async () => {
      stubViewport(1900);
      const wrapper = await mountSection({ plugins: makePlugins(6) });
      await wrapper.find(seeAll).trigger('click');
      expect(wrapper.emitted('seeAll')).toEqual([['insights']]);
    });
  });

  describe('grid', () => {
    beforeEach(() => stubViewport(1900));

    it('hands the grid every plugin, plus the number this width has room for', async () => {
      const wrapper = await mountSection({ plugins: makePlugins(9) });
      const grid = wrapper.findComponent({ name: 'PluginGrid' });

      expect(grid.props('plugins')).toHaveLength(9);
      expect(grid.props('maxCards')).toBe(5);
    });

    it('lowers the grid\'s cut with the width, in step with "See all"', async () => {
      const { resizeTo } = stubViewport(1900);
      const wrapper = await mountSection({ plugins: makePlugins(9) });
      const grid = wrapper.findComponent({ name: 'PluginGrid' });

      resizeTo(1000);
      await wrapper.vm.$nextTick();

      expect(grid.props('maxCards')).toBe(4);
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
