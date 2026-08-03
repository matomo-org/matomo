/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import WidgetControls from './WidgetControls.vue';

jest.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      Dashboard_Minimise: 'Minimise',
      Dashboard_Maximise: 'Maximise',
      General_Refresh: 'Refresh',
      General_Close: 'Close',
    };

    return messages[key] || key;
  },
}));

describe('WidgetControls', () => {
  function mountComponent(customProps = {}) {
    return mount(WidgetControls, {
      props: {
        canMinimise: true,
        canMaximise: true,
        canRefresh: true,
        canClose: true,
        ...customProps,
      },
    });
  }

  it('should render one action button per enabled control', () => {
    const wrapper = mountComponent();

    expect(wrapper.findAll('.widgetControls__action').length).toBe(4);
  });

  it('should only render controls whose flag is set', () => {
    const wrapper = mountComponent({
      canMinimise: true,
      canMaximise: false,
      canRefresh: true,
      canClose: false,
    });

    expect(wrapper.findAll('.widgetControls__action').length).toBe(2);
    expect(wrapper.find('.widgetControls__action--minimise').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--refresh').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--maximise').exists()).toBe(false);
    expect(wrapper.find('.widgetControls__action--close').exists()).toBe(false);
  });

  it('should render no controls when all flags are false', () => {
    const wrapper = mountComponent({
      canMinimise: false,
      canMaximise: false,
      canRefresh: false,
      canClose: false,
    });

    expect(wrapper.findAll('.widgetControls__action').length).toBe(0);
  });

  it('should emit the matching intent when a control is clicked', async () => {
    const wrapper = mountComponent();

    await wrapper.find('.widgetControls__action--close').trigger('click');

    expect(wrapper.emitted('close')).toBeTruthy();
    expect(wrapper.emitted('minimise')).toBeFalsy();
  });
});
