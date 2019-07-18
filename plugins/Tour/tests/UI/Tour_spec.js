/**!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
describe("Tour", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\Tour\\tests\\Fixtures\\SimpleFixtureTrackFewVisits";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        widgetizeParams = "module=Widgetize&action=iframe";

    var widgetUrl = "?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Tour&actionToWidgetize=getEngagement";


    function setCompleteAllChallenges()
    {
        testEnvironment.completeAllChallenges = 1;
        testEnvironment.save();
    }

    function setCompleteNoChallenges()
    {
        testEnvironment.completeNoChallenge = 1;
        testEnvironment.save();
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['Tour'];
        testEnvironment.save();
    });

    afterEach(function () {
        delete testEnvironment.completeAllChallenges;
        delete testEnvironment.completeNoChallenge;
        testEnvironment.save();
    });

    it('should load widget', function (done) {
        expect.screenshot('widget_initial').to.be.capture(function (page) {
            page.load(widgetUrl);
        }, done);
    });

    it('should skip goal step', function (done) {
        expect.screenshot('widget_skipped_goal').to.be.capture(function (page) {
            page.click('.tourChallenge.define_goal .icon-hide');
        }, done);
    });

    it('should mark some challanges as completed', function (done) {
        expect.screenshot('widget_complete_some_challenges').to.be.capture(function (page) {
            page.load('?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Actions&viewDataTable=table&flat=1&actionToWidgetize=getPageUrls&idSite=1&period=range&date=2018-01-02,2018-01-03&disableLink=1&widget=1&');
            page.load('?module=Widgetize&action=iframe&forceView=1&viewDataTable=VisitorLog&small=1&disableLink=0&widget=1&moduleToWidgetize=Live&actionToWidgetize=getLastVisitsDetails&idSite=1&period=day&date=yesterday&disableLink=1&widget=1');
            page.load('?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Live&actionToWidgetize=getVisitorProfilePopup&idSite=1&period=day&date=yesterday&disableLink=1&widget=1');
            page.load(widgetUrl);
        }, done);
    });

    it('go to page 2', function (done) {
        expect.screenshot('widget_complete_some_challenges_page_2').to.be.capture(function (page) {
            page.click('.nextChallenges');
        }, done);
    });

    it('go to page 3', function (done) {
        expect.screenshot('widget_complete_some_challenges_page_3').to.be.capture(function (page) {
            page.click('.nextChallenges');
        }, done);
    });

    it('should load widget when all completed', function (done) {
        expect.screenshot('widget_all_completed').to.be.capture(function (page) {
            setCompleteAllChallenges();
            page.load(widgetUrl);
        }, done);
    });

    it('should load widget when none completed', function (done) {
        expect.screenshot('widget_none_completed').to.be.capture(function (page) {
            setCompleteNoChallenges();
            page.load(widgetUrl);
        }, done);
    });
});