/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { shallowMount } from '@vue/test-utils';

type PlainObject = Record<string, unknown>;

const rootJQueryMock = {
  widgetPreview: jest.fn(),
  hide: jest.fn(),
  dashboard: jest.fn(),
  find: jest.fn(() => ({ length: 0 })),
};

const dollarMock = jest.fn(() => rootJQueryMock);

const testWindow = window as any;
testWindow.$ = dollarMock;
testWindow.jQuery = dollarMock;
testWindow.widgetsHelper = {
  getWidgetObjectFromUniqueId: jest.fn(),
};

const updateUrlMock = jest.fn();
const getSearchParamMock = jest.fn();
const getLoginModuleMock = jest.fn(() => 'Login');

const MatomoMock = {
  userLogin: 'admin',
  hasSuperUserAccess: false,
  userHasSomeAdminAccess: false,
  postEvent: jest.fn(),
  on: jest.fn(),
  getLoginModule: getLoginModuleMock,
};

const MatomoUrlMock = {
  urlParsed: { value: {} as PlainObject },
  hashParsed: { value: {} as PlainObject },
  getSearchParam: getSearchParamMock,
  updateUrl: updateUrlMock,
};

jest.mock('CoreHome', () => ({
  Matomo: MatomoMock,
  MatomoUrl: MatomoUrlMock,
  translate: (key: string) => key,
  ExpandOnClick: {},
  Tooltips: {},
  WidgetType: {},
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const DashboardSettings = require('./DashboardSettings.vue').default;

describe('Dashboard/DashboardSettings export navigation', () => {
  function mountComponent() {
    return shallowMount(DashboardSettings as any, {
      global: {
        mocks: {
          translate: (key: string) => key,
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();

    MatomoMock.userLogin = 'admin';
    MatomoUrlMock.urlParsed.value = {} as PlainObject;
    MatomoUrlMock.hashParsed.value = {} as PlainObject;
    getSearchParamMock.mockReturnValue('');
    getLoginModuleMock.mockReturnValue('Login');
  });

  describe('#getCurrentDashboardId()', () => {
    it('returns null when no dashboard id can be resolved', () => {
      getSearchParamMock.mockReturnValue('');
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBeNull();
    });

    it('returns a number when subcategory is numeric', () => {
      getSearchParamMock.mockReturnValue('7');
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBe(7);
    });

    it('falls back to query idDashboard when subcategory is missing', () => {
      getSearchParamMock.mockReturnValue('');
      MatomoUrlMock.urlParsed.value = { idDashboard: '9' };
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBe(9);
    });

    it('falls back to hash idDashboard when subcategory and query idDashboard are missing', () => {
      getSearchParamMock.mockReturnValue('');
      MatomoUrlMock.urlParsed.value = {};
      MatomoUrlMock.hashParsed.value = { idDashboard: '13' };
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBe(13);
    });

    it('prefers subcategory over idDashboard', () => {
      getSearchParamMock.mockReturnValue('7');
      MatomoUrlMock.urlParsed.value = { idDashboard: '9' };
      MatomoUrlMock.hashParsed.value = { idDashboard: '13' };
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBe(7);
    });

    it('falls back to query idDashboard when subcategory is invalid', () => {
      getSearchParamMock.mockReturnValue('foo');
      MatomoUrlMock.urlParsed.value = { idDashboard: '9' };
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBe(9);
    });

    it.each(['', 'foo', '1.5', '0', '-1'])(
      'returns null for invalid dashboard id value "%s"',
      (invalidValue) => {
        getSearchParamMock.mockReturnValue(invalidValue);
        MatomoUrlMock.urlParsed.value = {};
        MatomoUrlMock.hashParsed.value = {};
        const wrapper = mountComponent();

        expect((wrapper.vm as any).getCurrentDashboardId()).toBeNull();
      },
    );
  });

  describe('#normalizeDashboardId()', () => {
    it.each([
      ['1', 1],
      [1, 1],
      ['001', null],
      [' 7 ', 7],
      [['8'], 8],
      [[], null],
      ['1.5', null],
      ['0', null],
      ['-3', null],
      ['foo', null],
      [null, null],
      [undefined, null],
    ])('normalizes %p to %p', (input, expected) => {
      const wrapper = mountComponent();

      expect((wrapper.vm as any).normalizeDashboardId(input)).toBe(expected);
    });
  });

  describe('#onClickExportDashboard()', () => {
    it('redirects authenticated users to create scheduled report with fallback query idDashboard', () => {
      MatomoMock.userLogin = 'admin';
      getSearchParamMock.mockReturnValue('');
      MatomoUrlMock.urlParsed.value = { idDashboard: '11' };
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;

      const redirectToCreateScheduledReportsSpy = jest.spyOn(vm, 'redirectToCreateScheduledReports');

      vm.onClickExportDashboard();

      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledTimes(1);
      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledWith(11);
    });

    it('redirects authenticated users to create scheduled report with fallback hash idDashboard', () => {
      MatomoMock.userLogin = 'admin';
      getSearchParamMock.mockReturnValue('');
      MatomoUrlMock.urlParsed.value = {};
      MatomoUrlMock.hashParsed.value = { idDashboard: '17' };
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;

      const redirectToCreateScheduledReportsSpy = jest.spyOn(vm, 'redirectToCreateScheduledReports');

      vm.onClickExportDashboard();

      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledTimes(1);
      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledWith(17);
    });

    it('redirects authenticated users to create scheduled report with null when all ids are invalid', () => {
      MatomoMock.userLogin = 'admin';
      getSearchParamMock.mockReturnValue('foo');
      MatomoUrlMock.urlParsed.value = { idDashboard: '0' };
      MatomoUrlMock.hashParsed.value = { idDashboard: 'x' };
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;

      const redirectToCreateScheduledReportsSpy = jest.spyOn(vm, 'redirectToCreateScheduledReports');

      vm.onClickExportDashboard();

      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledTimes(1);
      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledWith(null);
    });
  });

  describe('#getCurrentDashboardId() legacy invalid subcategory', () => {
    it('returns null when subcategory and fallbacks are not numeric', () => {
      getSearchParamMock.mockReturnValue('foo');
      MatomoUrlMock.urlParsed.value = {};
      MatomoUrlMock.hashParsed.value = {};
      const wrapper = mountComponent();

      expect((wrapper.vm as any).getCurrentDashboardId()).toBeNull();
    });
  });

  describe('#redirectToCreateScheduledReports()', () => {
    it('removes dashboard-specific params and redirects to ScheduledReports with dashboard id', () => {
      MatomoUrlMock.urlParsed.value = {
        module: 'Dashboard',
        action: 'embeddedIndex',
        category: 'General_Dashboard',
        subcategory: '12',
        idDashboard: '12',
        idSite: '1',
      };
      MatomoUrlMock.hashParsed.value = {
        category: 'General_Dashboard',
        subcategory: '12',
        idDashboard: '12',
        period: 'day',
        date: 'today',
      };

      const wrapper = mountComponent();
      (wrapper.vm as any).redirectToCreateScheduledReports(7);

      expect(updateUrlMock).toHaveBeenCalledTimes(1);
      expect(updateUrlMock).toHaveBeenCalledWith(
        {
          module: 'ScheduledReports',
          action: 'index',
          idSite: '1',
          idDashboard: 7,
        },
        {
          period: 'day',
          date: 'today',
        },
      );
    });

    it('does not include idDashboard when no dashboard id is provided', () => {
      MatomoUrlMock.urlParsed.value = {
        module: 'Dashboard',
        subcategory: '12',
        idSite: '1',
      };
      MatomoUrlMock.hashParsed.value = {
        period: 'week',
      };

      const wrapper = mountComponent();
      (wrapper.vm as any).redirectToCreateScheduledReports(null);

      expect(updateUrlMock).toHaveBeenCalledTimes(1);
      expect(updateUrlMock).toHaveBeenCalledWith(
        {
          module: 'ScheduledReports',
          action: 'index',
          idSite: '1',
        },
        {
          period: 'week',
        },
      );
    });
  });

  describe('#redirectToLoginPage()', () => {
    it('uses Matomo.getLoginModule for redirect', () => {
      getLoginModuleMock.mockReturnValue('CustomLogin');

      const wrapper = mountComponent();
      (wrapper.vm as any).redirectToLoginPage();

      expect(getLoginModuleMock).toHaveBeenCalledTimes(1);
      expect(updateUrlMock).toHaveBeenCalledTimes(1);
      expect(updateUrlMock).toHaveBeenCalledWith({ module: 'CustomLogin' });
    });
  });

  describe('#onClickExportDashboard() existing behavior', () => {
    it('redirects authenticated users to create scheduled report with dashboard id', () => {
      MatomoMock.userLogin = 'admin';
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;

      const redirectToCreateScheduledReportsSpy = jest.spyOn(vm, 'redirectToCreateScheduledReports');
      const redirectToLoginPageSpy = jest.spyOn(vm, 'redirectToLoginPage');
      jest.spyOn(vm, 'getCurrentDashboardId').mockReturnValue(3);

      vm.onClickExportDashboard();

      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledTimes(1);
      expect(redirectToCreateScheduledReportsSpy).toHaveBeenCalledWith(3);
      expect(redirectToLoginPageSpy).not.toHaveBeenCalled();
    });

    it('redirects anonymous users to login page', () => {
      MatomoMock.userLogin = 'anonymous';
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;

      const redirectToCreateScheduledReportsSpy = jest.spyOn(vm, 'redirectToCreateScheduledReports');
      const redirectToLoginPageSpy = jest.spyOn(vm, 'redirectToLoginPage');

      vm.onClickExportDashboard();

      expect(redirectToCreateScheduledReportsSpy).not.toHaveBeenCalled();
      expect(redirectToLoginPageSpy).toHaveBeenCalledTimes(1);
    });
  });

  describe('export menu click', () => {
    it('triggers export navigation method when export menu item is clicked', async () => {
      MatomoMock.userLogin = 'admin';
      const wrapper = mountComponent();
      const vm = wrapper.vm as any;
      const onClickExportDashboardSpy = jest.spyOn(vm, 'onClickExportDashboard');

      await wrapper.find('.exportDashboard').trigger('click');

      expect(onClickExportDashboardSpy).toHaveBeenCalledTimes(1);
    });
  });
});
