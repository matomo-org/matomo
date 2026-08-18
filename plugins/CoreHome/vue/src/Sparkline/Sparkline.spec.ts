/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The component reaches for the Matomo globals to build its url; stub the modules it imports so the
// specs can focus on the loading state.
vi.mock('../Matomo/Matomo', () => ({
  default: {
    getThemeMode: () => 'light',
    getSparklineColors: () => ({ lineColor: '#162C4A' }),
    period: 'day',
    currentDateString: '2026-07-25',
    minDateYear: 2020,
    minDateMonth: 1,
    minDateDay: 1,
    shouldPropagateTokenAuth: false,
  },
}));

vi.mock('../MatomoUrl/MatomoUrl', () => ({
  default: {
    parsed: { value: {} },
    parse: (search: string) => Object.fromEntries(new URLSearchParams(search)),
    stringify: (params: Record<string, unknown>) => new URLSearchParams(
      Object.entries(params).map(([key, value]) => [key, String(value)]),
    ).toString(),
  },
}));

vi.mock('../AjaxHelper/AjaxHelper', () => ({
  default: class {
    // eslint-disable-next-line class-methods-use-this
    mixinDefaultGetParams(params: Record<string, unknown>) {
      return params;
    }
  },
}));

vi.mock('../Periods/Range', () => ({
  default: {
    getLastNRange: () => ({
      getDateRange: () => [new Date(2026, 5, 26), new Date(2026, 6, 25)],
    }),
  },
}));

vi.mock('../Periods', () => ({
  format: (date: Date) => date.toISOString().slice(0, 10),
}));

import Sparkline from './Sparkline.vue';

describe('CoreHome/Sparkline', () => {
  function createWrapper(props = {}) {
    return mount(Sparkline as never, {
      props: {
        params: '?module=API&action=get&columns=nb_visits',
        width: 300,
        height: 40,
        ...props,
      },
    });
  }

  it('marks itself loading until its first image has painted', async () => {
    const wrapper = createWrapper();
    expect(wrapper.find('img').classes()).toContain('sparklineImg--loading');

    await wrapper.find('img').trigger('load');

    expect(wrapper.find('img').classes()).not.toContain('sparklineImg--loading');
  });

  it('clears the loading state when the image fails, rather than showing a placeholder forever', async () => {
    const wrapper = createWrapper();

    await wrapper.find('img').trigger('error');

    expect(wrapper.find('img').classes()).not.toContain('sparklineImg--loading');
  });

  it('goes back to loading when a resize requests a new image, and clears again on arrival', async () => {
    // Reassigning src drops the bitmap the browser was showing, so the card would otherwise sit
    // empty for the length of the request.
    const wrapper = createWrapper();
    await wrapper.find('img').trigger('load');
    expect(wrapper.find('img').classes()).not.toContain('sparklineImg--loading');

    await wrapper.setProps({ width: 500 });
    expect(wrapper.find('img').classes()).toContain('sparklineImg--loading');

    await wrapper.find('img').trigger('load');
    expect(wrapper.find('img').classes()).not.toContain('sparklineImg--loading');
  });
});
