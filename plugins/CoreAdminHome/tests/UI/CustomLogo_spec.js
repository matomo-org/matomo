/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
            if (fs.exists(file)) {
                fs.remove(file);
            }
        });
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    var copyLogo = function(svg) {
        fs.copy(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.png"));
        fs.copy(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo-header.png"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo-header.png"));

        if (svg) {
            fs.copy(path.join(PIWIK_INCLUDE_PATH, "/tests/resources/customlogo/logo.svg"), path.join(PIWIK_INCLUDE_PATH, "/misc/user/logo.svg"));
        }
    };

    [true, false].forEach(function (useSvg) {

        var appendName = useSvg ? '_svg' : '';
        var appendTitle = useSvg ? ' SVG' : '';

        it('should show the custom'+appendTitle+' logo in admin header', function (done) {
            expect.screenshot('admin'+appendName).to.be.captureSelector('.nav-wrapper', function (page) {
                copyLogo(useSvg);
                page.load("?idSite=1&period=year&date=2012-08-09&module=CoreAdminHome&action=index");
            }, done);
        });

        it('should show the custom'+appendTitle+' logo in login header', function (done) {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.save();

            expect.screenshot('login'+appendName).to.be.captureSelector('.nav-wrapper', function (page) {
                copyLogo(useSvg);
                page.load("");
            }, done);
        });

        it('should show the custom'+appendTitle+' logo in unsubscribe email header', function (done) {
            expect.screenshot('unsubscribe'+appendName).to.be.captureSelector('.nav-wrapper', function (page) {
                copyLogo(useSvg);
                page.load("?module=ScheduledReports&action=unsubscribe&token=");
            }, done);
        });
    });
});