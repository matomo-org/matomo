/*!
 * Matomo - free/libre analytics platform
 *
 * Dashboard screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("Dashboard", function () {

  var generalParams = 'idSite=1&period=year&date=2012-08-09',
    urlBase = 'module=CoreHome&action=index&' + generalParams;

  before(async function () {
    testEnvironment.queryParamOverride = {
      forceNowValue: testEnvironment.forcedNowTimestamp,
      visitorId: testEnvironment.forcedIdVisitor,
      realtimeWindow: 'false'
    };
    testEnvironment.completeNoChallenge = true;
    testEnvironment.pluginsToLoad = ['CustomDirPlugin'];
    testEnvironment.save();
  });

  after(function () {
    delete testEnvironment.queryParamOverride;
    delete testEnvironment.completeNoChallenge;
  });

  it("should load dashboard2 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=2");
    await page.waitForNetworkIdle();
    await page.evaluate(function () {
      // Prevent random sizing error eg. http://builds-artifacts.matomo.org/ui-tests.master/2301.1/screenshot-diffs/diffviewer.html
      $("[widgetid=widgetActionsgetOutlinks] .widgetContent").text('Displays different at random -> hidden');
    });

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard2');
  });

  it("should load dashboard3 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=3");
    await page.waitForNetworkIdle();
    await page.waitForSelector('.widget');
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard3');
  });

  it("should load dashboard4 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=4");
    await page.waitForNetworkIdle();
    await page.waitForSelector('.widget');
    await page.waitForNetworkIdle();
    await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
      $('.dataTableScroller').css('overflow-x', 'scroll');
    });
    await page.waitForTimeout(500);
    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard4');
  });

  it("should load dashboard5 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=5");
    await page.waitForNetworkIdle();
    await page.waitForSelector('.widget');
    await page.waitForNetworkIdle();

    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard5');
  });

  it("should display dashboard correctly on a mobile phone", async function () {
    await page.webpage.setViewport({
      width: 480,
      height: 320
    });
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=1");
    await page.waitForNetworkIdle();

    expect(await page.screenshot({ fullPage: true })).to.matchImage('dashboard1_mobile');
  });
});
