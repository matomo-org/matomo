/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { vi } from 'vitest';

vi.mock('CoreHome', () => ({
  Matomo: {
    period: 'day',
    currentDateString: '2012-08-10',
    startDateString: '2012-08-10',
    endDateString: '2012-08-10',
    helper: {
      // mirrors piwikHelper.calculateEvolution, which rounds to one decimal of a percent
      calculateEvolution: (current: number, past: number) => {
        if (current === past) {
          return 0;
        }
        return past === 0 ? 1 : Math.round(((current - past) / past) * 1000) / 1000;
      },
    },
  },
  AjaxHelper: { fetch: vi.fn() },
  Sparkline: { template: '<div></div>' },
  Range: { getLastNRange: () => ({ startDate: '2012-08-09' }) },
  Periods: { get: () => ({ parse: () => ({ getDateRange: () => ['2012-08-09', '2012-08-09'] }) }) },
  format: (date: string) => date,
  createVueApp: () => ({ mount: vi.fn(), unmount: vi.fn() }),
  translate: (key: string) => key,
}));

import { flushPromises, mount } from '@vue/test-utils';
import { AjaxHelper } from 'CoreHome';
import SingleMetricView from './SingleMetricView.vue';

describe('CoreVisualizations/SingleMetricView', () => {
  it.each([
    ['from unrounded values', 8, 11, '-27.27 %'],
    ['from values the API returns as strings', '8', '11', '-27.27 %'],
    ['as zero for a decrease too small to show', 99999, 100000, '0.00 %'],
    ['as 100% from a past value of zero', 5, 0, '100.00 %'],
    ['as zero when both values are zero', 0, 0, '0.00 %'],
  ])('shows the change between periods %s', async (_, current, past, expected) => {
    // formatted current, raw current, raw past, formatted past
    const values = [current, current, past, past];
    vi.mocked(AjaxHelper.fetch).mockImplementation(
      () => Promise.resolve({ nb_visits: values.shift() }) as ReturnType<typeof AjaxHelper.fetch>,
    );

    const view = mount(SingleMetricView, {
      props: {
        metric: 'nb_visits',
        metricTranslations: { nb_visits: 'Visits' },
        goals: {},
      },
      global: { mocks: { translate: (key: string) => key } },
    });
    await flushPromises();

    expect(view.find('.metricEvolution').text()).toBe(expected);
    view.unmount();
  });
});
