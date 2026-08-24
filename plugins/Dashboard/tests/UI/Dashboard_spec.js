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

    await page.mouse.move(-10, -10); // move off any widget so its hover-revealed controls stay hidden
    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard3');
  });

  // The widget chrome renders the report header itself (_widgetFactoryTemplate.twig), so the report
  // inside must not render a second one - neither on load nor after the chrome replaces its content.
  // Refreshing is the sharper case: it reloads the widget the same way the first load did, so a
  // report that claimed a header there would stack one under the chrome's. This is the failure that
  // got the first attempt at mounting a header for titleless reports reverted.
  it("should give no widget a second actions menu, on load or after a refresh", async function () {
    const triggersPerWidget = () => page.evaluate(
      () => Array.from(document.querySelectorAll('.widget'))
        .map((widget) => widget.querySelectorAll('.reportHeader__actionsTrigger').length)
    );

    const onLoad = await triggersPerWidget();
    // not every widget has actions (an RSS or sparklines widget has no footer icons), so the check
    // is that none has two - paired with a count of the ones that do have a menu, so a dashboard
    // that stopped rendering them entirely cannot pass vacuously
    expect(Math.max(...onLoad)).to.equal(1);
    expect(onLoad.filter((count) => count === 1).length).to.be.above(0);

    await page.evaluate(() => {
      document.querySelector('.widget .widgetControls__action--refresh').click();
    });
    await page.waitForNetworkIdle();

    const afterRefresh = await triggersPerWidget();
    expect(Math.max(...afterRefresh)).to.equal(1);
    expect(afterRefresh.filter((count) => count === 1).length).to.be.above(0);
  });

  it("should load dashboard4 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=4");
    await page.waitForNetworkIdle();
    await page.waitForSelector('.widget');
    await page.waitForNetworkIdle();
    await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
      $('.dataTableScroller').css('overflow-x', 'scroll');
      $('[widgetid="widgetRssWidgetrssChangelog"] .widgetContent').html(
        '<div style="padding:10px 15px;"><ul class="rss"><li>' +
        '<div class="rss-description">Changed to something static for tests</div>' +
        '</li></ul></div>'
      );
    });
    await page.waitForTimeout(100);
    await page.mouse.move(-10, -10); // move off any widget so its hover-revealed controls stay hidden
    pageWrap = await page.$('.pageWrap');
    expect(await pageWrap.screenshot()).to.matchImage('dashboard4');
  });

  it("should load dashboard5 correctly", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=5");
    await page.waitForNetworkIdle();
    await page.waitForSelector('.widget');
    await page.waitForNetworkIdle();

    await page.mouse.move(-10, -10); // move off any widget so its hover-revealed controls stay hidden
    pageWrap = await page.$('.pageWrap');
    // A report label (e.g. "Provence-Alpes-Côte-d'Azur, France" in the Region widget) sits right on the
    // CSS text-overflow:ellipsis truncation boundary. Headless Chrome's sub-pixel text layout is not fully
    // deterministic, so it occasionally truncates one character earlier ("Fr…" vs "Fra…"). Allow a tiny
    // difference so this purely cosmetic variance doesn't make the test flaky.
    expect(await pageWrap.screenshot()).to.matchImage({
      imageName: 'dashboard5',
      comparisonThreshold: 0.001
    });
  });

  it("should display dashboard correctly on a mobile phone", async function () {
    await page.webpage.setViewport({
      width: 480,
      height: 320
    });
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=1");
    await page.waitForNetworkIdle();

    expect(await page.screenshotSelector('.top_controls, #dashboard')).to.matchImage('dashboard1_mobile');
  });

  // A dashboard widget's header is declared empty by the chrome and filled from the report, and
  // its menu is rendered by Vue only once that happens - so both the trigger being there at all
  // and its entries acting are worth asserting here rather than on a full-page report, where twig
  // renders the header with the report's own props and neither failure can occur.
  it("should offer the report actions in a widget's header and act on them", async function () {
    await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=2");
    await page.waitForNetworkIdle();
    const widget = '[widgetid="widgetActionsgetOutlinks"]';
    await page.waitForSelector(widget + ' table.dataTable', { timeout: 30000 });
    await page.waitForNetworkIdle();
    await page.waitForTimeout(2000);

    const hasTrigger = await page.evaluate((w) =>
      !!document.querySelector(w + ' .reportHeader__actionsTrigger'), widget);
    expect(hasTrigger, 'widget offers an actions trigger').to.equal(true);

    await page.evaluate((w) => {
      document.querySelector(w + ' .reportHeader__actionsTrigger').click();
    }, widget);
    await page.waitForTimeout(300);

    const hasEntry = await page.evaluate((w) =>
      !!document.querySelector(w + ' .dataTableFlatten'), widget);
    expect(hasEntry, 'flatten entry present').to.equal(true);

    await page.evaluate((w) => { document.querySelector(w + ' .dataTableFlatten').click(); }, widget);
    await page.waitForNetworkIdle();
    await page.waitForTimeout(1500);

    const flat = await page.evaluate((w) => {
      const t = document.querySelector(w + ' .dataTable');
      const c = t && window.$(t).data('uiControlObject');
      return c ? String(c.param.flat) : 'no-control';
    }, widget);
    expect(flat, 'flat applied after clicking the entry').to.equal('1');
  });
});
