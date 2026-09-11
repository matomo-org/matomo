/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TrackingSpamPreventionSettings", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\TrackingSpamPrevention\\tests\\Fixtures\\TrackingFixture";

    var selector = '.card-content:contains(\'TrackingSpamPrevention\')';
    var url = "?module=CoreAdminHome&action=generalSettings&idSite=1&period=day&date=yesterday";

    before(function () {
        testEnvironment.pluginsToLoad = ['TrackingSpamPrevention'];
        testEnvironment.save();
    });

    it("should display the settings page", async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(20000);
        expect(await page.screenshotSelector(selector)).to.matchImage('page');
    });

    it("should show the default provider list read-only when the default list is selected", async function () {
        // the radio input itself is visually replaced by the span materialize renders next to it
        await page.click('#cloud_blocking_modedefault + span');
        await page.waitForSelector('#default_organisation_block_list', { visible: true });

        const isDisabled = await page.evaluate(
            () => document.querySelector('#default_organisation_block_list').disabled
        );
        expect(isDisabled).to.equal(true);

        expect(await page.screenshotSelector(selector)).to.matchImage('default_list');
    });
});