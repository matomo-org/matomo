/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RateFeature", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = "?module=CoreHome&action=index&idSite=1&period=day&date=yesterday#?idSite=1&period=day&date=yesterday&segment=&category=General_Visitors&subcategory=General_Overview";

    before(async function() {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
    });

    it('should display the like feature popup', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        const like = await page.$('.like-icon');
        await like.evaluate(b => b.click());

        var modal = await page.waitForSelector('.modal.open', { visible: true });

        await page.waitForTimeout(1000);

        expect(await modal.screenshot()).to.matchImage('rate_feature_like_questions');
    });

    it('should accept like feedback', async function () {

        const useful = await page.$('#useful');
        await useful.evaluate(b => b.click());

        await page.type('#feedbacktext', 'test');

        const submit = await page.$('a.modal-action:nth-child(1)');
        await submit.click();
        await page.waitForNetworkIdle();

        var modal = await page.waitForSelector('.modal.open', { visible: true });
        expect(await modal.screenshot()).to.matchImage('rate_feature_like_submit');
    });

    it('should display the dislike feature popup', async function () {
        await page.goto(url);
        await page.waitForNetworkIdle();

        const like = await page.$('.dislike-icon');
        await like.evaluate(b => b.click());

        var modal = await page.waitForSelector('.modal.open', { visible: true });

        await page.waitForTimeout(1000);

        expect(await modal.screenshot()).to.matchImage('rate_feature_dislike_questions');
    });

    it('should accept dislike feedback', async function () {

        const useful = await page.$('#missingfeatures');
        await useful.evaluate(b => b.click());

        await page.type('#feedbacktext', 'test');

        const submit = await page.$('a.modal-action:nth-child(1)');
        await submit.click();
        await page.waitForNetworkIdle();

        var modal = await page.waitForSelector('.modal.open', { visible: true });
        expect(await modal.screenshot()).to.matchImage('rate_feature_dislike_submit');
    });

    function delay(interval) {
       return it('should delay', done =>
       {
          setTimeout(() => done(), interval)
       }).timeout(interval + 100);
    }


});
