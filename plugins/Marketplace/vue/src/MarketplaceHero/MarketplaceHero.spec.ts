/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// Pulled in dynamically: a vi.mock() factory is hoisted above the file's own imports. CoreHome's
// SearchInput is stubbed here rather than in the shared mock: the hero is the only Marketplace
// component that renders it, and the stub keeps the search field's contract visible in this spec.
vi.mock('CoreHome', async () => ({
  ...(await import('../testCoreHomeMock')).coreHomeMock(),
  SearchInput: {
    name: 'SearchInput',
    props: ['modelValue', 'placeholder', 'showClear'],
    emits: ['update:modelValue'],
    template: `<input
      class="searchInputStub"
      :value="modelValue"
      :placeholder="placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
    >`,
  },
}));

/* eslint-disable import/first */
import MarketplaceHero from './MarketplaceHero.vue';
import { translateStub } from '../testCoreHomeMock';

function mountHero(props: Record<string, unknown> = {}) {
  return mount(MarketplaceHero, {
    props: { modelValue: '', ...props },
    global: { mocks: { translate: translateStub } },
  });
}

describe('Marketplace/MarketplaceHero', () => {
  it('heads the page and introduces it', () => {
    const wrapper = mountHero();

    expect(wrapper.find('.marketplaceHero__title').text()).toBe('Marketplace_MatomoMarketplace');
    expect(wrapper.find('.marketplaceHero__subtitle').text()).toBe('Marketplace_IntroShort');
  });

  describe('the search placeholder', () => {
    it('names how many plugins the search covers', () => {
      expect(mountHero({ pluginCount: 142 }).find('.searchInputStub').attributes('placeholder'))
        .toBe('Marketplace_SearchPlaceholderWithCount:142');
    });

    // "Search 0 plugins and themes" is what a count would read as until the catalogue arrives
    it('leaves the count out while the catalogue is still on its way', () => {
      expect(mountHero().find('.searchInputStub').attributes('placeholder'))
        .toBe('Marketplace_SearchPlaceholder');
    });

    it('labels the field with the same text it shows as a placeholder', () => {
      const input = mountHero({ pluginCount: 3 }).find('.searchInputStub');

      expect(input.attributes('aria-label')).toBe(input.attributes('placeholder'));
    });
  });

  describe('the query', () => {
    it('shows the query it is given', () => {
      const input = mountHero({ modelValue: 'funnels' }).find('.searchInputStub');

      expect((input.element as HTMLInputElement).value).toBe('funnels');
    });

    it('passes a typed query straight back up', async () => {
      const wrapper = mountHero();
      const input = wrapper.find('.searchInputStub');

      (input.element as HTMLInputElement).value = 'heatmap';
      await input.trigger('input');

      expect(wrapper.emitted('update:modelValue')).toEqual([['heatmap']]);
    });
  });
});
