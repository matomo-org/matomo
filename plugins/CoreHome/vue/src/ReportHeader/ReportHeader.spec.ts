/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import ReportHeader from './ReportHeader.vue';

jest.mock('../translate', () => ({
  translate: (key: string) => {
    const messages: Record<string, string> = {
      Dashboard_Minimise: 'Minimise',
      Dashboard_Maximise: 'Maximise',
      General_Refresh: 'Refresh',
      General_Close: 'Close',
      General_Widget: 'Widget',
    };

    return messages[key] || key;
  },
}));

describe('ReportHeader', () => {
  function mountComponent(customProps = {}) {
    return mount(ReportHeader, {
      props: {
        context: 'dashboard',
        title: 'Visits Over Time',
        ...customProps,
      },
    });
  }

  it('should render the widget title', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.reportHeader__title').text()).toBe('Visits Over Time');
  });

  it('should always render the reserved (empty) report-actions region', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.reportHeader__actions').exists()).toBe(true);
  });

  it('should show all four controls in the dashboard context', () => {
    const wrapper = mountComponent({ context: 'dashboard' });

    expect(wrapper.findAll('.widgetControls__action').length).toBe(4);
  });

  it('should show only minimise and refresh in the maximised context', () => {
    const wrapper = mountComponent({ context: 'maximised' });

    expect(wrapper.find('.widgetControls__action--minimise').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--refresh').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--maximise').exists()).toBe(false);
    expect(wrapper.find('.widgetControls__action--close').exists()).toBe(false);
  });

  it('should show only maximise and close in the collapsed context', () => {
    const wrapper = mountComponent({ context: 'collapsed' });

    expect(wrapper.find('.widgetControls__action--maximise').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--close').exists()).toBe(true);
    expect(wrapper.find('.widgetControls__action--minimise').exists()).toBe(false);
    expect(wrapper.find('.widgetControls__action--refresh').exists()).toBe(false);
  });

  it('should render no controls in the preview and widgetized contexts', () => {
    expect(mountComponent({ context: 'preview' }).find('.widgetControls').exists()).toBe(false);
    expect(mountComponent({ context: 'widgetized' }).find('.widgetControls').exists()).toBe(false);
  });

  it('should re-emit control intents from the row', async () => {
    const wrapper = mountComponent({ context: 'dashboard' });

    await wrapper.find('.widgetControls__action--refresh').trigger('click');

    expect(wrapper.emitted('refresh')).toBeTruthy();
  });

  it('should dispatch a bubbling widgetcontrol:* CustomEvent for the jQuery bridge', async () => {
    const wrapper = mountComponent({ context: 'dashboard' });
    const received: string[] = [];
    wrapper.element.addEventListener('widgetcontrol:maximise', () => received.push('maximise'));

    await wrapper.find('.widgetControls__action--maximise').trigger('click');

    expect(received).toEqual(['maximise']);
  });

  it('should mark the title clickable and emit titleClick when clickable', async () => {
    const wrapper = mountComponent({ context: 'preview', titleClickable: true });

    const title = wrapper.find('.reportHeader__title');
    expect(title.classes()).toContain('reportHeader__title--clickable');
    expect(title.attributes('role')).toBe('button');

    await title.trigger('click');
    expect(wrapper.emitted('titleClick')).toBeTruthy();
  });

  it('should not make the title clickable by default', () => {
    const wrapper = mountComponent();

    const title = wrapper.find('.reportHeader__title');
    expect(title.classes()).not.toContain('reportHeader__title--clickable');
    expect(title.attributes('role')).toBeUndefined();
  });
});
