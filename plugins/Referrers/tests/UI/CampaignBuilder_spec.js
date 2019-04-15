/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CampaignBuilder", function () {
    this.timeout(0);

    var url = '?module=Widgetize&action=iframe&widget=1&moduleToWidgetize=Referrers&actionToWidgetize=getCampaignUrlBuilder&idSite=1&period=day&date=yesterday&disableLink=1&widget=1';

    before(function () {
        testEnvironment.pluginsToLoad = ['Referrers'];
        testEnvironment.save();
    });

    function captureUrlBuilder(done, screenshotName, theTest)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('.campaignUrlBuilder', theTest, done);
    }

    function ensureHighlightEffectIsGone(page)
    {
        page.wait(2000);
    }

    function generateUrl(page)
    {
        page.click('.generateCampaignUrl');
        ensureHighlightEffectIsGone(page);
    }

    it('should load the url builder', function (done) {
        expect.screenshot('loaded').to.be.capture(function (page) {
            page.load(url);
        }, done);
    });

    it('generate simple url with url and campaign name', function (done) {
        captureUrlBuilder(done, 'generate_url_nokeyword', function (page) {
            page.sendKeys('#websiteurl', 'https://www.example.com/foo/bar?x=1&y=2#foobarbaz');
            page.sendKeys('#campaignname', 'My2018Campaign-Test');
            generateUrl(page);
        });
    });

    it('can reset form', function (done) {
        captureUrlBuilder(done, 'generate_url_reset', function (page) {
            page.click('.resetCampaignUrl');
        });
    });

    it('generate simple url with url and campaign name and keyword', function (done) {
        captureUrlBuilder(done, 'generate_url_withkeyword', function (page) {
            page.sendKeys('#websiteurl', 'www.example.com');
            page.sendKeys('#campaignname', 'MyAwesome&#2<&§Name');
            page.sendKeys('#campaignkeyword', 'MyAwesome&#2<&§Keyword');
            generateUrl(page);
        });
    });
});