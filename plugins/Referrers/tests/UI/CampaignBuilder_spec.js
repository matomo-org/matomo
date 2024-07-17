/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CampaignBuilder", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var url = '?module=Widgetize&action=iframe&widget=1&moduleToWidgetize=Referrers&actionToWidgetize=getCampaignUrlBuilder&idSite=1&period=day&date=yesterday&disableLink=1&widget=1';

    before(function () {
        testEnvironment.pluginsToLoad = ['Referrers'];
        testEnvironment.save();
    });

    async function captureUrlBuilder(screenshotName, theTest)
    {
        await theTest();

        const element = await page.$('.campaignUrlBuilder');
        expect(await element.screenshot()).to.matchImage(screenshotName);
    }

    async function ensureHighlightEffectIsGone()
    {
        await page.waitForTimeout(2000);
    }

    async function generateUrl()
    {
        await page.click('.generateCampaignUrl');
        await ensureHighlightEffectIsGone();
    }

    it('should load the url builder', async function () {
        await page.goto(url);
        expect(await page.screenshot({ fullPage: true })).to.matchImage('loaded');
    });

    it('generate simple url with url and campaign name', async function () {
        await captureUrlBuilder('generate_url_nokeyword', async function () {
            await page.type('#websiteurl', 'https://www.example.com/foo/bar?x=1&y=2#foobarbaz');
            await page.type('#campaignname', 'My2018Campaign-Test');
            await generateUrl();
        });
    });

    it('can reset form', async function () {
        await captureUrlBuilder('generate_url_reset', async function () {
            await page.click('.resetCampaignUrl');
            await page.waitForTimeout(500); // wait to re-render
        });
    });

    it('generate simple url with url and campaign name and keyword', async function () {
        await captureUrlBuilder('generate_url_withkeyword', async function () {
            await page.type('#websiteurl', 'www.example.com');
            await page.type('#campaignname', 'MyAwesome&#2<&§Name');
            await page.type('#campaignkeyword', 'MyAwesome&#2<&§Keyword');
            await generateUrl();
        });
    });
});
