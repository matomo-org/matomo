/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import PeriodSelectorTrigger from './PeriodSelectorTrigger.vue';

window.piwik.minDateYear = 2011;
window.piwik.minDateMonth = 11;
window.piwik.minDateDay = 15;
window.piwik.maxDateYear = 2014;
window.piwik.maxDateMonth = 3;
window.piwik.maxDateDay = 29;

describe('PeriodSelectorTrigger', () => {
  function mountComponent(customProps = {}) {
    return mount(PeriodSelectorTrigger, {
      props: {
        committedPeriod: 'day',
        committedAnchorDate: new Date('2014-03-28'),
        appliedRangeStartDate: '2014-03-22',
        appliedRangeEndDate: '2014-03-28',
        ...customProps,
      },
    });
  }

  it('renders the date trigger button', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('#date').exists()).toBe(true);
    expect(wrapper.find('.icon-calendar').exists()).toBe(true);
  });

  it('shows move buttons for non-range committed periods', () => {
    const wrapper = mountComponent();

    expect(wrapper.find('.move-period-prev').exists()).toBe(true);
    expect(wrapper.find('.move-period-next').exists()).toBe(true);
  });

  it('hides move buttons for range periods', () => {
    const wrapper = mountComponent({
      committedPeriod: 'range',
      committedAnchorDate: new Date('2014-03-22'),
    });

    expect(wrapper.find('.move-period-prev').exists()).toBe(false);
    expect(wrapper.find('.move-period-next').exists()).toBe(false);
  });

  it('emits move-period when navigation is allowed', async () => {
    const wrapper = mountComponent();

    await wrapper.find('.move-period-prev').trigger('click');

    expect(wrapper.emitted('move-period')?.[0]).toEqual([{ direction: -1 }]);
  });

  it('disables forward navigation at the max boundary', () => {
    const wrapper = mountComponent({
      committedAnchorDate: new Date(2014, 2, 29),
    });

    expect((wrapper.find('.move-period-next').element as HTMLButtonElement).disabled).toBe(true);
  });
});
