/*!
 * Piwik - free/libre analytics platform
 *
 * Tests that theming works.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs');

describe("Theme", function () {
    this.retries(2);

    this.timeout(0);

    function clearAssets() {
        fs.removeTree(path.join(PIWIK_INCLUDE_PATH, 'tmp', 'assets'));
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

    it("should use the current theme", function (done) {
        expect.screenshot("home").to.be.capture(function (page) {
            page.load("?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09");
        }, done);
    });

    it("should theme the UI demo page", function (done) {
        expect.screenshot("demo").to.be.capture(function (page) {
            page.load("?module=Morpheus&action=demo");
        }, done);
    });
});
