/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({ WidgetType: {} }), { virtual: true });

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

    await wrapper.findAll('li')[0].trigger('click');
    expect(wrapper.emitted().select).toEqual([['widgetVisits']]);
  });

  it('applies inline top/marginBottom from offsetTop', () => {
    const wrapper = mount(WidgetsList as any, {
      props: { widgets: [widgetVisits], offsetTop: 42 },
    });

    const ul = wrapper.find('ul');
    expect((ul.element as HTMLElement).style.top).toBe('42px');
    expect((ul.element as HTMLElement).style.marginBottom).toBe('42px');
  });
});
