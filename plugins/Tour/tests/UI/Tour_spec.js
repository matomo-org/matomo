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
    before(function () {
        testEnvironment.pluginsToLoad = ['Tour'];
        testEnvironment.save();
    });

    it('should load widget', function (done) {
        expect.screenshot('widget_initial').to.be.capture(function (page) {
            page.load(widgetUrl);
        }, done);
    });

    it('should skip goal step', function (done) {
        expect.screenshot('widget_skipped_goal').to.be.capture(function (page) {
            page.click('.tourStep.define_goal .icon-hide');
        }, done);
    });

    it('should show complete message once all completed', function (done) {
        expect.screenshot('widget_completed').to.be.capture(function (page) {
            page.click('.tourStep.setup_branding .icon-hide');
            page.click('.tourStep.add_user .icon-hide');
            page.click('.tourStep.add_website .icon-hide');
            page.load(widgetUrl);
        }, done);
    });

});