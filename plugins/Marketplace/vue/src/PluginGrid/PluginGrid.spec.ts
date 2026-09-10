/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

vi.mock('CoreHome', () => ({
  MatomoUrl: { parsed: { value: { idSite: '1' } }, hashParsed: { value: {} }, stringify: () => '' },
  translate: (key: string) => key,
  translateOrDefault: (key: string) => key,
  ucfirst: (value: string) => value,
}));

/* eslint-disable import/first */
import PluginGrid from './PluginGrid.vue';
import { PluginCard as PluginCardType } from '../types';

function plugins(count: number): PluginCardType[] {
  return Array.from({ length: count }, (_unused, index) => ({
    name: `plugin${index}`,
    displayName: `Plugin ${index}`,
    description: '',
    owner: 'someone',
    categories: [],
    coverImage: '',
  } as unknown as PluginCardType));
}

function mountGrid(props: Record<string, unknown>) {
  return mount(PluginGrid, {
    props: {
      plugins: plugins(8),
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
    const wrapper = mountGrid({ plugins: plugins(2), maxCards: 5 });
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
