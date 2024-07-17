/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for the DBStats plugin.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Diagnostics", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    const url = "?module=Installation&action=systemCheckPage&idSite=1&period=day&date=yesterday";

    it("should load correctly", async function() {
        await page.goto(url);

        const content = await page.$('#content');
        await page.evaluate((directory) => {
            $('#systemCheckInformational td').each(function () {
                let html = $(this).html();
                html = html.replace(/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/g, 'DATETIME');
                html = html.replaceAll(directory, '/path/matomo/');
                html = html.replaceAll(directory.replaceAll('/', '\\/'), '\\/path\\/matomo');
                $(this).html(html);
            });
            // replace varying invalidation counts with 0
            $('#systemCheckInformational td:contains(nvalidation) + td').each(function () {
                let text = $(this).text();
                if (text.match(/^[0-9]+$/)) {
                  $(this).html($(this).html().replace(/[0-9]+/, '0'));
                }
            });

        }, PIWIK_INCLUDE_PATH);
        expect(await content.screenshot()).to.matchImage('page');
    });
});
