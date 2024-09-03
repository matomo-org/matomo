/*!
 * Matomo - free/libre analytics platform
 *
 * Tests that theming works.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DisablePluginArchive", function () {

  this.fixture = 'Piwik\\Tests\\Fixtures\\DisablePluginArchive';

  before(function () {
    const firefoxUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0";
    page.setUserAgent(firefoxUserAgent);
  });

  after(async () => {
    await page.setUserAgent(page.originalUserAgent);
  });

  var url = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
    + "actionToWidgetize=getKeywords&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1&segment=actions%3D%3D1";


  it("should show plugin disable text", async function () {
     await page.goto(url);
     expect(await page.screenshot({ fullPage: true })).to.matchImage('referrer_disabled');
  });
});
