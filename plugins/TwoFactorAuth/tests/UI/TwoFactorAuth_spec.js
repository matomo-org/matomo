/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TwoFactorAuth", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\TwoFactorAuth\\tests\\Fixtures\\TwoFactorFixture";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        userSettings = '?module=UsersManager&action=userSecurity&' + generalParams,
        logoutUrl = '?module=Login&action=logout&period=day&date=yesterday';


    async function selectModalButton(button)
    {
        await (await page.jQuery('.modal.open .modal-footer a:contains('+button+')')).click();
        await page.waitForNetworkIdle();
    }

    async function loginUser(username, doAuth)
    {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.save();

        // make sure to log out previous session
        await page.goto(logoutUrl);

        var cookies = await page.cookies();
        cookies.forEach(cookie => {
            page.deleteCookie(cookie);
        });

        if (typeof doAuth === 'undefined') {
            doAuth = true;
        }
        var logMeUrl = '?module=Login&action=logme&login=' + username + '&password=240161a241087c28d92d8d7ff3b6186b';
        if (doAuth) {
            logMeUrl += '&authCode=123456'; // we make sure in test config this code always works
        }
        await page.waitForTimeout(1000);
        await page.goto(logMeUrl);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000);
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
        testEnvironment.queryParamOverride = { date: '2018-03-04' };
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
        delete testEnvironment.configOverride;
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    async function confirmPassword()
    {
        await page.waitForSelector('.confirmPasswordForm');
        await page.waitFor(() => !!window.$);
        await page.evaluate(function(){
            $('.confirmPasswordForm #login_form_password').val('123abcDk3_l3');
            $('.confirmPasswordForm #login_form_submit').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);
    }

    it('a user with 2fa can open the widgetized view by token without needing to verify', async function () {
        await page.goto('?module=Widgetize&action=iframe&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&date=2018-03-04&token_auth=a4ca4238a0b923820dcc509a6f75849b&' + generalParams);
        const element = await page.$('.widget');
        expect(await element.screenshot()).to.matchImage('widgetized_no_verify');
    });

    it('when logging in through logme and not providing auth code it should show auth code screen', async function () {
        await loginUser('with2FA', false);
        await page.waitForTimeout(1000);
        const section = await page.$('.loginSection');
        expect(await section.screenshot()).to.matchImage('logme_not_verified');
    });

    it('when logging in and providing wrong code an error is shown', async function () {
        await page.type('.loginTwoFaForm #login_form_authcode', '555555');
        await page.evaluate(function(){
            document.querySelector('.loginTwoFaForm #login_form_submit').click();
        });
        await page.waitForNetworkIdle();
        const element = await page.$('.loginSection');
        expect(await element.screenshot()).to.matchImage('logme_not_verified_wrong_code');
    });

    it('when logging in through logme and verifying screen it works to access ui', async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.save();

        await page.type('.loginTwoFaForm #login_form_authcode', '123456');
        await page.waitFor(() => !!window.$);
        await page.evaluate(function(){
            document.querySelector('.loginTwoFaForm #login_form_submit').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        const element = await page.$('.pageWrap');
        expect(await element.screenshot()).to.matchImage('logme_verified');
    });

    it('should show user settings when two-fa enabled', async function () {
        await loginUser('with2FA');
        await page.goto(userSettings);
        await page.waitForSelector('.userSettings2FA', { visible: true, timeout: 0 });
        await page.waitForTimeout(750); // animation
        const elem = await page.$('.userSettings2FA');
        expect(await elem.screenshot()).to.matchImage('usersettings_twofa_enabled');
    });

    it('should be possible to show recovery codes step1 authentication', async function () {
        await page.click('.showRecoveryCodesLink');
        await page.waitForNetworkIdle();
        const element = await page.$('.loginSection');
        expect(await element.screenshot()).to.matchImage('show_recovery_codes_step1');
    });

    it('should be possible to show recovery codes step2 done', async function () {
        await confirmPassword();
        await page.waitForNetworkIdle();
        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('show_recovery_codes_step2');
    });

    it('should show user settings when two-fa enabled', async function () {
        requireTwoFa();
        await page.goto(userSettings);
        const element = await page.$('.userSettings2FA');
        expect(await element.screenshot()).to.matchImage('usersettings_twofa_enabled_required');
    });

    it('should be possible to disable two factor', async function () {
        await loginUser('with2FADisable');
        await page.goto(userSettings);
        await page.click('.disable2FaLink');

        const modal = await page.$('.modal.open');
        await page.waitForTimeout(250); // animation
        expect(await modal.screenshot()).to.matchImage('usersettings_twofa_disable_step1');
    });

    it('should be possible to disable two factor step 2 confirmed', async function () {
        await selectModalButton('Yes');
        await page.waitForTimeout(150);

        const element = await page.$('.loginSection');
        expect(await element.screenshot()).to.matchImage('usersettings_twofa_disable_step2');
    });

    it('should be possible to disable two factor step 3 verified', async function () {
        await confirmPassword();
        await page.waitForSelector('.userSettings2FA');
        const elem = await page.$('.userSettings2FA');
        expect(await elem.screenshot()).to.matchImage('usersettings_twofa_disable_step3');
    });

    it('should show setup screen - step 1', async function () {
        await loginUser('without2FA');
        await page.goto(userSettings);
        await page.click('.enable2FaLink');
        await confirmPassword();
        await page.waitForTimeout(1000);
        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step1');
    });

    it('should move to second step in setup - step 2', async function () {
        await page.evaluate(function(){
            $('.setupTwoFactorAuthentication .backupRecoveryCode:first').click();
        });
        await page.waitForNetworkIdle();
        await page.click('.setupTwoFactorAuthentication .goToStep2');
        await page.waitForNetworkIdle();
        await page.evaluate(function () {
            $('#qrcode').hide();
        });
        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step2');
    });

    it('should move to third step in setup - step 3', async function () {
        await page.click('.setupTwoFactorAuthentication .goToStep3');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000);

        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step3');
    });

    it('should move to third step in setup - step 4 confirm', async function () {
        fakeCorrectAuthCode();
        await page.type('.setupConfirmAuthCodeForm input[type=text]', '123458');
        await page.evaluate(function () {
            $('.setupConfirmAuthCodeForm input[type=text]').change();
        });
        await page.evaluate(function () {
            $('.setupConfirmAuthCodeForm .confirmAuthCode').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('#content', { visible: true });
        await page.waitForNetworkIdle();
        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step4');
    });

    it('should force user to setup 2fa when not set up yet but enforced', async function () {
        requireTwoFa();
        await loginUser('no2FA', false);
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step1');
    });

    it('should force user to setup 2fa when not set up yet but enforced step 2', async function () {
        await (await page.jQuery('.setupTwoFactorAuthentication .backupRecoveryCode:first')).click();
        await page.click('.setupTwoFactorAuthentication .goToStep2');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step2');
    });

    it('should force user to setup 2fa when not set up yet but enforced step 3', async function () {
        await page.click('.setupTwoFactorAuthentication .goToStep3');
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step3');
    });

    it('should force user to setup 2fa when not set up yet but enforced confirm code', async function () {
        requireTwoFa();
        fakeCorrectAuthCode();
        await page.type('.setupConfirmAuthCodeForm input[type=text]', '123458');
        await page.evaluate(function () {
            $('.setupConfirmAuthCodeForm input[type=text]').change();
        });
        await page.evaluate(function () {
            $('.setupConfirmAuthCodeForm .confirmAuthCode').click();
        });
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step4');
    });

});