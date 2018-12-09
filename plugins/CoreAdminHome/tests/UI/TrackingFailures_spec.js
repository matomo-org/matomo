/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TrackingFailures", function () {
    this.timeout(0);

    this.fixture = 'Piwik\\Tests\\Fixtures\\InvalidVisits';

    var manageUrl = '?module=CoreAdminHome&action=trackingFailures&idSite=1&period=day&date=today';
    var widgetUrl = '?module=Widgetize&action=iframe&moduleToWidgetize=CoreAdminHome&actionToWidgetize=getTrackingFailures&idSite=1&period=day&date=today&widget=1';

    function captureScreen(done, screenshotName, theTest)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('.matomoTrackingFailures', theTest, done);
    }

    function captureModal(done, screenshotName, theTest)
    {
        expect.screenshot(screenshotName).to.be.captureSelector('.modal.open', theTest, done);
    }

    function generateTrackingFailures()
    {
        testEnvironment.generateTrackingFailures = 1;
        testEnvironment.save();
    }

    function confirmModal(page)
    {
        page.click('.modal.open .modal-footer a:contains(Yes)');
    }

    afterEach(function () {
        delete testEnvironment.generateTrackingFailures;
        testEnvironment.save();
    });

    it('should show widget with no failures', function (done) {
        captureScreen(done, 'widget_no_failures', function (page) {
            page.load(widgetUrl);
        });
    });

    it('should show manage page with no failures', function (done) {
        captureScreen(done, 'manage_no_failures', function (page) {
            page.load(manageUrl);
        });
    });

    it('should show widget with failures', function (done) {
        generateTrackingFailures();
        captureScreen(done, 'widget_with_failures', function (page) {
            generateTrackingFailures();
            page.load(widgetUrl);
        });
    });

    it('should show manage page with failures', function (done) {
        generateTrackingFailures();
        captureScreen(done, 'manage_with_failures', function (page) {
            generateTrackingFailures();
            page.load(manageUrl);
        });
    });

    it('should show ask to confirm delete one', function (done) {
        captureModal(done, 'manage_with_failures_delete_one_ask_confirmation', function (page) {
            page.evaluate(function () {
                $('.matomoTrackingFailures table tbody tr:nth-child(2) .icon-delete').click()
            });
        });
    });

    it('should show delete when confirmed', function (done) {
        captureScreen(done, 'manage_with_failures_delete_one_confirmed', function (page) {
            confirmModal(page);
        });
    });

    it('should show ask to confirm delete all', function (done) {
        captureModal(done, 'manage_with_failures_delete_all_ask_confirmation', function (page) {
            page.click('.matomoTrackingFailures .deleteAllFailures');
        });
    });

    it('should show ask to confirm delete one', function (done) {
        captureScreen(done, 'manage_with_failures_delete_all_confirmed', function (page) {
            confirmModal(page);
        });
    });

});