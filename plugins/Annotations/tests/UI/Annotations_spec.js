/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for Marketplace.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Annotations", function () {
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

    // Adding an annotation with a range selected used to fail because
    // the inherited period=range made AjaxHelper reject the single annotation date.
    it("should add annotation when a date range is selected", async function() {
        const rangeParams = "idSite=1&period=range&date=2012-03-01,2012-04-01";
        await page.goto("?module=CoreHome&action=index&" + rangeParams + category);
        await page.waitForNetworkIdle();

        await page.click(".evolution-annotations span[title^=\"View and add annotations\"]");
        await page.waitForSelector(".add-annotation", { visible: true });

        await page.click(".add-annotation");
        await page.waitForSelector(".new-annotation-edit", { visible: true });

        await page.type(".new-annotation-edit", "range period annotation");
        await page.click(".new-annotation-save");
        await page.waitForNetworkIdle();

        // the manager only shows the note if the save succeeded
        const managerText = await page.evaluate(function () {
            return document.querySelector(".isFirstWidgetInPage .annotation-manager").innerText;
        });
        expect(managerText).to.contain("range period annotation");
    });

    // Same fix for the edit path: saving an edited annotation also sends a single date.
    it("should save an edited annotation when a date range is selected", async function() {
        // still on the range page from the previous test; edit the first annotation
        const annotation = ".isFirstWidgetInPage .annotation-manager .annotation";

        await page.click(annotation + " .annotation-value .annotation-enter-edit-mode");
        await page.waitForSelector(annotation + " .annotation-edit", { visible: true });

        // change the note text so the save isn't skipped as a no-op
        await page.click(annotation + " .annotation-edit", { clickCount: 3 });
        await page.type(annotation + " .annotation-edit", "edited range annotation");
        await page.click(annotation + " .annotation-edit-mode .annotation-save");
        await page.waitForNetworkIdle();

        // the manager only shows the new text if the save succeeded
        const managerText = await page.evaluate(function () {
            return document.querySelector(".isFirstWidgetInPage .annotation-manager").innerText;
        });
        expect(managerText).to.contain("edited range annotation");
    });
});
