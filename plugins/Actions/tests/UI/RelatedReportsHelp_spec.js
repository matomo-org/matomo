/*!
 * Matomo - free/libre analytics platform
 *
 * Verifies the report inline help (the "info" popup next to the report title) updates
 * when navigating between related reports. Regression test for
 * https://github.com/matomo-org/matomo/issues/23979
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RelatedReportsHelp", function () {
    const generalParams = 'idSite=1&period=year&date=2012-08-09';
    const urlBase = 'module=CoreHome&action=index&' + generalParams;

    // "Entry pages" and "Entry page titles" are two related reports sharing a subcategory,
    // so the Entry Pages report is shown with a related-report link to Entry Page Titles.
    const entryPagesUrl = '?' + urlBase + '#?' + generalParams
        + '&category=General_Actions&subcategory=Actions_SubmenuPagesEntry';

    async function inlineHelpText() {
        return page.evaluate(function () {
            const help = document.querySelector('.enrichedHeadline .inlineHelp');
            return help ? help.innerText.trim() : '';
        });
    }

    // The rate-feature widget's tooltip embeds the report name, so it reflects the feature
    // name that would be submitted as feedback (RateFeature sends its `title` as featureName).
    async function rateFeatureTitle() {
        return page.evaluate(function () {
            const el = document.querySelector('.enrichedHeadline .ratefeature');
            return el ? el.getAttribute('title') : '';
        });
    }

    async function openInlineHelp() {
        // the icons bar is only shown on hover, so reveal it before clicking the info icon
        await page.hover('.enrichedHeadline');
        await page.waitForSelector('.enrichedHeadline .icon-info', { visible: true });
        await page.click('.enrichedHeadline .icon-info');
        await page.waitForSelector('.enrichedHeadline .inlineHelp', { visible: true });
    }

    it("should update the report help text when switching to a related report", async function () {
        await page.goto(entryPagesUrl);
        await page.waitForNetworkIdle();

        await openInlineHelp();
        const entryPagesHelp = await inlineHelpText();
        expect(entryPagesHelp).to.contain('entry pages that were used');

        const entryPagesFeatureName = await rateFeatureTitle();
        expect(entryPagesFeatureName).to.contain('Entry pages');

        // switch to the related "Entry page titles" report
        await (await page.jQuery('.datatableRelatedReports span:contains(Entry page titles)')).click();
        await page.waitForNetworkIdle();

        // the help popup stays open across the reload and must now show the new report's help
        await page.waitForFunction(function () {
            const help = document.querySelector('.enrichedHeadline .inlineHelp');
            return help && help.innerText.indexOf('titles of entry pages') !== -1;
        });

        const entryPageTitlesHelp = await inlineHelpText();
        expect(entryPageTitlesHelp).to.not.equal(entryPagesHelp);
        expect(entryPageTitlesHelp).to.contain('titles of entry pages');

        // the rate-feature name must follow the report switch, not stay on the previous report
        const entryPageTitlesFeatureName = await rateFeatureTitle();
        expect(entryPageTitlesFeatureName).to.not.equal(entryPagesFeatureName);
        expect(entryPageTitlesFeatureName).to.contain('Entry page titles');
    });
});
