/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("OneClickUpdate", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Tests\\Fixtures\\LatestStableInstall";

    var latestStableUrl = config.piwikUrl + '/latestStableInstall/index.php';
    var settingsUrl = latestStableUrl + '?module=CoreAdminHome&action=home&idSite=1&period=day&date=yesterday';

    it('should show the new version available button in the admin screen', function (done) {
        expect.screenshot('latest_version_available').to.be.captureSelector('#header_message', function (page) {
            // login
            page.load(latestStableUrl);
            page.sendKeys('#login_form_login', 'superUserLogin');
            page.sendKeys('#login_form_password', 'superUserPass');
            page.click('#login_form_submit');

            // go to settings page
            page.load(settingsUrl);
        }, done);
    });

    it('should show the one click update screen when the update button is clicked', function (done) {
        expect.screenshot('update_screen').to.be.capture(function (page) {
            page.click('#header_message');
        }, done);
    });

    it('should fail to automatically update when trying to update over https fails', function (done) {
        expect.screenshot('update_fail').to.be.capture(function (page) {
            page.click('#updateAutomatically');
        }, done);
    });

    it('should update successfully and show the finished update screen', function (done) {
        expect.screenshot('update_success').to.be.capture(function (page) {
            page.click('#updateUsingHttp');
            page.wait(3000);
        }, done);
    });

    it('should login successfully after the update', function (done) {
        expect.screenshot('login').to.be.captureSelector('.pageWrap', function (page) {
            page.click('.footer a');
        }, done);
    });

    it('should have a working cron archiving process', function (done) {
        // track one action
        var trackerUrl = path.join(config.piwikUrl, "tests/PHPUnit/proxy/piwik.php?");
        testEnvironment.request(trackerUrl, {
            idsite: 1,
            url: 'http://piwik.net/test/url',
            action_name: 'test page',
        }, onTrackingDone);

        // run cron archiving
        function onTrackingDone() {
            testEnvironment.executeConsoleCommand('core:archive', [], onCronArchivingDone);
        }

        function onCronArchivingDone(code, output) {
            console.log(code);
            console.log(output);
            done();
        }
    });
});
