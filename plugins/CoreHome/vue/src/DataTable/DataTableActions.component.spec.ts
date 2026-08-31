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
    CoreHome_ShowPercentageValues: 'Show percentages',
    CoreHome_MakeItFlat: 'Make it flat',
    CoreHome_PivotBy: 'Pivot by %s',
  };

  const message = messages[key] || key;

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

    // only the action is rendered - the state the report is in is left out of the label
    expect(item.text()).toBe('Show percentages');
    expect(item.text()).not.toContain('The report is showing');
    expect(item.text()).not.toContain('default');

    // the label is the accessible name; the state is announced separately
    expect(item.attributes('role')).toBe('menuitemcheckbox');
    expect(item.attributes('aria-checked')).toBe('false');
  });

  it('should keep its wording and mark itself instead when percentages are shown', () => {
    const wrapper = mountComponent({ clientSideParameters: { show_percentage_values: '1' } });

    const item = wrapper.find(percentageItem);
    // the label names the setting, not what a click would do next, so it does not move
    expect(item.text()).toBe('Show percentages');

    // what changed is the tick beside it - and, for anyone who cannot see it, aria-checked
    expect(item.find('.icon-ok').exists()).toBe(true);
    expect(item.attributes('aria-checked')).toBe('true');
  });

  it('should carry no tick while percentages are off', () => {
    const wrapper = mountComponent({ clientSideParameters: {} });

    expect(wrapper.find(percentageItem).find('.icon-ok').exists()).toBe(false);
  });

  it('should treat a disabled setting the same however it is expressed', () => {
    ['0', 0, false, ''].forEach((value) => {
      const wrapper = mountComponent({ clientSideParameters: { show_percentage_values: value } });

      expect(wrapper.find(percentageItem).attributes('aria-checked')).toBe('false');
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

    await wrapper.find('.dataTablePeriods [role="menuitemradio"]').trigger('click');
    expect(wrapper.find('.mtm-dropdownPanel__submenu--open').exists()).toBe(false);
  });

  // The entries are not links, so a key press has to produce the click dataTable.js listens for.
  it('should act on a period the keyboard activates', async () => {
    const wrapper = mountWithPeriods();
    await wrapper.find('a.activatePeriodsSelection').trigger('click');

    const item = wrapper.find('.dataTablePeriods [role="menuitemradio"]');
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

describe('DataTableActions menu structure', () => {
  const tableGroup = {
    class: 'tableAllColumnsSwitch',
    buttons: [{ id: 'table', icon: 'icon-table', title: 'Display simple table' }],
  };
  const insightsGroup = {
    class: 'tableInsightViews',
    buttons: [{ id: 'insightsVisualization', icon: 'icon-insights', title: 'Insights' }],
  };
  const graphGroup = {
    class: 'tableGraphViews',
    buttons: [{ id: 'graphVerticalBar', icon: 'icon-chart-bar', title: 'Vertical bar graph' }],
  };

  function mountComponent(customProps = {}) {
    return mount(DataTableActions, {
      props: {
        showFooter: true,
        showFooterIcons: true,
        viewDataTable: 'table',
        placement: 'header',
        footerIcons: [tableGroup],
        requestParams: {},
        apiMethodToRequestDataTable: 'DevicesDetection.getType',
        maxFilterLimit: 100,
        reportId: 'DevicesDetection.getType',
        dataTableActions: [],
        clientSideParameters: {},
        translations: {},
        ...customProps,
      },
    });
  }

  // The roles only mean anything inside a menu: outside one a screen reader may drop them, and
  // aria-checked with them.
  it('should own its entries as one menu of groups', () => {
    const wrapper = mountComponent({ reportSupportsPercentageValues: true });

    expect(wrapper.find('[role="menu"]').exists()).toBe(true);
    expect(wrapper.findAll('ul.mtm-dropdownPanel__menu').every(
      (list) => list.attributes('role') === 'group',
    )).toBe(true);
    expect(wrapper.findAll('li.mtm-dropdownPanel__menuItem').every(
      (item) => item.attributes('role') === 'none',
    )).toBe(true);
  });

  it('should mark the visualisation in use, exclusively', () => {
    const wrapper = mountComponent({
      footerIcons: [tableGroup, graphGroup],
      viewDataTable: 'graphVerticalBar',
    });

    const entries = wrapper.findAll('[role="menuitemradio"]');
    expect(entries.length).toBe(2);
    expect(entries.map((e) => e.attributes('aria-checked'))).toEqual(['false', 'true']);
    expect(entries[1].find('.icon-ok').exists()).toBe(true);
  });

  // No href here, so without this the role promises a control the keyboard cannot reach.
  it('should let the keyboard pick a visualisation', async () => {
    const wrapper = mountComponent();

    const entry = wrapper.find('[role="menuitemradio"]');
    expect(entry.attributes('tabindex')).toBe('0');

    const clicked = vi.fn();
    entry.element.addEventListener('click', clicked);
    await entry.trigger('keydown.space');

    expect(clicked).toHaveBeenCalled();
  });

  it('should drop a group whose buttons carry no icon', () => {
    const wrapper = mountComponent({
      reportSupportsPercentageValues: true,
      footerIcons: [tableGroup, { class: 'tableGraphViews', buttons: [{ id: 'noIcon' }] }],
    });

    expect(wrapper.findAll('li.mtm-dropdownPanel__menuItem .tableGraphViews').length).toBe(0);
    // and with it the separator it would have been given
    expect(wrapper.findAll('li.mtm-dropdownPanel__separator').length).toBe(1);
  });

  it('should rule off before Insights rather than after it', () => {
    const wrapper = mountComponent({
      reportSupportsPercentageValues: true,
      footerIcons: [tableGroup, insightsGroup, graphGroup],
    });

    const rows = wrapper.findAll('ul.dataTableFooterIcons > li');
    const separators = rows
      .map((row, index) => (row.classes('mtm-dropdownPanel__separator') ? index : -1))
      .filter((index) => index !== -1);

    // one above the list, one above Insights - and none between Insights and the graphs
    expect(separators).toEqual([0, 2]);
    expect(rows[4].find('.tableGraphViews').exists()).toBe(true);
  });

  // Announcing role="menu" promises that the arrows walk the entries, so they have to.
  it('should walk its entries with the arrow keys', async () => {
    const wrapper = mountComponent({
      reportSupportsPercentageValues: true,
      footerIcons: [tableGroup, graphGroup],
    });
    document.body.appendChild(wrapper.element);

    const menu = wrapper.find('[role="menu"]');
    const items = wrapper.findAll('[role^="menuitem"]').map((item) => item.element);
    expect(items.length).toBeGreaterThan(2);

    await menu.trigger('keydown', { key: 'ArrowDown' });
    expect(document.activeElement).toBe(items[0]);

    await menu.trigger('keydown', { key: 'ArrowDown' });
    expect(document.activeElement).toBe(items[1]);

    await menu.trigger('keydown', { key: 'ArrowUp' });
    expect(document.activeElement).toBe(items[0]);

    await menu.trigger('keydown', { key: 'End' });
    expect(document.activeElement).toBe(items[items.length - 1]);

    // and round, so the list has no dead end
    await menu.trigger('keydown', { key: 'ArrowDown' });
    expect(document.activeElement).toBe(items[0]);

    wrapper.unmount();
  });

  it('should close the menu with the exports, ruled off from the visualisations', () => {
    const wrapper = mountComponent({
      reportSupportsPercentageValues: true,
      showExport: true,
      showExportAsImageIcon: true,
    });

    const lists = wrapper.findAll('ul.mtm-dropdownPanel__menu');
    const exports = lists[lists.length - 1];
    expect(exports.find('a.activateExportSelection').exists()).toBe(true);
    expect(exports.find('a.dataTableAction.tableIcon').exists()).toBe(true);

    // its own rule, and the entries below it
    const rows = exports.findAll('li');
    expect(rows[0].classes()).toContain('mtm-dropdownPanel__separator');
    expect(rows.length).toBe(3);
  });

  it('should carry no rule when the exports open the menu on their own', () => {
    const wrapper = mountComponent({
      reportSupportsPercentageValues: false,
      footerIcons: [],
      showExport: true,
    });

    expect(wrapper.find('a.activateExportSelection').exists()).toBe(true);
    expect(wrapper.find('li.mtm-dropdownPanel__separator').exists()).toBe(false);
  });

  // showConfigItems only gates the list; every entry inside has a condition of its own.
  it('should not rule off above the visualisations when nothing renders above them', () => {
    const wrapper = mountComponent({ reportSupportsPercentageValues: false });

    expect(wrapper.find('ul.tableConfiguration').exists()).toBe(false);
    expect(wrapper.find('li.mtm-dropdownPanel__separator').exists()).toBe(false);
  });
});
