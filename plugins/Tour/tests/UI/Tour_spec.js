/**!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("Tour", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    this.fixture = "Piwik\\Plugins\\Tour\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        widgetizeParams = "module=Widgetize&action=iframe";

    var widgetUrl = "?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Tour&actionToWidgetize=getEngagement";


    async function setCompleteAllChallenges()
    {
        testEnvironment.completeAllChallenges = 1;
        testEnvironment.save();
    }

    async function setCompleteNoChallenges()
    {
        testEnvironment.completeNoChallenge = 1;
        testEnvironment.save();
    }

    before(async function () {
        testEnvironment.pluginsToLoad = ['Tour'];
        testEnvironment.save();
    });

    afterEach(async function () {
        delete testEnvironment.completeAllChallenges;
        delete testEnvironment.completeNoChallenge;
        testEnvironment.save();
    });

    it('should load widget', async function () {
        await page.goto(widgetUrl);
        expect(await page.screenshot()).to.matchImage('widget_initial');
    });

    it('should skip goal step', async function () {
        await page.click('.tourChallenge.define_goal .icon-hide');
        expect(await page.screenshot()).to.matchImage('widget_skipped_goal');
    });

    it('should mark some challanges as completed', async function () {
        await page.goto('?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Actions&viewDataTable=table&flat=1&actionToWidgetize=getPageUrls&idSite=1&period=range&date=2018-01-02,2018-01-03&disableLink=1&widget=1&');
        await page.goto('?module=Widgetize&action=iframe&forceView=1&viewDataTable=VisitorLog&small=1&disableLink=0&widget=1&moduleToWidgetize=Live&actionToWidgetize=getLastVisitsDetails&idSite=1&period=day&date=yesterday&disableLink=1&widget=1');
        await page.goto('?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Live&actionToWidgetize=getVisitorProfilePopup&idSite=1&period=day&date=yesterday&disableLink=1&widget=1');
        await page.goto(widgetUrl);
        expect(await page.screenshot()).to.matchImage('widget_complete_some_challenges');
    });

    it('go to page 2', async function () {
        await page.evaluate(function () {
            $('.nextChallenges').click();
        });
        await page.waitForNetworkIdle();
        expect(await page.screenshot()).to.matchImage('widget_complete_some_challenges_page_2');
    });

    it('go to page 3', async function () {
        await page.evaluate(function () {
            $('.nextChallenges').click();
        });
        await page.waitForNetworkIdle();
        expect(await page.screenshot()).to.matchImage('widget_complete_some_challenges_page_3');
    });

    it('should load widget when all completed', async function () {
        await setCompleteAllChallenges();
        await page.goto(widgetUrl);
        expect(await page.screenshot()).to.matchImage('widget_all_completed');
    });

    it('should load widget when none completed', async function () {
        await setCompleteNoChallenges();
        await page.goto(widgetUrl);
        expect(await page.screenshot()).to.matchImage('widget_none_completed');
    });
});
