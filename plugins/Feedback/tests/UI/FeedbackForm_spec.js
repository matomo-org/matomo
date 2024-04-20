/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("FeedbackForm", function () {
    it('should load the feedback form when the feedback form link is clicked', async function() {
        await page.goto("?idSite=1&period=year&date=2012-08-09&module=Feedback&action=index");

        await page.evaluate(function () {
            $('.enrichedHeadline').each(function () {
                if ($(this).html().indexOf("Matomo") !== -1) {
                    var replace = $(this).html().replace(/Matomo\s*\d+\.\d+(\.\d+)?([\-a-z]*\d+)?/g, 'Matomo');
                    $(this).html(replace);
                }
            });
        });

        var pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('show');
    });
});
