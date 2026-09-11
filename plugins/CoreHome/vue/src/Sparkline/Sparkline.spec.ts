/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

// The component uses the Matomo globals to build its url, so stub the modules it imports and let
// these specs focus on the loading state.
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

  it('reports every change of its loading state, so a consumer can draw its own placeholder', async () => {
    const wrapper = createWrapper();

    // The initial `true` is not emitted: a parent that cares already starts out loading.
    expect(wrapper.emitted('loadingChange')).toBeUndefined();

    await wrapper.find('img').trigger('load');
    expect(wrapper.emitted('loadingChange')).toEqual([[false]]);

    await wrapper.setProps({ width: 500 });
    expect(wrapper.emitted('loadingChange')).toEqual([[false], [true]]);

    await wrapper.find('img').trigger('load');
    expect(wrapper.emitted('loadingChange')).toEqual([[false], [true], [false]]);
  });

  it('reports the loading state cleared when the image fails, so the placeholder is not permanent', async () => {
    const wrapper = createWrapper();

    await wrapper.find('img').trigger('error');

    expect(wrapper.emitted('loadingChange')).toEqual([[false]]);
  });

  it('draws itself at the size its props ask for, which the width/height attributes cannot do', () => {
    // The width/height attributes lose to any CSS rule, including the 100x25 default in the .less.
    const img = createWrapper().find('img');

    expect(img.attributes('style')).toContain('width: 300px');
    expect(img.attributes('style')).toContain('height: 40px');
  });

  it('leaves the size to CSS when it is given no dimensions', () => {
    const img = createWrapper({ width: undefined, height: undefined }).find('img');

    expect(img.attributes('style')).toBeUndefined();
  });
});
