/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shallowMount } from '@vue/test-utils';
import { consumeStoredValue, getStoredValue, removeStoredValue, setStoredValue } from './storage';

type PlainObject = Record<string, unknown>;

const mockOn = jest.fn();
const mockDollar = jest.fn(() => ({ on: mockOn }));

const mockFetch = jest.fn();
const mockPost = jest.fn();
const mockShowNotification = jest.fn();
const mockRemoveNotification = jest.fn();
const DASHBOARD_EXPORT_STORAGE_KEY = 'scheduledReports.dashboardExportId';

const mockMatomo = {
  helper: {
    lazyScrollTo: jest.fn(),
    htmlDecode: (value: string) => value,
    refreshAfter: jest.fn(),
    hideAjaxError: jest.fn(),
    modalConfirm: jest.fn(),
  },
  postEvent: jest.fn(),
  idSite: 1,
  timezoneOffset: 0,
};

const mockMatomoUrl = {
  parsed: { value: {} as PlainObject },
};

const mockTranslate = (key: string) => key;

jest.mock('CoreHome', () => ({
  AjaxHelper: {
    fetch: mockFetch,
    post: mockPost,
  },
  ContentTable: {},
  format: () => '2026-02-24',
  getToday: () => new Date('2026-02-24'),
  Matomo: mockMatomo,
  MatomoUrl: mockMatomoUrl,
  MatomoLoader: {},
  NotificationsStore: {
    show: mockShowNotification,
    remove: mockRemoveNotification,
  },
  translate: mockTranslate,
}), { virtual: true });

jest.mock('CorePluginsAdmin', () => ({
  Form: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const ManageScheduledReport = require('./ManageScheduledReport.vue').default;

const defaultProps = {
  contentTitle: '',
  userLogin: 'admin',
  loginModule: 'Login',
  reports: [],
  siteName: 'Test Site',
  segmentEditorActivated: false,
  savedSegmentsById: {},
  periods: {},
  downloadOutputType: 1,
  language: 'en',
  reportFormatsByReportType: {},
  paramPeriods: {},
  reportTypeOptions: {},
  reportFormatsByReportTypeOptions: {},
  displayFormats: {},
  reportsByCategoryByReportType: {},
  allowMultipleReportsByReportType: {},
  countWebsites: 1,
  reportTypes: {},
};

function mountComponent() {
  return shallowMount(ManageScheduledReport as PlainObject, {
    props: defaultProps,
    global: {
      mocks: {
        translate: mockTranslate,
      },
    },
  });
}

async function flushPromises() {
  await Promise.resolve();
  await Promise.resolve();
  await new Promise((resolve) => {
    setTimeout(resolve, 0);
  });
}

describe('ScheduledReports/ManageScheduledReport storage helper', () => {
  it('gets, sets, removes and consumes stored values', () => {
    setStoredValue('key', 'value');

    expect(getStoredValue('key')).toBe('value');

    expect(consumeStoredValue('key')).toBe('value');
    expect(getStoredValue('key')).toBeNull();

    setStoredValue('key', 'another');
    removeStoredValue('key');
    expect(getStoredValue('key')).toBeNull();
  });

  it('returns null when consuming a missing value', () => {
    expect(consumeStoredValue('missing')).toBeNull();
  });
});

describe('ScheduledReports/ManageScheduledReport dashboard export bootstrap', () => {
  beforeAll(() => {
    const testWindow = window as unknown as {
      $: unknown;
      jQuery: unknown;
      piwikHelper: unknown;
    };

    testWindow.$ = mockDollar;
    testWindow.jQuery = mockDollar;
    testWindow.piwikHelper = {
      escape: (value: string) => value,
    };
  });

  beforeEach(() => {
    jest.clearAllMocks();
    sessionStorage.clear();
    mockMatomoUrl.parsed.value = {};
    mockFetch.mockResolvedValue({
      dashboardName: 'dashboard',
      email: { VisitsSummary_get: true },
      unmappedWidgets: [],
    });
  });

  it('does not call API when dashboard export state is absent', async () => {
    mountComponent();
    await flushPromises();

    expect(mockFetch).not.toHaveBeenCalled();
    expect(mockShowNotification).not.toHaveBeenCalled();
  });

  it('shows invalid dashboard and skips API for invalid stored dashboard id', async () => {
    sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, 'foo');
    mountComponent();
    await flushPromises();

    expect(mockFetch).not.toHaveBeenCalled();
    expect(mockShowNotification).toHaveBeenCalledWith(expect.objectContaining({
      message: 'ScheduledReports_ExportDashboardInvalidDashboard',
      context: 'error',
      type: 'persistent',
    }));
  });

  it('calls API exactly once for a valid stored dashboard id', async () => {
    sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, '7');
    mockFetch.mockResolvedValue({
      dashboardName: '',
      email: {},
      unmappedWidgets: [],
    });
    mountComponent();
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(1);
    expect(mockFetch).toHaveBeenCalledWith(expect.objectContaining({
      method: 'ScheduledReports.getWidgetReportMap',
      dashId: '7',
      idSite: 1,
    }));
  });

  it('shows generic retry error for fetch failures on valid stored dashboard id', async () => {
    sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, '7');
    mockFetch.mockRejectedValue(new Error('network'));
    mountComponent();
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(1);
    expect(mockShowNotification).toHaveBeenCalledWith(expect.objectContaining({
      message: 'General_ErrorTryAgain',
      context: 'error',
      type: 'toast',
    }));
  });

  it('shows invalid dashboard when mapping response is not usable', async () => {
    sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, '7');
    mockFetch.mockResolvedValue({
      dashboardName: '',
      email: {},
      unmappedWidgets: [],
    });
    mountComponent();
    await flushPromises();

    expect(mockFetch).toHaveBeenCalledTimes(1);
    expect(mockShowNotification).toHaveBeenCalledWith(expect.objectContaining({
      message: 'ScheduledReports_ExportDashboardInvalidDashboard',
      context: 'error',
      type: 'persistent',
    }));
  });

  it('removes stored dashboard export state before starting async export work', () => {
    sessionStorage.setItem(DASHBOARD_EXPORT_STORAGE_KEY, '7');
    mockFetch.mockReturnValue(new Promise(() => {}));

    mountComponent();

    expect(sessionStorage.getItem(DASHBOARD_EXPORT_STORAGE_KEY)).toBeNull();
    expect(mockFetch).toHaveBeenCalledTimes(1);
  });
});
