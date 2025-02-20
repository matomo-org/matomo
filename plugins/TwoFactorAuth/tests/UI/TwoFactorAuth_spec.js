/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        await page.waitForTimeout(250);
        await page.waitForNetworkIdle();
    }

    async function loginUser(username, doAuth)
    {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.save();

        // make sure to log out previous session
        await page.goto(logoutUrl);
        await page.waitForSelector('.loginSection', {visible: true});

        var cookies = await page.cookies();
        cookies.forEach(cookie => {
            page.deleteCookie(cookie);
        });

        if (typeof doAuth === 'undefined') {
            doAuth = true;
        }
        var logMeUrl = '?module=Login&action=logme&login=' + username + '&password=240161a241087c28d92d8d7ff3b6186b';

        // avoid loading dashboard after login as it takes long
        logMeUrl += '&url=' + encodeURIComponent('index.php?category=General_Visitors&subcategory=Live_VisitorLog&' + generalParams);

        if (doAuth) {
            logMeUrl += '&authCode=123456'; // we make sure in test config this code always works
        }
        await page.goto(logMeUrl);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();

        await page.webpage.setViewport({
            width: 1350,
            height: 768,
        });
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
        testEnvironment.configOverride = { Development: { disable_merged_assets: 1 }};
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
        await page.waitForNetworkIdle();
        await page.type('.confirmPasswordForm #login_form_password', '123abcDk3_l3');
        await page.click('.confirmPasswordForm #login_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
    }

    it('a user with 2fa can open the widgetized view by token without needing to verify', async function () {
        await page.goto('?module=Widgetize&action=iframe&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&date=2018-03-04&token_auth=a4ca4238a0b923820dcc509a6f75849b&' + generalParams);

        // check if widget element is present, if login wouldn't work the login screen would be shown instead
        const widgetsCount = await page.evaluate(() => $('.widget').length);
        expect(widgetsCount).to.equal(1);
    });

    it('when logging in through logme and not providing auth code it should show auth code screen', async function () {
        await loginUser('with2FA', false);
        const section = await page.waitForSelector('.loginSection');
        expect(await section.screenshot()).to.matchImage('logme_not_verified');
    });

    it('when logging in and providing wrong code an error is shown', async function () {
        await page.type('.loginTwoFaForm #login_form_authcode', '555555');
        await page.evaluate(function(){
            document.querySelector('.loginTwoFaForm #login_form_submit').click();
        });
        await page.waitForNetworkIdle();
        const element = await page.waitForSelector('.loginSection');
        expect(await element.screenshot()).to.matchImage('logme_not_verified_wrong_code');
    });

    it('when logging in through logme and verifying screen it works to access ui', async function () {
        testEnvironment.overrideConfig('General', 'login_allow_logme', '1')
        testEnvironment.save();

        await page.type('.loginTwoFaForm #login_form_authcode', '123456');
        await page.click('.loginTwoFaForm #login_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget', { timeout: 60000 });
        await page.waitForNetworkIdle();

        // do not take a screenshot, as it's not relevant. We only check if there is the right amount of widgets loaded
        const widgetsCount = await page.evaluate(() => $('.widget').length);
        expect(widgetsCount).to.equal(9);
    });

    it('should show user settings when two-fa enabled', async function () {
        await loginUser('with2FA');
        await page.goto(userSettings);
        await page.waitForSelector('.userSettings2FA', { visible: true, timeout: 0 });
        await page.waitForTimeout(1000); // animation
        const elem = await page.$('.userSettings2FA');
        expect(await elem.screenshot()).to.matchImage('usersettings_twofa_enabled');
    });

    it('should be possible to show recovery codes step1 authentication', async function () {
        await page.click('.showRecoveryCodesLink');
        const element = await page.waitForSelector('.loginSection', {visible: true});
        await page.waitForTimeout(200);
        expect(await element.screenshot()).to.matchImage('show_recovery_codes_step1');
    });

    it('should be possible to show recovery codes step2 done', async function () {
        await confirmPassword();
        expect(await page.screenshotSelector('#content')).to.matchImage('show_recovery_codes_step2');
    });

    it('should show user settings when two-fa enabled', async function () {
        requireTwoFa();
        await page.goto(userSettings);
        await page.waitForSelector('.userSettings2FA', {visible: true});
        await page.waitForTimeout(200);
        expect(await page.screenshotSelector('.userSettings2FA')).to.matchImage('usersettings_twofa_enabled_required');
    });

    it('should be possible to disable two factor', async function () {
        await loginUser('with2FADisable');
        await page.goto(userSettings);
        await page.waitForSelector('.disable2FaLink', { timeout: 60000 });
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.click('.disable2FaLink');

        const modal = await page.$('.modal.open');
        await page.waitForTimeout(500); // animation
        expect(await modal.screenshot()).to.matchImage('usersettings_twofa_disable_step1');
    });

    it('should be possible to disable two factor step 2 confirmed', async function () {
        await selectModalButton('Yes');

        const element = await page.waitForSelector('.loginSection', {visible: true});
        await page.waitForTimeout(150);
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
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.click('.enable2FaLink');
        await confirmPassword();
        await page.waitForSelector('.setupTwoFactorAuthentication');
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
        await page.waitForTimeout(500);

        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step2');
    });

    it('should open the OTP codes in modal', async function () {
        await page.waitForTimeout(250);
        await page.click('.setupTwoFactorAuthentication .showOtpCodes');
        await page.waitForSelector('.modal.open', {visible: true});
        await page.waitForTimeout(500);

        // replace QR with an image
        await page.evaluate(function () {
          const qrCodeImg = $('#qrcode img');
          qrCodeImg.attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAIAQMAAAD+wSzIAAAABlBMVEX///+/v7+jQ3Y5AAAADklEQVQI12P4AIX8EAgALgAD/aNpbtEAAAAASUVORK5CYII');
          qrCodeImg.attr('width', 200);
          qrCodeImg.attr('height', 200);
        });

        await page.evaluate(() => $('.modal.open').css('max-height', '90%').css('top', '5%'));
        const modal = await page.waitForSelector('.modal.open', { visible: true });
        expect(await modal.screenshot()).to.matchImage('twofa_setup_step2_showcodes');
    });

    it('should show the manual OTP code in modal', async function () {
        const codeLength = await page.evaluate(() => {
            return $('.modal.open .text-code pre').text().length;
        });

        expect(codeLength).to.equal(16);
    });

    it('should not show step 3 if OTP codes modal just closed', async function () {
        selectModalButton('Cancel');
        await page.waitForTimeout(500);

        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step2');
    });

    it('should move to third step in setup - step 3', async function () {
        await page.waitForTimeout(250);
        await page.click('.setupTwoFactorAuthentication .showOtpCodes');
        await page.waitForSelector('.modal.open', {visible: true});

        selectModalButton('Continue');
        await page.waitForTimeout(250);

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
        await page.waitForSelector('#content', { visible: true, timeout: 60000 });
        await page.waitForNetworkIdle();
        const element = await page.$('#content');
        expect(await element.screenshot()).to.matchImage('twofa_setup_step4');
    });

    it('should force user to setup 2fa when not set up yet but enforced', async function () {
        requireTwoFa();
        await loginUser('no2FA', false, true);
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step1');
    });

    it('should force user to setup 2fa when not set up yet but enforced step 2', async function () {
        await (await page.jQuery('.setupTwoFactorAuthentication .backupRecoveryCode:first')).click();
        await page.click('.setupTwoFactorAuthentication .goToStep2');
        await page.waitForTimeout(100);
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.loginSection,#content,#notificationContainer')).to.matchImage('twofa_forced_step2');
    });

    it('should force user to setup 2fa when not set up yet but enforced step 2 show codes', async function () {
        await page.click('.setupTwoFactorAuthentication .showOtpCodes');
        await page.waitForTimeout(500);

        // replace QR with an image
        await page.evaluate(function () {
          const qrCodeImg = $('#qrcode img');
          qrCodeImg.attr('src', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAIAQMAAAD+wSzIAAAABlBMVEX///+/v7+jQ3Y5AAAADklEQVQI12P4AIX8EAgALgAD/aNpbtEAAAAASUVORK5CYII');
          qrCodeImg.attr('width', 200);
          qrCodeImg.attr('height', 200);
        });

        await page.evaluate(() => $('.modal.open').css('max-height', '90%').css('top', '5%'));
        const modal = await page.waitForSelector('.modal.open', { visible: true });
        expect(await modal.screenshot()).to.matchImage('twofa_forced_step2_showcodes');
    });

    it('should force user to setup 2fa when not set up yet but enforced step 3', async function () {
        selectModalButton('Continue');
        await page.waitForTimeout(250);

        await page.mouse.move(-10, -10);
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
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        // after setting up forced 2fa, the default page will be loaded (dashboard)
        const widgetsCount = await page.evaluate(() => $('.widget').length);
        expect(widgetsCount).to.equal(9);
    });

});
