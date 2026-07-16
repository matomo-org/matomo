/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { mount } from '@vue/test-utils';
import ReportExportPopover from './ReportExportPopover.vue';
import Matomo from '../Matomo/Matomo';
import MatomoUrl from '../MatomoUrl/MatomoUrl';

jest.mock('../translate', () => ({
  translate: (key: string) => key,
}));

jest.mock('../Matomo/Matomo', () => ({
  __esModule: true,
  default: {
    config: {
      datatable_export_range_as_day: '',
    },
    language: 'en',
    token_auth: 'test-token',
    postEvent: jest.fn(),
  },
}));

jest.mock('../MatomoUrl/MatomoUrl', () => ({
  __esModule: true,
  default: {
    stringify: jest.fn(() => 'encoded=params'),
  },
}));

jest.mock('../SelectOnFocus/SelectOnFocus', () => ({
  __esModule: true,
  default: {},
}));

jest.mock('../useExternalPluginComponent', () => ({
  __esModule: true,
  default: () => ({
    name: 'FieldStub',
    props: {
      name: String,
      modelValue: [String, Number, Boolean],
    },
    template: '<div class="field-stub" :data-name="name"></div>',
  }),
}));

function createWrapper(extraProps: Record<string, unknown> = {}) {
  const globalProperties = {
    translate: (id: string, ...values: string[]|string[][]) => {
      if (!values.length) {
        return id;
      }
      return `${id} ${values.flat().join(' ')}`.trim();
    },
    $sanitize: (value: string) => value,
  };

  return mount(ReportExportPopover, {
    props: {
      hasSubtables: true,
      availableReportTypes: {
        default: 'Standard',
        processed: 'Processed',
      },
      availableReportFormats: {
        default: {
          CSV: 'CSV',
          TSV: 'TSV',
          XML: 'XML',
        },
        processed: {
          JSON: 'JSON',
          XML: 'XML',
        },
      },
      limitAllOptions: {
        yes: 'All',
        no: 'Custom',
      },
      maxFilterLimit: 0,
      dataTable: {
        param: {
          idSite: 1,
          date: '2025-01-01',
          period: 'day',
          viewDataTable: 'table',
        },
        props: {},
        getReportMetadata: () => ({ dimensions: {} }),
      },
      requestParams: {},
      apiMethod: 'Actions.getPageUrls',
      ...extraProps,
    },
    global: {
      config: {
        globalProperties: globalProperties as any,
      },
    },
  });
}

describe('CoreHome/ReportExportPopover', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('should generate processed export URL params and post the mounted event', () => {
    const wrapper = createWrapper({
      initialReportType: 'processed',
      initialReportFormat: 'JSON',
    });

    const exportLink = wrapper.vm.exportLink as string;
    const stringifyMock = MatomoUrl.stringify as jest.Mock;
    const calls = stringifyMock.mock.calls.map((call) => call[0]);
    const processedCall = calls.find(
      (params) => params.method === 'API.getProcessedReport' && params.force_api_session === 1,
    );

    expect(exportLink).toContain('encoded=params');
    expect(processedCall).toBeDefined();
    expect(processedCall.apiModule).toBe('Actions');
    expect(processedCall.apiAction).toBe('getPageUrls');
    expect(processedCall.token_auth).toBe('test-token');
    expect(processedCall.force_api_session).toBe(1);
    expect((Matomo as any).postEvent).toHaveBeenCalledWith(
      'ReportExportPopover.additionalContent',
      expect.any(Object),
    );
  });

  it('should generate export URL without token for display', () => {
    const wrapper = createWrapper({
      initialReportType: 'default',
      initialReportFormat: 'CSV',
    });

    const exportLinkWithoutToken = wrapper.vm.exportLinkWithoutToken as string;
    const stringifyMock = MatomoUrl.stringify as jest.Mock;
    const calls = stringifyMock.mock.calls.map((call) => call[0]);
    const withoutTokenCall = calls.find(
      (params) => params.token_auth === 'ENTER_YOUR_TOKEN_AUTH_HERE',
    );

    expect(exportLinkWithoutToken).toContain('encoded=params');
    expect(withoutTokenCall).toBeDefined();
    expect(withoutTokenCall.method).toBe('Actions.getPageUrls');
    expect(withoutTokenCall.force_api_session).toBeUndefined();
    expect(withoutTokenCall.translateColumnNames).toBe(1);
    expect(withoutTokenCall.language).toBe('en');
  });

  it('should default to JSON format when report with metadata is selected', async () => {
    const wrapper = createWrapper({
      initialReportType: 'default',
      initialReportFormat: 'CSV',
    });

    expect((wrapper.vm as any).reportFormat).toBe('CSV');

    await wrapper.setData({ reportType: 'processed' });

    expect((wrapper.vm as any).reportFormat).toBe('JSON');
  });

  it('should keep flat export available without visible subtables when the report supports flattening', () => {
    const wrapper = createWrapper({
      hasSubtables: false,
      canExportFlat: true,
      initialReportFormat: 'TSV',
      initialOptionFlat: true,
    });

    expect((wrapper.vm as any).effectiveSubtableOptions).toEqual({
      optionFlat: true,
      optionExpanded: false,
    });

    const exportLink = wrapper.vm.exportLink as string;
    expect(exportLink).toContain('encoded=params');

    const stringifyMock = MatomoUrl.stringify as jest.Mock;
    const calls = stringifyMock.mock.calls.map((call) => call[0]);
    const exportCall = calls.find(
      (params) => params.method === 'Actions.getPageUrls' && params.format === 'TSV',
    );

    expect(exportCall.flat).toBe(1);
    expect(exportCall.expanded).toBeUndefined();
  });

  it('should include requestParams label in graph evolution export links', () => {
    createWrapper({
      initialReportType: 'default',
      initialReportFormat: 'CSV',
      dataTable: {
        param: {
          idSite: 1,
          date: '2025-01-01,2025-01-31',
          period: 'range',
          viewDataTable: 'graphEvolution',
        },
        props: {},
        getReportMetadata: () => ({ dimensions: {} }),
      },
      requestParams: {
        label: '@referrer.com',
      },
      apiMethod: 'Referrers.getWebsites',
    });

    const stringifyMock = MatomoUrl.stringify as jest.Mock;
    const calls = stringifyMock.mock.calls.map((call) => call[0]);
    const exportCall = calls.find(
      (params) => params.method === 'Referrers.getWebsites' && params.format === 'CSV',
    );

    expect(exportCall).toBeDefined();
    expect(exportCall.period).toBe('day');
    expect(exportCall.label).toBe('@referrer.com');
  });
});
