/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Morpheus", function () {
    this.timeout(0);

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

    it("should keep content in auto-clear password field before auto-clear time", async function() {
        const element = await page.$('[data-snippet="form.passwordAutoClear"]');
        await page.type('#password_autoclear', 'some password');
        await page.waitForTimeout(3000);
        expect(await element.screenshot()).to.matchImage('passwordAutoClear_filled');
    });

    it("should remove content in auto-clear password field after auto-clear time", async function() {
        const element = await page.$('[data-snippet="form.passwordAutoClear"]');
        await page.waitForTimeout(3000); // there's already 3 seconds wait in the previous test
        expect(await element.screenshot()).to.matchImage('passwordAutoClear_cleared');
    });
});
