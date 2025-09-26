/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Annotations", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\TwoSitesWithAnnotations";

    const generalParams = "idSite=1&period=day&date=2012-04-01";
    const category = "&category=General_Visitors&subcategory=General_Overview";
    const url = "?module=CoreHome&action=index&" + generalParams + category;

    it("should show annotations", async function() {
        await page.goto(url);
        await page.waitForNetworkIdle();

        await page.click(".evolution-annotations span[title^=\"View and add annotations\"]");
        await page.waitForTimeout(200);

        expect(await page.screenshotSelector(".isFirstWidgetInPage .annotation-manager")).to.matchImage("list_annotations");
    });

    it("should add annotation", async function() {
        await page.click(".add-annotation");
        await page.waitForTimeout(100);

        await page.type(".new-annotation-edit", "<h2>x</h2><script>alert(5)</script>{{ 2+2 }}");
        await page.click(".new-annotation-save");
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(".isFirstWidgetInPage .annotation-manager")).to.matchImage("added_annotation");
    });
});
