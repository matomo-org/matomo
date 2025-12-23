/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SegmentManagementPageTest", function () {
    var generalParams = 'idSite=1&period=range&date=2010-03-06,2010-03-08';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=CoreHome_Segments';

    it("should load correctly", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('initial');
    });
});
