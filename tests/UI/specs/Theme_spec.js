/*!
 * Matomo - free/libre analytics platform
 *
 * Tests that theming works.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('../../lib/screenshot-testing/support/path');

var removeTree = function(path) {
    if (fs.existsSync(path)) {
        fs.readdirSync(path).forEach(function (file, index) {
            var curPath = path + "/" + file;
            if (fs.lstatSync(curPath).isDirectory()) { // recurse
                removeTree(curPath);
            } else { // delete file
                fs.unlinkSync(curPath);
            }
        });
        fs.rmdirSync(path);
    }
}

describe("Theme", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    function clearAssets() {
        removeTree(path.join(PIWIK_INCLUDE_PATH, 'tmp', 'assets'));
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['ExampleTheme'];

        // Enable development mode to be able to see the UI demo page
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();

        clearAssets();
    });

    after(function () {

        clearAssets();
    });

    it("should use the current theme", async function () {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForSelector('.widget');
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
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
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('demo');
    });
});
