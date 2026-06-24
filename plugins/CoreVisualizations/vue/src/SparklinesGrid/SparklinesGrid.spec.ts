/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

jest.mock('CoreHome', () => ({
  Sparkline: { template: '<img class="sparkline-stub" />' },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SparklinesGrid = require('./SparklinesGrid.vue').default;

describe('CoreVisualizations/SparklinesGrid', () => {
  function entry(description: string) {
    return {
      url: '?module=API&action=get',
      metrics: { '': [{ value: '1', description }] },
      order: 1,
      title: null,
      group: '0',
      seriesIndices: null,
      graphParams: null,
    };
  }

  function createWrapper(props: Record<string, unknown> = {}) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    return mount(SparklinesGrid as any, {
      props: {
        sparklines: { 0: [entry('Visits')], 1: [entry('Actions'), entry('Bounce rate')] },
        ...props,
      },
    });
  }

  it('flattens grouped sparklines into one card per entry', () => {
    const wrapper = createWrapper();

    expect(wrapper.findAllComponents({ name: 'SparklineCard' }).length).toBe(3);
  });

  it('uses the responsive 4/3/2/1 grid columns on reporting pages', () => {
    const wrapper = createWrapper();
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toEqual(expect.arrayContaining(['col', 's12', 'm6', 'l4', 'xl3']));
  });

  it('collapses to a single column in widget mode', () => {
    const wrapper = createWrapper({ isWidget: true });
    const col = wrapper.find('.row.sparklinesGrid > div');

    expect(col.classes()).toContain('s12');
    expect(col.classes()).not.toContain('xl3');
  });
});
