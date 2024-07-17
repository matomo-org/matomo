/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Morpheus", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = "?module=Morpheus&action=demo";

    before(function () {
        // Enable development mode
        testEnvironment.overrideConfig('Development', 'enabled', true);
        testEnvironment.save();
    });

    it("should show all UI components and CSS classes", async function() {
        await page.goto(url);
        await page.waitForSelector('.progressbar img');
        await page.evaluate(() => {
            $('img[src~=loading],.progressbar img').each(function () {
                $(this).hide();
            });
        });
        await page.waitForTimeout(500); // wait for rendering
        expect(await page.screenshot({ fullPage: true })).to.matchImage('load');
    });
});
