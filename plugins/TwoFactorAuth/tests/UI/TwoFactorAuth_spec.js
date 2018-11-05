/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TwoFactorAuth", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\TwoFactorAuth\\tests\\Fixtures\\TwoFactorFixture";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        userSettings = '?module=UsersManager&action=userSettings&' + generalParams,
        logoutUrl = '?module=Login&action=logout&period=day&date=yesterday';


    function selectModalButton(page, button)
    {
        page.click('.modal.open .modal-footer a:contains('+button+')');
    }

    function loginUser(page, username, doAuth)
    {
        // make sure to log out previous session
        page.load(logoutUrl);

        if (typeof doAuth === 'undefined') {
            doAuth = true;
        }
        var logMeUrl = '?module=Login&action=logme&login=' + username + '&password=240161a241087c28d92d8d7ff3b6186b'
        if (doAuth) {
            logMeUrl += '&authCode=123456'; // we make sure in test config this code always works
        }
        page.wait(1000);
        page.load(logMeUrl);
    }

    function requireTwoFa() {
        testEnvironment.requireTwoFa = 1;
        testEnvironment.save();
    }

    function fakeCorrectAuthCode() {
        testEnvironment.fakeCorrectAuthCode = 1;
        testEnvironment.save();
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['TwoFactorAuth'];
        testEnvironment.save();
    });

    beforeEach(function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.restoreRecoveryCodes = 1;
        testEnvironment.save();
    });

    afterEach(function () {
        delete testEnvironment.requireTwoFa;
        delete testEnvironment.restoreRecoveryCodes;
        delete testEnvironment.fakeCorrectAuthCode;
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    function confirmPassword(page)
    {
        page.wait(1000);
        page.sendKeys('.confirmPasswordForm #login_form_password', '123abcDk3_l3');
        page.click('.confirmPasswordForm #login_form_submit');
    }

    function captureScreen(done, screenshotName, test, selector) {
        if (!selector) {
            selector = '.loginSection,#content,#notificationContainer';
        }

        expect.screenshot(screenshotName).to.be.captureSelector(selector, test, done);
    }

    function captureUserSettings(done, screenshotName, test, selector) {
        captureScreen(done, screenshotName, test, '.userSettings2FA');
    }

    function captureModal(done, screenshotName, test, selector) {
        captureScreen(done, screenshotName, test, '.modal.open');
    }

    it('when logging in through logme and not providing auth code it should show auth code screen', function (done) {
        captureScreen(done, 'logme_not_verified', function (page) {
            loginUser(page, 'with2FA', false);
        });
    });

    it('when logging in and providing wrong code an error is shown', function (done) {
        captureScreen(done, 'logme_not_verified_wrong_code', function (page) {
            page.sendKeys('.loginTwoFaForm #login_form_authcode', '555555');
            page.click('.loginTwoFaForm #login_form_submit');
        });
    });

    it('when logging in through logme and verifying screen it works to access ui', function (done) {
        captureScreen(done, 'logme_verified', function (page) {
            page.sendKeys('.loginTwoFaForm #login_form_authcode', '123456');
            page.click('.loginTwoFaForm #login_form_submit');
        });
    });

    it('should show user settings when two-fa enabled', function (done) {
        captureUserSettings(done, 'usersettings_twofa_enabled', function (page) {
            loginUser(page, 'with2FA');
            page.load(userSettings);
        });
    });

    it('should be possible to show recovery codes step1 authentication', function (done) {
        captureScreen(done, 'show_recovery_codes_step1', function (page) {
            page.click('.showRecoveryCodesLink');
        });
    });
    it('should be possible to show recovery codes step2 done', function (done) {
        captureScreen(done, 'show_recovery_codes_step2', function (page) {
            confirmPassword(page);
        });
    });

    it('should show user settings when two-fa enabled', function (done) {
        captureUserSettings(done, 'usersettings_twofa_enabled_required', function (page) {
            requireTwoFa();
            page.load(userSettings);
        });
    });

    it('should be possible to disable two factor', function (done) {
        captureModal(done, 'usersettings_twofa_disable_step1', function (page) {
            loginUser(page, 'with2FADisable');
            page.load(userSettings);
            page.click('.disable2FaLink');
        });
    });

    it('should be possible to disable two factor confirmed', function (done) {
        captureScreen(done, 'usersettings_twofa_disable_step2', function (page) {
            selectModalButton(page, 'Yes');
        });
    });

    it('should be possible to disable two factor', function (done) {
        captureUserSettings(done, 'usersettings_twofa_disable_step3', function (page) {
            confirmPassword(page);
        });
    });

    it('should show setup screen - step 1', function (done) {
        captureScreen(done, 'twofa_setup_step1', function (page) {
            loginUser(page, 'without2FA');
            page.load(userSettings);
            page.click('.enable2FaLink');
            confirmPassword(page);
        });
    });

    it('should move to second step in setup - step 2', function (done) {
        captureScreen(done, 'twofa_setup_step2', function (page) {
            page.click('.setupTwoFactorAuthentication .backupRecoveryCode:first');
            page.click('.setupTwoFactorAuthentication .goToStep2');
        });
    });

    it('should move to third step in setup - step 3', function (done) {
        captureScreen(done, 'twofa_setup_step3', function (page) {
            page.click('.setupTwoFactorAuthentication .goToStep3');
        });
    });

    it('should move to third step in setup - step 4 confirm', function (done) {
        captureScreen(done, 'twofa_setup_step4', function (page) {
            fakeCorrectAuthCode();
            page.sendKeys('.setupConfirmAuthCodeForm input[type=text]', '123458');
            page.evaluate(function () {
                $('.setupConfirmAuthCodeForm input[type=text]').change();
            });
            page.click('.setupConfirmAuthCodeForm .confirmAuthCode');
        });
    });

    it('should force user to setup 2fa when not set up yet but enforced', function (done) {
        captureScreen(done, 'twofa_forced_step1', function (page) {
            requireTwoFa();
            loginUser(page, 'no2FA', false);
        });
    });

    it('should force user to setup 2fa when not set up yet but enforced step 2', function (done) {
        captureScreen(done, 'twofa_forced_step2', function (page) {
            page.click('.setupTwoFactorAuthentication .backupRecoveryCode:first');
            page.click('.setupTwoFactorAuthentication .goToStep2');
        });
    });

    it('should force user to setup 2fa when not set up yet but enforced step 3', function (done) {
        captureScreen(done, 'twofa_forced_step3', function (page) {
            page.click('.setupTwoFactorAuthentication .goToStep3');
        });
    });
    it('should force user to setup 2fa when not set up yet but enforced confirm code', function (done) {
        captureScreen(done, 'twofa_forced_step4', function (page) {
            requireTwoFa();
            fakeCorrectAuthCode();
            page.sendKeys('.setupConfirmAuthCodeForm input[type=text]', '123458');
            page.evaluate(function () {
                $('.setupConfirmAuthCodeForm input[type=text]').change();
            });
            page.click('.setupConfirmAuthCodeForm .confirmAuthCode');
        });
    });

});