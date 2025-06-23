/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UserSettings", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsersPastDate";

    var userSettingsUrl = "?module=UsersManager&action=userSettings";
    var userSecurityUrl = "?module=UsersManager&action=userSecurity";

    before(async function() {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
    });

    it('should show user security page', async function () {
        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
            $('table.listAuthTokens th').css('width', '16%'); // five columns + actions
        });
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load_security');
    });

    it('should ask for password when trying to add token', async function () {
        await page.click('.addNewToken');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.loginSection');
        expect(await page.screenshotSelector('.loginSection')).to.matchImage('add_token_check_password');
    });

    it('should accept correct password', async function () {
        await page.type('#login_form_password', superUserPassword);
        await page.click('#login_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.addTokenForm');
        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token');
    });

    it('should create new token with default expiration date', async function () {
        await page.type('.addTokenForm input[id=description]', 'test description<img src=j&#X41vascript:alert("xss fail")>');
        await page.click('.addTokenForm .btn');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token_success');
    });

    it('should show new token with default expire date on security page', async function () {
        await page.click('[vue-entry="UsersManager.AddNewTokenSuccess"] .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
            $('table.listAuthTokens th').css('width', '16%'); // five columns + actions
        });
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load_security_new_token');
    });

    it('should not ask for password when trying to add a second token in quick succession', async function () {
        testEnvironment.overrideConfig('General', 'auth_token_default_expiration_days', 90);
        testEnvironment.save();

        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.click('.addNewToken');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.addTokenForm');
        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token_no_password');
    });

    it('should show a date picker with a shorter configured expire interval when clicked into the date field', async function () {
        await page.click('[name="token_expire_date"]');
        await page.waitForSelector('.ui-datepicker');

        if (testEnvironment.configOverride.General &&
          testEnvironment.configOverride.General.auth_token_default_expiration_days
        ) {
            delete testEnvironment.configOverride.General.auth_token_default_expiration_days;
            testEnvironment.save();
        }

        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token_show_calendar');
    });

    it('should create new token without expiration date', async function () {
        await page.type('.addTokenForm input[id=description]', 'no expiration token');
        await page.click('.addTokenForm #has_expiration');
        await page.click('.addTokenForm .btn');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.admin')).to.matchImage('add_token_no_expiration_success');
    });

    it('should show new token without expire date on security page', async function () {
        await page.goto(userSecurityUrl);
        await page.waitForSelector('.listAuthTokens', { visible: true });
        await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
            $('table.listAuthTokens th').css('width', '16%'); // five columns + actions
        });
        await page.waitForTimeout(100);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load_security_new_token_no_expiration');
    });

    it('should delete all tokens without password confirmation right after one was created', async function () {
        await page.click('button.delete-all-tokens');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(200);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load_security_no_tokens');
    });

    it('should show user settings page', async function () {
        await page.goto(userSettingsUrl);
        expect(await page.screenshotSelector('.admin')).to.matchImage('load');
    });

    it('should allow user to subscribe to newsletter', async function () {
        await page.click('#newsletterSignupCheckbox input');
        await page.click('#newsletterSignupBtn input');
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector('.pageWrap')).to.matchImage('signup_success');
    });

    it('should not prompt user to subscribe to newsletter again', async function () {
        // Assumes previous test has clicked on the signup button - so we shouldn't see it this time
        await page.goto(userSettingsUrl);
        expect(await page.screenshotSelector('.admin')).to.matchImage('already_signed_up');
    });

    it('should ask for password confirmation when changing email', async function () {
        await page.evaluate(function () {
            $('#userSettingsTable input#email').val('testlogin123@example.com').change();
        });
        await page.waitForTimeout(100);
        await page.click('#userSettingsTable .matomo-save-button .btn');
        await page.waitForTimeout(500); // wait for animation

        let pageWrap = await page.$('.modal.open');
        expect(await pageWrap.screenshot()).to.matchImage('asks_confirmation');
    });

    it('should load error when wrong password specified', async function () {
        await page.type('.modal.open #currentUserPassword', 'foobartest123');
        btnNo = await page.jQuery('.modal.open .modal-action:not(.modal-no)');
        await btnNo.click();
        await page.waitForNetworkIdle();

        let pageWrap = await page.$('#notificationContainer');
        expect(await pageWrap.screenshot()).to.matchImage('wrong_password_confirmed');
    });

    it('should not allow to set the current password as new password', async function () {
        await page.goto(userSecurityUrl);
        await page.type('#password', superUserPassword);
        await page.type('#passwordBis', superUserPassword);
        await page.type('#passwordConfirmation', superUserPassword);
        await page.click('#userSettingsTable .btn');
        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reuse');
    });
});
