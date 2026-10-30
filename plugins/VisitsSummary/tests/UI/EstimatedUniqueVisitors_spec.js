/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('EstimatedUniqueVisitors', function () {
  // a fixture that is not archived up front, so the archives are built with the estimate switched on
  this.fixture = 'Piwik\\Tests\\Fixtures\\VisitsOverSeveralDays';

  const overviewUrl = '?module=CoreHome&action=index&idSite=1&period=year&date=2010-12-14'
    + '#?idSite=1&period=year&date=2010-12-14&category=General_Visitors&subcategory=General_Overview';

  async function getPickerEntries() {
    // the same URL with a hash would only scroll the page that is already open, so leave it first
    await page.goto('about:blank');
    await page.goto(overviewUrl);
    await page.waitForSelector('.sparkline');
    await page.waitForNetworkIdle();
    await page.click('.series-picker-wrapper .mtm-selector__trigger');
    await page.waitForSelector('.series-picker-wrapper .mtm-dropdownPanel__menuLink');
    return page.evaluate(() => $('.series-picker-wrapper .mtm-dropdownPanel__menuLink').map((i, e) => $(e).text().trim()).get());
  }

  afterEach(function () {
    delete testEnvironment.configOverride.General;
    testEnvironment.save();
  });

  it('should offer the estimate in the evolution graph when it is on', async function () {
    testEnvironment.configOverride.General = { enable_processing_unique_visitors_estimated: 1 };
    testEnvironment.save();

    expect(await getPickerEntries()).to.include('Unique visitors (estimated)');
    expect(await page.evaluate(() => $('.sparkline').text())).not.to.match(/estimated/i);
  });

  it('should not offer the estimate when it is off', async function () {
    expect(await getPickerEntries()).not.to.include('Unique visitors (estimated)');
  });
});
