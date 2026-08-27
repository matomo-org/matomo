/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { mount } from '@vue/test-utils';
import DataTableActions from './DataTableActions.vue';

function translateStub(key: string, ...args: string[]) {
  const messages: Record<string, string> = {
    CoreHome_ShowPercentageValuesDataTable: 'The report is showing absolute values %s Show percentages',
    CoreHome_ShowAbsoluteValuesDataTable: 'The report is showing percentages %s Show absolute values',
    CoreHome_ShowPercentageValues: 'Show percentages',
    CoreHome_ShowAbsoluteValues: 'Show absolute values',
    CoreHome_Default: 'default',
  };

  const message = messages[key] || key;

  // mirror the real helper: with no replacement values the raw message is returned, placeholders
  // intact. getSingleStateIconText relies on that to detect whether a message has an action half.
  if (!args.length) {
    return message;
  }

  const values = [...args];

  return message.replace(/%(\d\$)?s/g, () => values.shift() || '');
}

vi.mock('../translate', () => ({ translate: (...args: string[]) => translateStub(...(args as [string])) }));

vi.mock('../DropdownButton/DropdownButton', () => ({ default: {} }));
vi.mock('../ReportExport/ReportExport', () => ({ default: {} }));

describe('DataTableActions percentage values setting', () => {
  const percentageItem = '.configItem.dataTableShowPercentageValues';

  function mountComponent(customProps = {}) {
    return mount(DataTableActions, {
      props: {
        showFooter: true,
        showFooterIcons: true,
        reportSupportsPercentageValues: true,
        viewDataTable: 'table',
        placement: 'header',
        footerIcons: [],
        requestParams: {},
        apiMethodToRequestDataTable: 'DevicesDetection.getType',
        maxFilterLimit: 100,
        reportId: 'DevicesDetection.getType',
        dataTableActions: [],
        clientSideParameters: {},
        translations: {},
        ...customProps,
      },
      global: {
        // the template calls `translate` as a global property, not the imported helper
        config: {
          globalProperties: {
            translate: translateStub,
            $sanitize: (value: string) => value,
          } as any,
        },
      },
    });
  }

  it('should not offer the setting when the report has no percentage values', () => {
    const wrapper = mountComponent({ reportSupportsPercentageValues: false });

    expect(wrapper.find(percentageItem).exists()).toBe(false);
    // with no candidate item left the config list renders nothing at all
    expect(wrapper.find('ul.tableConfiguration li').exists()).toBe(false);
  });

  it('should not offer the setting on an empty table, like the totals row item', () => {
    const wrapper = mountComponent({ isDataTableEmpty: true });

    expect(wrapper.find(percentageItem).exists()).toBe(false);
  });

  it('should offer the setting, unhighlighted, when the report shows absolute values', () => {
    const wrapper = mountComponent({ clientSideParameters: {} });

    const item = wrapper.find(percentageItem);
    expect(item.exists()).toBe(true);

    expect(item.text()).toContain('The report is showing absolute values');
    // the offered action is rendered as the `.action` half of the item
    expect(item.find('span.action').text()).toBe('Show percentages');
    expect(item.text()).not.toContain('default');

    // the action, not the current state, is the accessible name
    expect(item.attributes('aria-label')).toBe('Show percentages');
  });

  it('should flip the wording, the accessible name and the icon state when percentages are shown', () => {
    const wrapper = mountComponent({ clientSideParameters: { show_percentage_values: '1' } });

    const item = wrapper.find(percentageItem);
    expect(item.text()).toContain('The report is showing percentages');
    // switching back returns the report to its default, as for the other toggles
    expect(item.find('span.action').text()).toBe('Show absolute values (default)');

    expect(item.attributes('aria-label')).toBe('Show absolute values');
  });

  it('should treat a disabled setting the same however it is expressed', () => {
    ['0', 0, false, ''].forEach((value) => {
      const wrapper = mountComponent({ clientSideParameters: { show_percentage_values: value } });

      expect(wrapper.find(percentageItem).attributes('aria-label')).toBe('Show percentages');
    });
  });
});

