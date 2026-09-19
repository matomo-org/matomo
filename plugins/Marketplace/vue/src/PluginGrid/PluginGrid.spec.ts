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
import PluginGrid from './PluginGrid.vue';
import { CARD_CONTEXT, makePlugins } from '../testMarketplaceFixtures';

function mountGrid(props: Record<string, unknown>) {
  return mount(PluginGrid, {
    props: { plugins: makePlugins(8), context: CARD_CONTEXT, ...props },
    global: { stubs: { PluginCard: true, PluginCardSkeleton: true } },
  });
}

describe('PluginGrid', () => {
  it('renders every plugin when no cut is asked for', () => {
    expect(mountGrid({}).findAllComponents({ name: 'PluginCard' })).toHaveLength(8);
  });

  it('renders only the first maxCards, so nothing is in the DOM but hidden', () => {
    const wrapper = mountGrid({ maxCards: 3 });
    const cards = wrapper.findAllComponents({ name: 'PluginCard' });

    expect(cards).toHaveLength(3);
    expect(cards.map((card) => card.props('plugin').name))
      .toEqual(['plugin0', 'plugin1', 'plugin2']);
  });

  it('leaves a shorter list alone', () => {
    const wrapper = mountGrid({ plugins: makePlugins(2), maxCards: 5 });
    expect(wrapper.findAllComponents({ name: 'PluginCard' })).toHaveLength(2);
  });

  it('follows maxCards down as the window narrows', async () => {
    const wrapper = mountGrid({ maxCards: 5 });
    expect(wrapper.findAllComponents({ name: 'PluginCard' })).toHaveLength(5);

    await wrapper.setProps({ maxCards: 2 });
    expect(wrapper.findAllComponents({ name: 'PluginCard' })).toHaveLength(2);
  });

  it('adds skeletons on top of the cards while more are loading', () => {
    const wrapper = mountGrid({ skeletonCount: 4 });
    expect(wrapper.findAllComponents({ name: 'PluginCardSkeleton' })).toHaveLength(4);
  });
});
