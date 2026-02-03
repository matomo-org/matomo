/*!
 * Matomo - free/libre analytics platform
 *
 * ReportExportPopover UI tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ReportExportPopover', function () {
  this.fixture = "Piwik\\Tests\\Fixtures\\UITestFixture";

  const url = "?module=CoreHome&action=index&idSite=1&period=day&date=2012-08-09"
    + "#?idSite=1&period=day&date=2012-08-09&category=General_Actions&subcategory=General_Pages";

  async function isOptionExpandSubtableVisible() {
    return await page.evaluate(() => (
      $('#reportExport div[name="option_expanded"] div.form-group.matomo-form-field').is(':visible')
    ));
  }
  async function clickFormat(format){
    await page.evaluate((formatValue) => {
      const selector = `#reportExport input[name="format"][value="${formatValue}"]`;
      const input = document.querySelector(selector);
      if (input) {
        input.click();
      }
    }, format);
  }
  async function clickOptionFlat() {
    await page.evaluate(() => {
      const input = document.querySelector('#reportExport input[name="option_flat"]');
      if (input) {
        input.click();
      }
    });
  }
  it('should hide expanded option when CSV or TSV format is selected and show it for everything else', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();
    await page.waitForSelector('#widgetActionsgetPageUrls', { visible: true });
    await page.waitForSelector('#widgetActionsgetPageUrls .dataTable', { visible: true });
    await page.waitForFunction(() => (
      !!document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection')
    ));
    await page.evaluate(() => {
      const button = document.querySelector('#widgetActionsgetPageUrls .dataTableAction.activateExportSelection');
      if (button) {
        button.click();
      }
    });
    await page.waitForSelector('#reportExport', { visible: true });
    const formatsToCheck = ['CSV', 'JSON', 'TSV', 'HTML', 'RSS', 'XML'];
    const formatsToHideExpanded = ['CSV', 'TSV'];

    let retoggleFlat = false;
    for (const format of formatsToCheck) {
      if (retoggleFlat) {
        await clickOptionFlat();
        await page.waitForTimeout(200);
        retoggleFlat = false;
      }
      await clickFormat(format);
      await page.waitForTimeout(200);
      const optionIsExpanded = await isOptionExpandSubtableVisible();
      if (formatsToHideExpanded.includes(format)) {
        expect(optionIsExpanded, `format ${format} should hide expanded option`).to.be.false;
        retoggleFlat = true;
      } else {
        expect(optionIsExpanded, `format ${format} should show expanded option`).to.be.true;
      }
    }
  });
});
