/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import WidgetControlsDropdown from './WidgetControlsDropdown.vue';

jest.mock('../ExpandOnClick/ExpandOnClick', () => ({
  __esModule: true,
  default: {}, // no-op directive; the real one wires document listeners / Matomo helpers
}));

jest.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      Dashboard_Minimise: 'Minimise',
      Dashboard_Maximise: 'Maximise',
      General_Refresh: 'Refresh',
      General_Close: 'Close',
      CoreHome_WidgetControls: 'Widget controls',
    };

    return messages[key] || key;
  },
}));

describe('WidgetControlsDropdown', () => {
  function mountComponent(customProps = {}) {
    return mount(WidgetControlsDropdown, {
      props: {
        canMinimise: true,
        canMaximise: true,
        canRefresh: true,
        canClose: true,
        ...customProps,
      },
    });
  }

  it('should render one menu item per enabled control', () => {
    const wrapper = mountComponent();

    expect(wrapper.findAll('.mtm-dropdownPanel__menuItem').length).toBe(4);
  });

  it('should only render controls whose flag is set', () => {
    const wrapper = mountComponent({
      canMinimise: true,
      canMaximise: false,
      canRefresh: true,
      canClose: false,
    });

    const items = wrapper.findAll('.mtm-dropdownPanel__menuLink');
    expect(items.length).toBe(2);
    expect(wrapper.find('.widgetControl-minimise').exists()).toBe(true);
    expect(wrapper.find('.widgetControl-refresh').exists()).toBe(true);
    expect(wrapper.find('.widgetControl-maximise').exists()).toBe(false);
    expect(wrapper.find('.widgetControl-close').exists()).toBe(false);
  });

  it('should render no controls when all flags are false', () => {
    const wrapper = mountComponent({
      canMinimise: false,
      canMaximise: false,
      canRefresh: false,
      canClose: false,
    });

    expect(wrapper.findAll('.mtm-dropdownPanel__menuItem').length).toBe(0);
  });

  it('should emit the matching intent when a control is clicked', async () => {
    const wrapper = mountComponent();

    await wrapper.find('.widgetControl-close').trigger('click');

    expect(wrapper.emitted('close')).toBeTruthy();
    expect(wrapper.emitted('minimise')).toBeFalsy();
  });

  it('should dispatch a bubbling widgetcontrol:* CustomEvent for the jQuery bridge', async () => {
    const wrapper = mountComponent();
    const received: string[] = [];
    wrapper.element.addEventListener('widgetcontrol:maximise', () => received.push('maximise'));

    await wrapper.find('.widgetControl-maximise').trigger('click');

    expect(received).toEqual(['maximise']);
  });
});
