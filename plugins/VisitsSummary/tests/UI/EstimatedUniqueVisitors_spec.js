/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('EstimatedUniqueVisitors', function () {
  // a fixture that is not archived up front, so the archives are built with the estimate switched on
  this.fixture = 'Piwik\\Tests\\Fixtures\\VisitsOverSeveralDays';

  async function openOverview(period) {
    // the same URL with a hash would only scroll the page that is already open, so leave it first
    await page.goto('about:blank');
    await page.goto(`?module=CoreHome&action=index&idSite=1&period=${period}&date=2010-12-14`
      + `#?idSite=1&period=${period}&date=2010-12-14&category=General_Visitors&subcategory=General_Overview`);
    await page.waitForSelector('.sparkline');
    await page.waitForNetworkIdle();
  }

  async function getPickerEntries() {
    await page.click('.series-picker-wrapper .mtm-selector__trigger');
    await page.waitForSelector('.series-picker-wrapper .mtm-dropdownPanel__menuLink');
    return page.evaluate(() => $('.series-picker-wrapper .mtm-dropdownPanel__menuLink').map((i, e) => $(e).text().trim()).get());
  }

  async function getSparklineText() {
    return page.evaluate(() => $('.sparkline').text().replace(/\s+/g, ' '));
  }

  afterEach(function () {
    delete testEnvironment.configOverride.General;
    testEnvironment.save();
  });

  it('should label unique visitors as estimated on a year when it is on', async function () {
    testEnvironment.configOverride.General = { enable_unique_visitors_estimate_site_totals: 1 };
    testEnvironment.save();

    await openOverview('year');

    expect(await getSparklineText()).to.include('unique visitors (estimated)');
    expect(await getPickerEntries()).to.include.members(['Unique visitors (estimated)', 'Users (estimated)']);
  });

  it('should keep the exact labels on a day when it is on', async function () {
    testEnvironment.configOverride.General = { enable_unique_visitors_estimate_site_totals: 1 };
    testEnvironment.save();

    await openOverview('day');

    expect(await getSparklineText()).not.to.match(/estimated/i);
    expect(await getPickerEntries()).to.include('Unique visitors').and.not.include('Unique visitors (estimated)');
  });

  it('should show no unique visitors on a year when it is off', async function () {
    await openOverview('year');

    expect(await getSparklineText()).not.to.include('unique visitors');
    expect(await getPickerEntries()).not.to.include('Unique visitors');
  });
});
