/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var fs = require('fs'),
    path = require('../../../../tests/lib/screenshot-testing/support/path');

describe("CustomLogo", function () {
    this.timeout(0);

    before(function () {
        testEnvironment.optionsOverride = {
            branding_use_custom_logo: '1'
        };
        testEnvironment.save();
    });

    afterEach(function () {
        [
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png"),
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo-header.png"),
            path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.svg")
        ].forEach(function(file) {
            if (fs.existsSync(file)) {
                fs.unlinkSync(file);
            }
        });
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    var copyLogo = function(svg) {
        fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png"));
        fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo-header.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo-header.png"));

        if (svg) {
            fs.copyFileSync(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.svg"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.svg"));
        }
    };

    [true, false].forEach(function (useSvg) {

        var appendName = useSvg ? '_svg' : '';
        var appendTitle = useSvg ? ' SVG' : '';

        it('should show the custom'+appendTitle+' logo in admin header', async function () {
            copyLogo(useSvg);
            await page.goto("?idSite=1&period=year&date=2012-08-09&module=CoreAdminHome&action=index");

            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('admin'+appendName);
        });

        it('should show the custom'+appendTitle+' logo in login header', async function () {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.save();

            copyLogo(useSvg);
            await page.goto("");
            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('login'+appendName);
        });

        it('should show the custom'+appendTitle+' logo in unsubscribe email header', async function () {
            copyLogo(useSvg);
            await page.goto("?module=ScheduledReports&action=unsubscribe&token=");
            var navWrap = await page.$('.nav-wrapper');
            expect(await navWrap.screenshot()).to.matchImage('unsubscribe'+appendName);
        });
    });

    // dummy test to ensure custom logo usage is reset
    it('should remove the custom logo usage', async function () {
        testEnvironment.optionsOverride = {
            branding_use_custom_logo: '0'
        };
        testEnvironment.save();
        await page.goto("");
    });
});
