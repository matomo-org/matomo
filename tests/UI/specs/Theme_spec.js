/*!
 * Matomo - free/libre analytics platform
 *
 * Tests that theming works.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Theme", function () {
    this.timeout(0);

    before(function () {
        testEnvironment.pluginsToLoad = ['ExampleTheme'];

        // Enable development mode to be able to see the UI demo page
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();

        page.clearAssets();
    });

    after(function () {

        page.clearAssets();
    });

    it("should use the current theme", async function () {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForTimeout(500); // wait for angular finished rendering
        expect(await page.screenshot({ fullPage: true })).to.matchImage('home');
    });

    it("should theme the UI demo page", async function () {
        await page.goto("?module=Morpheus&action=demo");
        await page.waitForSelector('.progressbar img');
        await page.evaluate(() => {
            $('img[src~=loading],.progressbar img').each(function () {
                $(this).hide();
            });
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500); // wait for angular finished rendering
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('demo');
    });
});
