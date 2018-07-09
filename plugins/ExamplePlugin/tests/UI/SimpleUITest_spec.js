/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SimpleUITest", function () {
    this.timeout(0);

    // uncomment this if you want to define a custom fixture to load before the test instead of the default one
    // this.fixture = "Piwik\\Plugins\\ExamplePlugin\\tests\\Fixtures\\YOUR_FIXTURE_NAME";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    before(function () {
        testEnvironment.pluginsToLoad = ['ExamplePlugin'];
        testEnvironment.save();
    });

    it('should load a simple page by its module and action and take a full screenshot', async function() {
        var screenshotName = 'simplePage';
        // will take a screenshot and store it in "processed-ui-screenshots/SimpleUITest_simplePage.png"
        var urlToTest = "?" + generalParams + "&module=ExamplePlugin&action=index";

        await page.goto(urlToTest);

        expect(await page.screenshot({ fullPage: true })).to.matchImage(screenshotName);
    });

    it('should load a simple page by its module and action and take a partial screenshot', async function() {
        var screenshotName  = 'simplePagePartial';
        // will take a screenshot and store it in "processed-ui-screenshots/SimpleUITest_simplePagePartial.png"
        var contentSelector = '#root,.expandDataTableFooterDrawer';
        // take a screenshot only of the content of this CSS/jQuery selector
        var urlToTest       = "?" + generalParams + "&module=ExamplePlugin&action=index";
        // "?" + urlBase + "#" + generalParams + "&module=ExamplePlugin&action=index"; this defines a URL for a page within the dashboard

        await page.goto(urlToTest);

        expect(await page.screenshotSelector(contentSelector)).to.matchImage(screenshotName);
    });
});