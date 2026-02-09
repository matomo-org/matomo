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

    for (const format of formatsToCheck) {
      await clickFormat(format);
      const shouldShowExpanded = !formatsToHideExpanded.includes(format);
      await page.waitForFunction(
        (formatValue) => (
          document.querySelector(`#reportExport input[name="format"][value="${formatValue}"]`)?.checked === true
        ),
        {},
        format,
      );
      const optionIsExpanded = await isOptionExpandSubtableVisible();
      expect(optionIsExpanded, `format ${format} should ${shouldShowExpanded ? 'show' : 'hide'} expanded option`)
        .to.equal(shouldShowExpanded);
    }
  });
});
