/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';

type PlainObject = Record<string, unknown>;

const mockTranslate = (key: string, ...values: Array<string|number>) => {
  const translations: Record<string, string> = {
    ScheduledReports_CreateTooltip: 'Create tooltip',
    General_Website: 'Website',
    General_Description: 'Description',
    ScheduledReports_DescriptionOnReportAndReportsList: 'Description help',
    ScheduledReports_ReportMissingDescription: 'To create a report, please include a %1$sDescription%2$s.',
    ScheduledReports_ReportsIncluded: 'Select the reports to include',
    ScheduledReports_ReportsIncludedHelp: 'Choose at least one report to include in this scheduled report.',
    ScheduledReports_CreateAndScheduleReport: 'Create and Schedule a report',
    General_OrCancel: 'or %s Cancel %s',
    CoreHome_LearnMoreFullStop: '%1$sLearn more.%2$s',
  };

  const template = translations[key] || key;
  return values.reduce(
    (result: string, value, index) => result
      .replace(`%${index + 1}$s`, String(value))
      .replace('%s', String(value)),
    template as string,
  );
};

const mockMatomo = {
  helper: {
    htmlDecode: (value: string) => value,
    compileVueEntryComponents: jest.fn(),
    destroyVueComponent: jest.fn(),
  },
  timezoneOffset: 0,
};

const mockExternalLink = jest.fn((url: string) => `<a href="${url}">`);

jest.mock('CoreHome', () => ({
  ContentBlock: {
    template: '<div><slot /></div>',
    props: ['contentTitle'],
  },
  Matomo: mockMatomo,
  MatomoUrl: {
    stringify: () => 'module=CoreHome',
    urlParsed: { value: {} },
  },
  translate: mockTranslate,
  debounce: (fn: unknown) => fn,
  externalLink: mockExternalLink,
}), { virtual: true });

jest.mock('CorePluginsAdmin', () => ({
  Field: {
    template: '<div class="field-stub"><div v-if="errorMessage" class="form-group__error-message">{{ errorMessage }}</div><slot /><slot name="inline-help" /></div>',
    props: ['title', 'modelValue', 'uiControlAttributes', 'inlineHelp', 'errorMessage'],
  },
  Form: {},
  SaveButton: {
    template: '<button type="button"><slot /></button>',
    props: ['value'],
  },
}), { virtual: true });

// eslint-disable-next-line @typescript-eslint/no-var-requires
const AddReport = require('./AddReport.vue').default;

const defaultProps = {
  report: {
    idreport: 0,
    description: '',
    idsegment: '',
    type: 'email',
    formatemail: 'pdf',
    displayFormat: 1,
    evolutionGraph: false,
    evolutionPeriodFor: 'prev',
    evolutionPeriodN: 5,
    period: 'day',
    periodParam: 'day',
    hour: '12',
    parameters: {},
  },
  selectedReports: {},
  selectedReportsOrder: {},
  paramPeriods: {},
  reportTypeOptions: [{ key: 'email', value: 'Email' }],
  reportFormatsByReportTypeOptions: { email: [] },
  displayFormats: [],
  reportsByCategoryByReportType: { email: {} },
  allowMultipleReportsByReportType: { email: true },
  countWebsites: 1,
  siteName: 'Test Site',
  reportTypes: [{ key: 'email', value: 'Email' }],
  segmentEditorActivated: false,
  savedSegmentsById: {},
  periods: {},
  validationErrors: {
    description: false,
    reports: false,
  },
};

describe('ScheduledReports/AddReport', () => {
  beforeAll(() => {
    const testWindow = window as unknown as {
      $: (value: unknown) => PlainObject;
      ReportPlugin: PlainObject;
    };

    testWindow.$ = () => ({
      data: () => ({ report_: null }),
    });
    testWindow.ReportPlugin = {
      updateReportString: 'Update Report',
      createReportString: 'Create Report',
      periodTranslations: {
        day: {
          single: 'day',
          plural: 'days',
        },
      },
    };
  });

  beforeEach(() => {
    jest.clearAllMocks();
  });

  function mountComponent(overrideProps: PlainObject = {}) {
    return mount(AddReport as PlainObject, {
      props: {
        ...defaultProps,
        ...overrideProps,
      },
      global: {
        mocks: {
          translate: mockTranslate,
          $sanitize: (value: string) => value,
        },
        stubs: {
          SelectedReportsList: {
            template: '<div class="selected-reports-list-stub" />',
            props: ['reports', 'enabled'],
          },
        },
      },
    });
  }

  it('renders inline description validation without link placeholders', () => {
    const wrapper = mountComponent({
      validationErrors: {
        description: true,
        reports: false,
      },
    });

    expect(wrapper.text()).toContain('To create a report, please include a Description.');
    expect(wrapper.text()).not.toContain('%1$s');
    expect(wrapper.text()).not.toContain('%2$s');
  });

  it('applies the error class to the existing reports help text', () => {
    const wrapper = mountComponent({
      validationErrors: {
        description: false,
        reports: true,
      },
    });

    const reportsHelp = wrapper.find('#scheduled-reports-selection-heading + div');

    expect(reportsHelp.exists()).toBe(true);
    expect(reportsHelp.classes()).toContain('scheduled-reports-field-help');
    expect(reportsHelp.classes()).toContain('form-group__error-message');
    expect(reportsHelp.text()).toBe('Choose at least one report to include in this scheduled report.');
  });
});
