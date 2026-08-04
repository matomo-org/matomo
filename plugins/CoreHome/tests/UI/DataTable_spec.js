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

  async function readHeaderLayout() {
    return page.evaluate((widgetSel) => {
      const widget = document.querySelector(widgetSel);
      const dataTable = widget.querySelector('.dataTable');
      const title = widget.querySelector('.reportHeader__title');

      return {
        headers: widget.querySelectorAll('.reportHeader').length,
        headingTag: title.tagName,
        title: title.innerText.trim(),
        actionRowsInTable: dataTable.querySelectorAll('.dataTableHeaderControls').length,
        widgetControls: widget.querySelectorAll('.reportHeader .widgetControls__action').length,
      };
    }, widgetSelector);
  }

  it('should render the report header once, with no widget controls', async function () {
    await loadWidget();

    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.headingTag).to.be.equal('H2');
    // the widget name from the metadata still wins over the report's own title
    expect(layout.title).to.be.equal('Device type');
    expect(layout.widgetControls).to.be.equal(0);
    expect(layout.actionRowsInTable).to.be.equal(1);
  });

  it('should keep the header across sorting, limit and visualization changes', async function () {
    await loadWidget();

    await interactWithColumnSortingAndLimit();
    expect((await readHeaderLayout()).headers).to.be.equal(1);

    await changeVisualization();
    expect((await readHeaderLayout()).headers).to.be.equal(1);

    await toggleTotalsRow();
    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.title).to.be.equal('Device type');
  });

  it('should render the header of a report without data', async function () {
    await page.goto(emptyDevicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(`${widgetSelector} .dataTable.isDataTableEmpty`, { visible: true });
    await page.waitForNetworkIdle();

    const layout = await readHeaderLayout();
    expect(layout.headers).to.be.equal(1);
    expect(layout.title).to.be.equal('Device type');
  });

  // A report shown without a content block card keeps the metrics of the plain `h2` it used to
  // be, and ReportHeader.less carries hand-copied values to do so. Compare against a real `h2`
  // rendered in the same page so the copies fail loudly if the global heading styles ever change.
  it('should give a report shown without a card the metrics of a plain heading', async function () {
    const ecommerceLog = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09"
      + "#?idSite=1&period=year&date=2012-08-09&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog";

    await page.goto(ecommerceLog);
    await page.waitForNetworkIdle();
    await page.waitForSelector('.reportHeader--plainTitle', { visible: true });

    const parity = await page.evaluate(() => {
      const header = document.querySelector('.reportHeader--plainTitle');
      const title = header.querySelector('.reportHeader__title');
      const probe = document.createElement('h2');
      probe.textContent = title.innerText;
      header.parentNode.insertBefore(probe, header);
      const of = getComputedStyle(title);
      const op = getComputedStyle(probe);
      const result = {
        height: [header.offsetHeight, probe.offsetHeight],
        fontSize: [of.fontSize, op.fontSize],
        lineHeight: [of.lineHeight, op.lineHeight],
        color: [of.color, op.color],
      };
      probe.remove();
      return result;
    });

    Object.keys(parity).forEach((k) => {
      expect(parity[k][0], k).to.be.equal(parity[k][1]);
    });
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
