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
import EmptyState from './EmptyState.vue';
import { translateStub } from '../testCoreHomeMock';

function mountEmptyState(props: Record<string, unknown> = {}) {
  return mount(EmptyState, {
    props,
    global: { mocks: { translate: translateStub } },
  });
}

describe('Marketplace/EmptyState', () => {
  it('says that nothing was found', () => {
    expect(mountEmptyState().find('.marketplaceEmptyState__message').text())
      .toBe('Marketplace_NoPluginsFound');
  });

  it('offers to clear the search when something was searched for', () => {
    expect(mountEmptyState({ hasQuery: true }).find('.marketplaceEmptyState__reset').text())
      .toBe('Marketplace_ResetFilters');
  });

  it('offers the whole catalogue when only a category is narrowing the list', () => {
    expect(mountEmptyState({ hasQuery: false }).find('.marketplaceEmptyState__reset').text())
      .toBe('Marketplace_ShowAllPlugins');
  });

  it('emits reset when the button is pressed', async () => {
    const wrapper = mountEmptyState({ hasQuery: true });
    await wrapper.find('.marketplaceEmptyState__reset').trigger('click');

    expect(wrapper.emitted('reset')).toHaveLength(1);
  });

  // an empty catalogue reaches this state with nothing filtered, where the button would do nothing
  it('offers no reset at all when there is nothing to reset', () => {
    const wrapper = mountEmptyState({ canReset: false });

    expect(wrapper.find('.marketplaceEmptyState__message').exists()).toBe(true);
    expect(wrapper.find('.marketplaceEmptyState__reset').exists()).toBe(false);
  });
});
