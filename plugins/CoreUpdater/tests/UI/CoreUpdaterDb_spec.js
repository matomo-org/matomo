/*!
 * Matomo - free/libre analytics platform
 *
 * CoreUpdater screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CoreUpdaterDb", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CoreUpdater\\tests\\Fixtures\\DbUpdaterTestFixture";

    before(function () {
        testEnvironment.tablesPrefix = 'piwik_';
        testEnvironment.save();
    });

    after(function () {
        if (testEnvironment.configOverride.General) {
            delete testEnvironment.configOverride.General;
            testEnvironment.save();
        }
    });

    function apiUpgradeTest(format) {
        it("should start the updater when an old version of Piwik is detected in the DB with format " + format, async function() {
            expect.file('CoreUpdater.API.ErrorMessage' + format + '.txt').to.be.pageContents(function (page) {
                page.load('');
                page.downloadUrl('?module=API&method=API.getPiwikVersion&format=' + format);
            }, done);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON', 'PHP'];
    formats.forEach(apiUpgradeTest);

    it("should start the updater when an old version of Piwik is detected in the DB", async function() {
        expect.screenshot("main").to.be.capture(function (page) {
            page.load("");
            page.evaluate(function () {
                $('p').each(function () {
                    var replace = $(this).html().replace(/(?!1\.0)\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, '');
                    $(this).html(replace);
                });
            });
        }, done);
    });

    it("should show instance id in updating screen", async function() {
        expect.screenshot("main_instance").to.be.capture(function (page) {
            testEnvironment.configOverride.General = {
                instance_id: 'custom.instance'
            };
            testEnvironment.save();

            page.load("");
            page.evaluate(function () {
                $('p').each(function () {
                    var replace = $(this).html().replace(/(?!1\.0)\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, '');
                    $(this).html(replace);
                });
            });
        }, done);
    });

    it("should show the donation form when the update process is complete", async function() {
        expect.screenshot("updated").to.be.capture(function (page) {
            page.click('.btn');
        }, done);
    });
});
