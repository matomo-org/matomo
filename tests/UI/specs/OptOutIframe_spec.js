/*!
 * Matomo - free/libre analytics platform
 *
 * Opt-out form tests
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("OptOutIframe", function () {
    const siteUrl = "/tests/resources/overlay-test-site-real/opt-out.php?implementation=iframe",
        safariUserAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A",
        chromeUserAgent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36";

    function expandIframe() {
        return page.evaluate(() => {
            const $iframe = $('iframe#optOutIframe');
            $iframe.contents().find('#textError_https').hide();
            $iframe.width(350);
            $iframe.height($iframe.contents().outerHeight());
        });
    }

    after(async () => {
        await page.setUserAgent(page.originalUserAgent);
    });

    it("should display correctly when embedded in another site", async function () {
        await page.clearCookies();

        page.setUserAgent(chromeUserAgent);
        await page.goto(siteUrl);

        await expandIframe();

        const element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('loaded');
    });

    it("should reload the iframe when clicking the opt out checkbox and display an empty checkbox", async function () {
        await page.evaluate(function () {
            $('iframe#optOutIframe').contents().find('input#trackVisits').click();
        });

        await page.waitForTimeout(5000); // opt out iframe creates a new page, so we can't wait on it that easily
        await page.waitForNetworkIdle(); // safety

        await expandIframe();

        const element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('opted-out');
    });

    it("should correctly show the checkbox unchecked after reloading after opting-out", async function () {
        page.setUserAgent(chromeUserAgent);
        await page.goto(siteUrl);

        await expandIframe();

        const element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('opted-out_reloaded');
    });

    it("using opt out twice should work correctly", async function () {
        page.setUserAgent(chromeUserAgent);
        await page.goto(siteUrl);

        await page.evaluate(function () {
            $('iframe#optOutIframe').contents().find('input#trackVisits').click();
        });

        await page.waitForTimeout(5000);

        await expandIframe();

        // check the box has opted in state after clicking once
        var element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('clicked_once');

        await page.evaluate(function () {
            $('iframe#optOutIframe').contents().find('input#trackVisits').click();
        });

        await page.waitForTimeout(5000);

        // check the box has outed out state after click another time
        await page.reload();

        await expandIframe();

        var element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('clicked_twice');
    });

    it("should correctly show display opted-in form when cookies are cleared", async function () {
        await page.clearCookies();

        page.setUserAgent(safariUserAgent);
        await page.goto(siteUrl);

        await expandIframe();

        const element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('safari-loaded');
    });

    it("should correctly set opt-out cookie on safari", async function () {
        await page.evaluate(function () {
            $('iframe#optOutIframe').contents().find('input#trackVisits').click();
        });

        await page.waitForTimeout(5000); // opt out iframe creates a new page, so we can't wait on it that easily
        await page.waitForNetworkIdle(); // safety

        await page.goto(siteUrl); // reload to check that cookie was set

        await expandIframe();

        const element = await page.jQuery('iframe#optOutIframe');
        expect(await element.screenshot()).to.matchImage('safari-opted-out');
    });
});
