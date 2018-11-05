/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TwoFactorAuthUsersManager", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\TwoFactorAuth\\tests\\Fixtures\\TwoFactorFixture";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        usersManager = '?module=UsersManager&action=index&' + generalParams;

    before(function () {
        testEnvironment.pluginsToLoad = ['TwoFactorAuth'];
        testEnvironment.save();
    });


    function selectModalButton(page, button)
    {
        page.click('.modal.open .modal-footer a:contains('+button+')');
    }

    function captureModal(done, screenshotName, test, selector) {
        captureScreen(done, screenshotName, test, '.modal.open');
    }

    function captureScreen(done, screenshotName, test, selector) {
        if (!selector) {
            selector = '#content,#notificationContainer';
        }

        expect.screenshot(screenshotName).to.be.captureSelector(selector, test, done);
    }

    function captureModal(done, screenshotName, test, selector) {
        captureScreen(done, screenshotName, test, '.modal.open');
    }

    it('shows users with 2fa and not 2fa', function (done) {
        captureScreen(done, 'list', function (page) {
            page.load(usersManager);
            page.evaluate(function () {
                $('td#last_seen').html(''); // fix random test failure
            });
        });
    });

    it('menu should show 2fa tab', function (done) {
        captureScreen(done, 'edit_with_2fa', function (page) {
            page.setViewportSize(1250);
            page.click('#manageUsersTable #row2 .edituser');
            page.evaluate(function () {
                $('.userEditForm .menuUserTwoFa a').click();
            });
        });
    });

    it('should ask for confirmation before resetting 2fa', function (done) {
        captureModal(done, 'edit_with_2fa_reset_confirm', function (page) {
            page.click('.userEditForm .twofa-reset .resetTwoFa .btn');
        });
    });

    it('should be possible to confirm the reset', function (done) {
        captureScreen(done, 'edit_with_2fa_reset_confirmed', function (page) {
            page.click('.twofa-confirm-modal .modal-close:not(.modal-no)');
        });
    });

});