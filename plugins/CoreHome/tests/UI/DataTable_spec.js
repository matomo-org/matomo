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
      page.webpage.removeListener('request', requestHandler);
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
