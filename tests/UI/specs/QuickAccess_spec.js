/*!
 * Matomo - free/libre analytics platform
 *
 * ActionsDataTable screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("QuickAccess", function () {
    const selectorToCapture = ".quick-access,.quick-access .dropdown";
    const url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09";

    async function enterSearchTerm(searchTermToAdd) {
        await page.evaluate(function () {
            $('.quick-access input').val('');
        });

        await page.focus(".quick-access input");
        await page.keyboard.type(searchTermToAdd);
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);

        await page.evaluate(function () {
            $('.quick-access input').blur();
        });
    }

    it("should be displayed", async function () {
        await page.goto(url);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('initially');
    });

    it("should search for something and update view", async function () {
        await enterSearchTerm('s');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_1');
    });

    it("should search again when typing another letter", async function () {
        await enterSearchTerm('as');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_2');
    });

    it("should show message if no results", async function () {
        await enterSearchTerm('alaskdjfs');
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_no_result');
    });

    it("should be possible to activate via shortcut", async function () {
        await page.goto(url);
        await page.focus('body');
        await page.keyboard.type('f');

        await page.evaluate(function () {
            $('.quick-access input').blur();
        });

        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('shortcut');
    });

    it("should search for websites", async function () {
        await enterSearchTerm('si');
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_sites');
    });

    it("clicking on a category should show all items that belong to that category", async function () {
        const element = await page.jQuery('.quick-access-category:first');
        await element.click();
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selectorToCapture)).to.matchImage('search_category');
    });
});
