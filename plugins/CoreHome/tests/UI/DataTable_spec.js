/*!
 * Matomo - free/libre analytics platform
 *
 * UI regression for DataTable sorting & AJAX reloading.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('DataTable', function () {
  this.optionsOverride = {
    'persist-fixture-data': false
  };

  const devicesUrl = "?module=CoreHome&action=index&idSite=3&period=day&date=yesterday&category=General_Visitors&subcategory=DevicesDetection_Devices";
  const emptyDevicesUrl = "?module=CoreHome&action=index&idSite=3&period=day&date=2011-01-01&category=General_Visitors&subcategory=DevicesDetection_Devices";
  const widgetSelector = '#widgetDevicesDetectiongetType';
  const selectors = {
    metricColumn: `${widgetSelector} #nb_uniq_visitors`,
    limitDropdownTrigger: `${widgetSelector} .limitSelection .select-wrapper input.select-dropdown`,
    limitDropdownMenu: `${widgetSelector} .limitSelection .select-wrapper ul.select-dropdown`,
    visualizationTrigger: `${widgetSelector} .dataTableHeaderControls .dataTableControls a.activateVisualizationSelection`,
    visualizationTriggerIcon: `${widgetSelector} .dataTableHeaderControls .dataTableControls a.activateVisualizationSelection > span, ${widgetSelector} .dataTableHeaderControls .dataTableControls a.activateVisualizationSelection > img`,
    visualizationMenu: `${widgetSelector} .dataTableHeaderControls .dataTableControls ul.dropdown-content.dataTableFooterIcons`,
    visualizationButtons: `${widgetSelector} .dataTableHeaderControls .dataTableControls ul.dropdown-content.dataTableFooterIcons li .tableIcon[data-footer-icon-id]`,
    configureTrigger: `${widgetSelector} .dataTableHeaderControls .dataTableControls a.dropdownConfigureIcon`,
    totalsRowToggle: '.dataTableShowTotalsRow',
  };

  async function openDevicesDetectionWidget() {
    await page.goto(devicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(widgetSelector, { visible: true });
    await page.waitForNetworkIdle();
  }

  async function loadWidget() {
    await openDevicesDetectionWidget();
    const widget = await page.$(widgetSelector);
    expect(widget).to.be.ok;
    return widget;
  }

  async function trackViewDataTableRequests(action) {
    let ajaxRequestCount = 0;
    const requestHandler = (request) => {
      if (request.resourceType() === 'xhr' && request.url().indexOf('saveViewDataTableParameters') !== -1) {
        ajaxRequestCount += 1;
      }
    };
    page.webpage.on('request', requestHandler);
    try {
      await action();
    } finally {
      page.webpage.off('request', requestHandler);
    }
    return ajaxRequestCount;
  }

  async function interactWithColumnSortingAndLimit() {
    await page.waitForSelector(selectors.metricColumn, { visible: true });
    await page.click(selectors.metricColumn);
    await page.waitForNetworkIdle();
    await page.click(selectors.metricColumn);
    await page.waitForNetworkIdle();

    await page.waitForSelector(selectors.limitDropdownTrigger, { visible: true });
    await page.click(selectors.limitDropdownTrigger);

    await page.waitForSelector(selectors.limitDropdownMenu, { visible: true });
    const dropdown = await page.$(selectors.limitDropdownMenu);
    expect(dropdown).to.be.ok;
    await page.click(`${selectors.limitDropdownMenu} li:nth-child(1)`);
    await page.waitForNetworkIdle();
  }

  async function changeVisualization() {
    await page.waitForSelector(selectors.visualizationTriggerIcon, { visible: true });
    await page.click(selectors.visualizationTriggerIcon);
    await page.waitForSelector(selectors.visualizationMenu, { visible: true });

    await page.waitForFunction((selector) => document.querySelectorAll(selector).length >= 2, {}, selectors.visualizationButtons);
    await page.$$eval(selectors.visualizationButtons, (buttons) => {
      if (buttons.length < 2) {
        throw new Error('Not enough visualization buttons found');
      }
      buttons[1].click();
    });
    await page.waitForNetworkIdle();
  }

  async function toggleTotalsRow(times = 1) {
    await page.waitForSelector(selectors.configureTrigger, { visible: true });
    for (let i = 0; i < times; i += 1) {
      await page.click(selectors.configureTrigger);
      await page.waitForSelector(selectors.totalsRowToggle, { visible: true });
      await page.click(selectors.totalsRowToggle);
      await page.waitForNetworkIdle();
    }
  }

  // The action row is rendered inside .dataTable so the datatable javascript can bind to it, and
  // is then moved into the report header (see dataTable.js adoptTableActionsIntoReportHeader).
  // .dataTable is replaced wholesale on every reload while the header persists, so both the
  // single-row invariant and the id stamp linking them have to survive each interaction.
  async function readHeaderLayout() {
    return page.evaluate((widgetSel) => {
      const widget = document.querySelector(widgetSel);
      const dataTable = widget.querySelector('.dataTable');
      const adopted = widget.querySelectorAll('.reportHeader__actions .dataTableHeaderControls');

      return {
        headers: widget.querySelectorAll('.reportHeader').length,
        headingTag: widget.querySelector('.reportHeader__title').tagName,
        title: widget.querySelector('.reportHeader__title').innerText.trim(),
        actionRows: widget.querySelectorAll('.dataTableHeaderControls').length,
        adoptedActionRows: adopted.length,
        actionRowsLeftInTable: dataTable.querySelectorAll('.dataTableHeaderControls').length,
        footerActionRows: dataTable.querySelectorAll('.dataTableFooterNavigation .dataTableControls').length,
        stampedId: adopted.length ? adopted[0].getAttribute('data-datatable-id') : null,
        dataTableId: dataTable.getAttribute('id'),
        widgetControls: widget.querySelectorAll('.reportHeader .widgetControls__action').length,
      };
    }, widgetSelector);
  }

  function expectExactlyOneAdoptedActionRow(layout) {
    expect(layout.headers).to.be.equal(1);
    expect(layout.actionRows).to.be.equal(1);
    expect(layout.adoptedActionRows).to.be.equal(1);
    expect(layout.actionRowsLeftInTable).to.be.equal(0);
    expect(layout.footerActionRows).to.be.equal(0);
    expect(layout.stampedId).to.be.equal(layout.dataTableId);
  }

  it('should render the report header once, with the action icons moved into it', async function () {
    await loadWidget();

    const layout = await readHeaderLayout();
    expectExactlyOneAdoptedActionRow(layout);
    // a full-page report owns the page's report heading level
    expect(layout.headingTag).to.be.equal('H2');
    // and shows no widget controls
    expect(layout.widgetControls).to.be.equal(0);
    // the widget name from the metadata still wins over the report's own title
    expect(layout.title).to.be.equal('Device type');
  });

  it('should render the header of a report without data, leaving its hidden actions in the table', async function () {
    await page.goto(emptyDevicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(`${widgetSelector} .dataTable.isDataTableEmpty`, { visible: true });
    await page.waitForNetworkIdle();

    const layout = await readHeaderLayout();

    // the title still renders...
    expect(layout.headers).to.be.equal(1);
    expect(layout.title).to.be.equal('Device type');
    // ...but the actions stay inside the datatable, where the
    // `.dataTable.isDataTableEmpty:not(.hasSearchKeyword)` rule keeps hiding them, so the
    // header's anchor stays empty and collapses
    expect(layout.adoptedActionRows).to.be.equal(0);
    expect(layout.actionRowsLeftInTable).to.be.equal(1);
    expect(await page.evaluate((sel) => {
      const anchor = document.querySelector(`${sel} .reportHeader__actions`);
      return window.getComputedStyle(anchor).display;
    }, widgetSelector)).to.be.equal('none');
  });

  it('should keep a single header and action row across sorting, limit and visualization changes', async function () {
    await loadWidget();

    await interactWithColumnSortingAndLimit();
    expectExactlyOneAdoptedActionRow(await readHeaderLayout());

    await changeVisualization();
    expectExactlyOneAdoptedActionRow(await readHeaderLayout());

    await toggleTotalsRow();
    expectExactlyOneAdoptedActionRow(await readHeaderLayout());
  });

  it('should allow saving of preference for normal user when changing sorting and table limits', async function () {
    await loadWidget();
    const reportPage = await page.$('.reporting-page');
    expect(reportPage).to.be.ok;

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await interactWithColumnSortingAndLimit();
    });

    expect(ajaxRequestCount).to.be.equal(3);
  });

  it('should allow saving preference when changing the visualization of the devices widget', async function () {
    await loadWidget();

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await changeVisualization();
    });

    expect(ajaxRequestCount).to.be.equal(1);
  });

  it('should allow saving preference when toggling the totals row via the configuration menu', async function () {
    await loadWidget();

    const ajaxRequestCount = await trackViewDataTableRequests(async () => {
      await toggleTotalsRow(2);
    });

    expect(ajaxRequestCount).to.be.equal(2);
  });

  describe('As anonymous user', function () {
    before(async function () {
      await testEnvironment.callApi('UsersManager.setUserAccess', {
        userLogin: 'anonymous',
        access: 'view',
        idSites: [3],
      });
      testEnvironment.testUseMockAuth = 0;
      await testEnvironment.save();
    });

    after(async function () {
      testEnvironment.testUseMockAuth = 1;
      await testEnvironment.save();
    });

    it('should not save preferences when anonymous user performs datatable actions', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await interactWithColumnSortingAndLimit();
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });

    it('should not save preferences when anonymous user changes visualization', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await changeVisualization();
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });

    it('should not save preferences when anonymous user toggles totals row', async function () {
      await loadWidget();

      const ajaxRequestCount = await trackViewDataTableRequests(async () => {
        await toggleTotalsRow(2);
      });

      expect(ajaxRequestCount).to.be.equal(0);
    });
  });
});
