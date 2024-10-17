/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for UserId plugin
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserId", function () {
  this.fixture = "Piwik\\Plugins\\UserId\\tests\\Fixtures\\TrackFewVisitsAndCreateUsers";

  it('should show user id report', async function () {
    await page.goto('?module=CoreHome&action=index&idSite=1&period=day&date=2010-02-04#?idSite=1&period=day&date=2010-02-04&category=General_Visitors&subcategory=UserId_UserReportTitle');
    await page.waitForNetworkIdle();
    expect(await page.screenshotSelector('#widgetUserIdgetUsers')).to.matchImage('report');
  });

    it('should switch to table with engagement metrics', async function () {
      await page.click('.activateVisualizationSelection > span');
      await page.click('.tableIcon[data-footer-icon-id=tableAllColumns]');
      await page.mouse.move(-10, -10);
      await page.waitForNetworkIdle();
      expect(await page.screenshotSelector('#widgetUserIdgetUsers')).to.matchImage('report_engagement');
  });


});
