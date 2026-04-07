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

    async function setColorSchemePreference(value) {
        await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: value }]);
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
    async function recreatePage() {
      await page.createPage();
      await setColorSchemePreference('light');
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['ExampleTheme'];

        // Enable development mode to be able to see the UI demo page
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();

        clearAssets();
    });

    after(async function () {
        await setColorSchemePreference('light');
        await saveThemeMode('light');
        clearAssets();
    });

    it("should use the current theme", async function () {
        await setColorSchemePreference('light');
        await saveThemeMode('light');
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForSelector('.widget');
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('home');
    });

    it("should screenshot dashboard in dark mode", async function () {
        await setColorSchemePreference('light');
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
        await setColorSchemePreference('light');
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
        await setColorSchemePreference('light');
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

    it("should use dark styling in match browser mode when the browser prefers dark", async function () {
        await setColorSchemePreference('dark');
        await saveThemeMode('auto');
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        await page.waitForSelector('.widget');
        await page.waitForFunction(() => {
            return document.documentElement.getAttribute('data-theme-mode') === 'auto'
                && window.piwik.getThemeMode() === 'dark';
        });
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('home_dark');
    });

    it("should update the resolved theme when the browser theme changes in match browser mode", async function () {
        await recreatePage();
        try {
          await page.evaluateOnNewDocument(() => {
            const originalMatchMedia = window.matchMedia ? window.matchMedia.bind(window) : null;
            const listeners = [];
            const fakeMediaQueryList = {
              matches: false,
              media: '(prefers-color-scheme: dark)',
              addEventListener (eventName, listener) {
                if (eventName === 'change') {
                  listeners.push(listener);
                }
              },
              removeEventListener (eventName, listener) {
                if (eventName !== 'change') {
                  return;
                }

                const index = listeners.indexOf(listener);
                if (index !== -1) {
                  listeners.splice(index, 1);
                }
              },
            };

            window.__setFakeBrowserThemePreference = (matches) => {
              fakeMediaQueryList.matches = matches;
              listeners.slice()
                .forEach((listener) => listener({
                  matches: fakeMediaQueryList.matches,
                  media: fakeMediaQueryList.media,
                }));
            };

            window.matchMedia = (query) => {
              if (query === fakeMediaQueryList.media) {
                return fakeMediaQueryList;
              }

              if (originalMatchMedia) {
                return originalMatchMedia(query);
              }

              return {
                matches: false,
                media: query,
                addEventListener () {},
                removeEventListener () {},
              };
            };
          });

          await saveThemeMode('auto');
          await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
          await page.waitForSelector('.widget');
          await page.waitForFunction(() => window.piwik.getThemeMode() === 'light');

          await page.evaluate(() => {
            window.__setFakeBrowserThemePreference(true);
          });

          await page.waitForFunction(() => window.piwik.getThemeMode() === 'dark');
        } finally {
          await recreatePage();
        }
    });

    it("should fall back to light styling when matchMedia is unavailable", async function () {
        await setColorSchemePreference('dark');
        await saveThemeMode('auto');
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");

        const result = await page.evaluate(() => {
            const originalMatchMedia = window.matchMedia;
            delete window.matchMedia;
            const themeMode = window.piwik.refreshThemeMode(false);
            window.matchMedia = originalMatchMedia;
            return themeMode;
        });

        expect(result.resolvedThemeMode).to.equal('light');
    });
});