// Locks what the bar renders before the actions move up into the report header, so that move can
// be reviewed as a relocation rather than a rewrite.
describe('DataTableActions rendered actions', () => {
  function mountComponent(customProps = {}) {
    return mount(DataTableActions, {
      props: {
        showFooter: true,
        showFooterIcons: true,
        viewDataTable: 'table',
        placement: 'header',
        footerIcons: [],
        requestParams: {},
        apiMethodToRequestDataTable: 'DevicesDetection.getType',
        maxFilterLimit: 100,
        reportId: 'DevicesDetection.getType',
        dataTableActions: [],
        clientSideParameters: {},
        translations: {},
        ...customProps,
      },
      global: {
        config: {
          globalProperties: {
            translate: translateStub,
            $sanitize: (value: string) => value,
          } as any,
        },
      },
    });
  }

  // The next two lock the contract the move into the header must preserve, not the move itself:
  // they pass against the pre-move component too. Third-party plugins reach their own actions
  // through `a.dataTableAction.<id>`, so that selector holding still is what keeps them working.
  it('should render each action only when its own flag is set', () => {
    const off = mountComponent();
    expect(off.find('a.activateExportSelection').exists()).toBe(false);
    expect(off.find('a.dataTableAction.tableIcon').exists()).toBe(false);

    const on = mountComponent({
      showExport: true,
      showExportAsImageIcon: true,
    });
    expect(on.find('a.activateExportSelection').exists()).toBe(true);
    expect(on.find('a.dataTableAction.tableIcon').exists()).toBe(true);

    // annotations is a menu entry now, so it needs its flag and it is gone from the footer
    expect(on.find('a.annotationView').exists()).toBe(false);
    expect(mountComponent({ showAnnotations: true })
      .find('a.annotationView').exists()).toBe(true);
    expect(mountComponent({ showAnnotations: true, placement: 'footer' })
      .find('a.annotationView').exists()).toBe(false);
  });

  it('should render the extra report actions it is given', () => {
    const wrapper = mountComponent({
      dataTableActions: [
        { id: 'myCustomAction', title: 'Custom', icon: 'icon-add' },
      ],
    });

    expect(wrapper.find('a.dataTableAction.myCustomAction').exists()).toBe(true);
  });

  // The id is still scoped to the placement even though only the header renders this action
  // today: a second placement would otherwise silently duplicate the id in the document again.
  it('should scope the export-as-image id to its placement', () => {
    const header = mountComponent({ showExportAsImageIcon: true });

    expect(header.find('a.dataTableAction.tableIcon').attributes('id'))
      .toBe('dataTableExportAsImageIcon-header');
  });
});

describe('DataTableActions period submenu', () => {
  function mountWithPeriods(customProps = {}) {
    return mount(DataTableActions, {
      props: {
        showFooter: true,
        showFooterIcons: true,
        viewDataTable: 'graphEvolution',
        placement: 'header',
        footerIcons: [],
        requestParams: {},
        apiMethodToRequestDataTable: 'VisitsSummary.get',
        maxFilterLimit: 100,
        reportId: 'VisitsSummary.get',
        dataTableActions: [],
        showPeriods: true,
        selectablePeriods: ['day', 'week'],
        clientSideParameters: { period: 'day' },
        translations: {},
        ...customProps,
      },
      global: {
        config: {
          globalProperties: {
            translate: translateStub,
            $sanitize: (value: string) => value,
          } as any,
        },
      },
    });
  }

  it('should open on a click and fold once a period is picked', async () => {
    const wrapper = mountWithPeriods();
    expect(wrapper.find('.mtm-dropdownPanel__submenu--open').exists()).toBe(false);

    await wrapper.find('a.activatePeriodsSelection').trigger('click');
    expect(wrapper.find('.mtm-dropdownPanel__submenu--open').exists()).toBe(true);

    await wrapper.find('.dataTablePeriods [role="menuitem"]').trigger('click');
    expect(wrapper.find('.mtm-dropdownPanel__submenu--open').exists()).toBe(false);
  });

  // The entries are not links, so a key press has to produce the click dataTable.js listens for.
  it('should act on a period the keyboard activates', async () => {
    const wrapper = mountWithPeriods();
    await wrapper.find('a.activatePeriodsSelection').trigger('click');

    const item = wrapper.find('.dataTablePeriods [role="menuitem"]');
    const clicked = vi.fn();
    item.element.addEventListener('click', clicked);

    await item.trigger('keydown.enter');
    expect(clicked).toHaveBeenCalled();
  });

  it('should name the annotations entry after what the click will do', () => {
    expect(mountWithPeriods({ showAnnotations: true }).find('a.annotationView').text())
      .toContain('Annotations_ShowAnnotations');
    expect(mountWithPeriods({ showAnnotations: true, annotationsShowing: true })
      .find('a.annotationView').text()).toContain('Annotations_HideAnnotations');
  });
});
