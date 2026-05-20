/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({
  translate: (key: string) => key,
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const WidgetsList = require('./WidgetsList.vue').default;

const widgetVisits = {
  uniqueId: 'widgetVisits',
  name: 'Visits Over Time',
  parameters: {},
  category: { id: 'Visitors_VisitsOverTime' },
};
const widgetKpi = {
  uniqueId: 'widgetKpi',
  name: 'KPI',
  parameters: {},
  category: { id: 'General_KpiMetric' },
};
const widgetBlocked = {
  uniqueId: 'widgetBlocked',
  name: 'Already On Dashboard',
  parameters: {},
  category: { id: 'Visitors' },
};
const widgetLongName = {
  uniqueId: 'widgetLongName',
  name: 'This is a very long widget name that should wrap instead of truncating in the modal',
  parameters: {},
  category: { id: 'Visitors' },
};

describe('Dashboard/AddWidgetModal/WidgetsList', () => {
  let dashboardArea: HTMLElement;

  beforeEach(() => {
    jest.useFakeTimers();
    dashboardArea = document.createElement('div');
    dashboardArea.id = 'dashboardWidgetsArea';
    const placed = document.createElement('div');
    placed.setAttribute('widgetId', 'widgetBlocked');
    dashboardArea.appendChild(placed);
    document.body.appendChild(dashboardArea);
  });

  afterEach(() => {
    jest.useRealTimers();
    document.body.removeChild(dashboardArea);
  });

  it('renders unavailable class for widgets already on the dashboard, except for KPI metrics', () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits, widgetKpi, widgetBlocked] },
    });

    const items = wrapper.findAll('li');
    expect(items[0].classes()).not.toContain('widgetpreview-unavailable');
    expect(items[1].classes()).not.toContain('widgetpreview-unavailable');
    expect(items[2].classes()).toContain('widgetpreview-unavailable');
  });

  it('emits hover with a 400ms debounce', async () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits] },
    });

    await wrapper.findAll('li')[0].trigger('mouseenter');
    expect(wrapper.emitted().hover).toBeUndefined();

    jest.advanceTimersByTime(399);
    expect(wrapper.emitted().hover).toBeUndefined();

    jest.advanceTimersByTime(1);
    expect(wrapper.emitted().hover).toEqual([['widgetVisits']]);
  });

  it('cancels the debounced hover on mouseleave', async () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits] },
    });

    await wrapper.findAll('li')[0].trigger('mouseenter');
    await wrapper.findAll('li')[0].trigger('mouseleave');

    jest.advanceTimersByTime(500);
    expect(wrapper.emitted().hover).toBeUndefined();
  });

  it('emits select on click', async () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits] },
    });
    (wrapper.vm as unknown as { supportsHover: boolean }).supportsHover = true;

    await wrapper.findAll('li')[0].trigger('click');
    expect(wrapper.emitted().select).toEqual([['widgetVisits']]);
  });

  it('still emits select when clicking a widget already on the dashboard', async () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetBlocked] },
    });
    (wrapper.vm as unknown as { supportsHover: boolean }).supportsHover = true;

    expect(wrapper.findAll('li')[0].classes()).toContain('widgetpreview-unavailable');

    await wrapper.findAll('li')[0].trigger('click');
    expect(wrapper.emitted().select).toEqual([['widgetBlocked']]);
  });

  it('still emits select when clicking a widget added earlier in the session', async () => {
    const wrapper = mount(WidgetsList as any, {
      props: {
        widgets: [widgetVisits],
        addedWidgets: new Set(['widgetVisits']),
      },
    });
    (wrapper.vm as unknown as { supportsHover: boolean }).supportsHover = true;

    expect(wrapper.findAll('li')[0].classes()).toContain('widgetpreview-unavailable');

    await wrapper.findAll('li')[0].trigger('click');
    expect(wrapper.emitted().select).toEqual([['widgetVisits']]);
  });

  it('renders the add hint using the shared translation key', () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits] },
    });

    expect(wrapper.find('.widgetpreview-add-hint').text()).toBe('+ General_Add');
  });

  it('renders the full widget name text for long labels', () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetLongName] },
    });

    expect(wrapper.find('.widgetpreview-widgetname').text()).toBe(widgetLongName.name);
  });
});
