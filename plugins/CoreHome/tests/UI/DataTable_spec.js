/*!
 * Matomo - free/libre analytics platform
 *
 * UI regression for DataTable sorting & AJAX reloading.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('DataTable', function () {
  const devicesUrl = "?module=CoreHome&action=index&idSite=3&period=day&date=yesterday&category=General_Visitors&subcategory=DevicesDetection_Devices";
  const widgetSelector = '#widgetDevicesDetectiongetType';

  async function openDevicesDetectionWidget() {
    await page.goto(devicesUrl);
    await page.waitForNetworkIdle();
    await page.waitForSelector(widgetSelector, { visible: true });
    await page.waitForNetworkIdle();
  }

  before(async function () {

  });

  after(async function () {
    testEnvironment.testUseMockAuth = 1;
    testEnvironment.save();
  });

  it('should trigger an ajax call to save preference for normal user when doing some datatable actions', async function () {
    await openDevicesDetectionWidget();

    const table = await page.$(widgetSelector);
    expect(table).to.be.ok;

    const reportPage = await page.$('.reporting-page');
    expect(reportPage).to.be.ok;

    let ajaxRequestCount = 0;
    const requestHandler = (request) => {
      if (request.resourceType() === 'xhr' && request.url().indexOf('saveViewDataTableParameters') !== -1) {
        console.log('i am inside the request handler');
        console.log(request.url());
        ajaxRequestCount += 1;
      }
    };
    console.log('adding listener');
    page.webpage.on('request', requestHandler);

    await page.waitForSelector(`${widgetSelector} #nb_uniq_visitors`, { visible: true });
    await page.click(`${widgetSelector} #nb_uniq_visitors`);
    await page.waitForNetworkIdle();
    await page.click(`${widgetSelector} #nb_uniq_visitors`);
    await page.waitForNetworkIdle();

    const limitDropdownTrigger = `${widgetSelector} .limitSelection .select-wrapper input.select-dropdown`;
    const limitDropdownMenu = `${widgetSelector} .limitSelection .select-wrapper ul.select-dropdown`;

    await page.waitForSelector(limitDropdownTrigger, { visible: true });
    await page.click(limitDropdownTrigger);

    await page.waitForSelector(limitDropdownMenu, { visible: true });
    let dropdown = await page.$(limitDropdownMenu);
    expect(dropdown).to.be.ok;

    // Pick the first option to trigger an AJAX call
    await page.click(`${limitDropdownMenu} li:nth-child(1)`);

    // give the AJAX call a moment to fire
    await page.waitForNetworkIdle();
    page.webpage.removeListener('request', requestHandler);
    expect(ajaxRequestCount).to.be.equal(3);

  });

  it('should allow changing the visualization of the devices widget', async function () {
    await openDevicesDetectionWidget();

    const table = await page.$(widgetSelector);
    expect(table).to.be.ok;

    let ajaxRequestCount = 0;
    const requestHandler = (request) => {
      if (request.resourceType() === 'xhr' && request.url().indexOf('saveViewDataTableParameters') !== -1) {
        console.log('diri naman', request.url());
        ajaxRequestCount += 1;
      }
    };
    page.webpage.on('request', requestHandler);

    const visualizationTrigger = `${widgetSelector} .dataTableHeaderControls .dataTableControls a.activateVisualizationSelection`;
    const visualizationTriggerIcon = `${visualizationTrigger} > span, ${visualizationTrigger} > img`;
    const visualizationMenu = `${widgetSelector} .dataTableHeaderControls .dataTableControls ul.dropdown-content.dataTableFooterIcons`;
    const visualizationButtonSelector = `${visualizationMenu} li .tableIcon[data-footer-icon-id]`;

    await page.waitForSelector(visualizationTriggerIcon, { visible: true });
    await page.click(visualizationTriggerIcon);
    await page.waitForSelector(visualizationMenu, { visible: true });

    await page.waitForFunction((selector) => document.querySelectorAll(selector).length >= 2, {}, visualizationButtonSelector);
    await page.$$eval(visualizationButtonSelector, (buttons) => {
      if (buttons.length < 2) {
        throw new Error('Not enough visualization buttons found');
      }
      console.log('button html', buttons[1].outerHTML);
      buttons[1].click();
    });

    await page.waitForNetworkIdle();
    page.webpage.removeListener('request', requestHandler);
    expect(ajaxRequestCount).to.be.equal(1);
  });

  it('should allow toggling the totals row via the configuration menu', async function () {
    await openDevicesDetectionWidget();

    const table = await page.$(widgetSelector);
    expect(table).to.be.ok;

    let ajaxRequestCount = 0;
    const requestHandler = (request) => {
      if (request.resourceType() === 'xhr' && request.url().indexOf('saveViewDataTableParameters') !== -1) {
        ajaxRequestCount += 1;
      }
    };
    page.webpage.on('request', requestHandler);

    const configureTrigger = `${widgetSelector} .dataTableHeaderControls .dataTableControls a.dropdownConfigureIcon`;
    const totalsRowToggle = '.dataTableShowTotalsRow';

    const toggleTotalsRow = async () => {
      await page.click(configureTrigger);
      await page.waitForSelector(totalsRowToggle, { visible: true });
      await page.click(totalsRowToggle);
    };

    await page.waitForSelector(configureTrigger, { visible: true });
    await toggleTotalsRow();
    await page.waitForNetworkIdle();

    const reportPage = await page.$('.reporting-page');
    expect(reportPage).to.be.ok;

    await toggleTotalsRow();
    await page.waitForNetworkIdle();

    page.webpage.removeListener('request', requestHandler);
    expect(ajaxRequestCount).to.be.equal(2);

  });
});
