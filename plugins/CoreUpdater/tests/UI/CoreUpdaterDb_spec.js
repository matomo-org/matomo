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
            const url = '?module=API&method=API.getPiwikVersion&format=' + format;
            var pageContents = await page.downloadUrl(url);

            expect.file('CoreUpdater.API.ErrorMessage' + format + '.txt').to.equal(pageContents);
        });
    }

    var formats = ['CSV', 'TSV', 'XML', 'JSON', 'PHP'];
    formats.forEach(apiUpgradeTest);

    it("should start the updater when an old version of Piwik is detected in the DB", async function() {
        await page.goto("");
        await page.evaluate(function () {
            $('p').each(function () {
                var replace = $(this).html().replace(/(?!1\.0)\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, '');
                $(this).html(replace);
            });
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('main');
    });

    it("should show instance id in updating screen", async function() {
        testEnvironment.configOverride.General = {
            instance_id: 'custom.instance'
        };
        testEnvironment.save();

        await page.goto("");
        await page.evaluate(function () {
            $('p').each(function () {
                var replace = $(this).html().replace(/(?!1\.0)\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, '');
                $(this).html(replace);
            });
        });

        expect(await page.screenshot({ fullPage: true })).to.matchImage('main_instance');
    });

    it("should show the donation form when the update process is complete", async function() {
        await page.click('.btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('updated');
    });
});
