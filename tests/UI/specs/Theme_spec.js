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
    function clearAssets() {
        removeTree(path.join(PIWIK_INCLUDE_PATH, 'tmp', 'assets'));
    }

    async function saveThemeMode(themeMode) {
        await page.goto("?module=UsersManager&action=userSettings&idSite=1&period=day&date=yesterday");
        await page.waitForSelector(`input[name="themeMode"][value="${themeMode}"]`);
        await page.click(`input[name="themeMode"][value="${themeMode}"]`);
        await page.click('.matomo-save-button input.btn');
        await page.waitForFunction((mode) => {
            return document.documentElement.getAttribute('data-theme-mode') === mode;
        }, {}, themeMode);
        await page.waitForNetworkIdle();
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['ExampleTheme'];

        // Enable development mode to be able to see the UI demo page
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();

        clearAssets();
    });

    after(async function () {
        await saveThemeMode('light');
        clearAssets();
    });

    it("should use the current theme", async function () {
        await saveThemeMode('light');
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForSelector('.widget');
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('home');
    });

    it("should screenshot dashboard in dark mode", async function () {
        await saveThemeMode('dark');
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForSelector('.widget');
        await page.waitForFunction(() => {
            return document.documentElement.getAttribute('data-theme-mode') === 'dark';
        });
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('home_dark');
    });

    it("should theme the UI demo page", async function () {
        await saveThemeMode('light');
        await page.goto("?module=Morpheus&action=demo");
        await page.waitForSelector('.progressbar .matomo-loader');
        await page.evaluate(() => {
            $('img[src~=loading],.progressbar .matomo-loader').each(function () {
                $(this).hide();
            });
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('demo');
    });

    it("should screenshot the UI demo page in dark mode", async function () {
        await saveThemeMode('dark');
        await page.goto("?module=Morpheus&action=demo");
        await page.waitForSelector('.progressbar .matomo-loader');
        await page.waitForFunction(() => {
            return document.documentElement.getAttribute('data-theme-mode') === 'dark';
        });
        await page.evaluate(() => {
            $('img[src~=loading],.progressbar .matomo-loader').each(function () {
                $(this).hide();
            });
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('demo_dark');
    });
});
