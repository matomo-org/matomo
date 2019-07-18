/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("FeedbackForm", function () {
    it('should load the feedback form when the feedback form link is clicked', async function() {
        await page.goto("?idSite=1&period=year&date=2012-08-09&module=Feedback&action=index");

        await page.evaluate(function () {
            $('.enrichedHeadline .title').each(function () {
                if ($(this).text().indexOf("Matomo") !== -1) {
                    var replace = $(this).text().replace(/Matomo\s*\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, 'Matomo');
                    $(this).text(replace);
                }
            });
        });

        var pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('show');
    });
});