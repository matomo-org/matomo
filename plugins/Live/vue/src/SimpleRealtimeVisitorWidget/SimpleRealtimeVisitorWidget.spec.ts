/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

const mockFetch = jest.fn();
const QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY = 'Live_QueryMaxExecutionTimeExceeded';

jest.mock('CoreHome', () => ({
  AjaxHelper: {
    fetch: mockFetch,
  },
  formatNumber: (value: string | number) => new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0,
    minimumFractionDigits: 0,
  }).format(Number(value)),
  translate: (key: string, value?: string|number, value2?: string|number, value3?: string|number) => {
    const translations: Record<string, string> = {
      Live_NbVisitor: '1 visitor',
      Live_NbVisitors: `${value} visitors`,
      General_OneVisit: '1 visit',
      General_NVisits: `${value} visits`,
      General_OneAction: '1 action',
      VisitsSummary_NbActionsDescription: `${value} actions`,
      Intl_OneMinute: '1 minute',
      Intl_NMinutes: `${value} minutes`,
      Live_SimpleRealTimeWidget_Message: `${value} ${value2} ${value3}`,
      [QUERY_MAX_EXECUTION_TIME_EXCEEDED_TRANSLATION_KEY]: 'Query exceeded max execution time',
    };

    return translations[key] || key;
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const SimpleRealtimeVisitorWidget = require('./SimpleRealtimeVisitorWidget.vue').default;

async function flushPromises() {
  await Promise.resolve();
  await Promise.resolve();
}

function mountComponent(props = {}) {
  return mount(SimpleRealtimeVisitorWidget, {
    props: {
      lastMinutes: 3,
      refreshAfterXSecs: 3,
      ...props,
    },
    attachTo: document.body,
    global: {
      mocks: {
        $sanitize: (value: string) => value,
      },
    },
  });
}

describe('Live/SimpleRealtimeVisitorWidget', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.useFakeTimers();
  });

  afterEach(() => {
    jest.runOnlyPendingTimers();
    jest.useRealTimers();
    document.body.innerHTML = '';
  });

  it('shows placeholder counters until the first request resolves', async () => {
    let resolveFetch: ((value: Array<Record<string, number>>) => void)|undefined;
    mockFetch.mockImplementation(() => new Promise((resolve) => {
      resolveFetch = resolve;
    }));

    const wrapper = mountComponent();

    expect(wrapper.find('.simple-realtime-visitor-counter').text()).toBe('-');
    expect(mockFetch).toHaveBeenCalledTimes(1);

    expect(resolveFetch).toBeDefined();
    resolveFetch!([{ visitors: 4, visits: 5, actions: 6 }]);
    await flushPromises();

    expect(wrapper.find('.simple-realtime-visitor-counter').text()).toBe('4');
    wrapper.unmount();
  });

  it('formats counters using the shared number formatter', async () => {
    mockFetch.mockResolvedValue([{ visitors: 1234, visits: 2345, actions: 3456 }]);

    const wrapper = mountComponent();
    await flushPromises();

    expect(wrapper.find('.simple-realtime-visitor-counter').text()).toBe('1,234');
    expect(wrapper.find('.simple-realtime-elaboration').text()).toContain('2,345 visits');
    expect(wrapper.find('.simple-realtime-elaboration').text()).toContain('3,456 actions');
    wrapper.unmount();
  });

  it('retries after non-max-execution-time errors', async () => {
    mockFetch.mockRejectedValue(new Error('network error'));

    mountComponent({ refreshAfterXSecs: 2 });
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(1);

    jest.advanceTimersByTime(2000);
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(2);
  });

  it('stops refreshing and resets counters for max execution time errors', async () => {
    mockFetch.mockRejectedValue(new Error('Query exceeded max execution time details'));

    const wrapper = mountComponent({ refreshAfterXSecs: 2 });
    await flushPromises();

    expect(wrapper.find('.alert').text()).toContain('Query exceeded max execution time');
    expect(wrapper.find('.simple-realtime-visitor-counter').text()).toBe('-');

    jest.advanceTimersByTime(5000);
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(1);
    wrapper.unmount();
  });
});
