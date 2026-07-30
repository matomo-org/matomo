/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import EnrichedHeadline from './EnrichedHeadline.vue';

vi.mock('../translate', () => ({
  translate: (key: string) => key,
  translateOrDefault: (key: string) => key,
}));

function createWrapper(props: Record<string, unknown> = {}) {
  return mount(EnrichedHeadline, {
    props,
    slots: {
      default: '<span>Pages</span>',
    },
    global: {
      config: {
        globalProperties: {
          translate: (key: string) => key,
          $sanitize: (value: string) => value,
        } as never,
      },
    },
  });
}

describe('EnrichedHeadline', () => {
  it('should take the inline help from its prop', () => {
    const wrapper = createWrapper({ inlineHelp: '<p>What this report shows</p>' });

    expect(wrapper.find('.helpIcon .icon-info').exists()).toBe(true);
    expect(wrapper.find('.inlineHelp').html()).toContain('What this report shows');
  });

  it('should not read the documentation out of an adjacent DataTable', () => {
    // the owner passes the documentation in; the old DOM scrape is gone
    document.body.innerHTML = '<div class="reportDocumentation" data-content="scraped"></div>';

    const wrapper = createWrapper();

    expect(wrapper.find('.helpIcon').exists()).toBe(false);
    expect(wrapper.find('.inlineHelp').html()).not.toContain('scraped');
  });

  it('should follow the inlineHelp prop when the owner swaps in another report', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>first</p>' });

    await wrapper.setProps({ inlineHelp: '<p>second</p>' });

    expect(wrapper.find('.inlineHelp').html()).toContain('second');
    expect(wrapper.find('.inlineHelp').html()).not.toContain('first');
  });

  it('should follow the featureName prop, so feedback is filed under the current report', async () => {
    const wrapper = createWrapper({ featureName: 'Pages' });

    await wrapper.setProps({ featureName: 'Entry Pages' });

    expect(wrapper.vm.actualFeatureName).toBe('Entry Pages');
  });

  it('should close an open help popup when the new report has no documentation', async () => {
    const wrapper = createWrapper({ inlineHelp: '<p>first</p>' });

    await wrapper.find('.helpIcon').trigger('click');
    expect(wrapper.vm.showInlineHelp).toBe(true);

    await wrapper.setProps({ inlineHelp: '' });

    expect(wrapper.vm.showInlineHelp).toBe(false);
    expect(wrapper.find('.helpIcon').exists()).toBe(false);
  });

  it('should fall back to the rendered title for the feature name', () => {
    const wrapper = createWrapper();

    expect(wrapper.vm.actualFeatureName).toBe('Pages');
  });
});
