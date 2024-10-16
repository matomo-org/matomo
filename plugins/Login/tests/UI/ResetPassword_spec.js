/*!
 * Matomo - free/libre analytics platform
 *
 * Reset password screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ResetPassword', function () {
    before(async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    async function readResetPasswordUrl() {
        const expectedMailOutputFile = PIWIK_INCLUDE_PATH + '/tmp/Login.resetPassword.mail.json',
              fileContents = require('fs').readFileSync(expectedMailOutputFile),
              mailSent = JSON.parse(fileContents);

        let resetUrl = mailSent.contents.match(/http:\/\/[^"]+resetToken[^"]+"/);

        if (!resetUrl || !resetUrl[0]) {
            throw new Error(`Could not find reset URL in email, captured mail info: ${fileContents}`)
        }
        resetUrl = resetUrl[0].replace(/"$/, '');
        resetUrl = await page.evaluate((resetUrl) => {
            return piwikHelper.htmlDecode(resetUrl);
        }, resetUrl);

        return resetUrl;
    }

    it('should display password reset form when forgot password link clicked', async function() {
        await page.goto('');
        await page.waitForNetworkIdle();
        await page.waitForSelector('a#login_form_nav');
        await page.click('a#login_form_nav');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('forgot_password');
    });

    it('should show reset password form and error message on error', async function() {
        await page.type('#reset_form_login', superUserLogin);
        await page.type('#reset_form_password', superUserPassword + '2');
        await page.click('#reset_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.notification');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_error');
    });

    it('should send email when password reset form submitted', async function() {
        await page.reload();
        await page.click('a#login_form_nav');
        await page.type('#reset_form_login', superUserLogin);
        await page.type('#reset_form_password', superUserPassword + '2');
        await page.type('#reset_form_password_bis', superUserPassword + '2');
        await page.click('#reset_form_submit');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset');
    });

    it('should show reset password confirmation page when password reset link is clicked', async function() {
        const resetUrl = await readResetPasswordUrl();

        await page.goto(resetUrl);
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_confirm');
    });

    it('should reset password when password reset link is clicked', async function() {
        await page.type('#mtmpasswordconfirm', superUserPassword + '2');
        await page.click('#login_reset_confirm');
        await page.waitForNetworkIdle();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_complete');
    });

    it('should login successfully when new credentials used', async function() {
        await page.type('#login_form_login', superUserLogin);
        await page.type('#login_form_password', superUserPassword + '2');
        await page.click('#login_form_submit');

        // check dashboard is shown
        await page.waitForNetworkIdle();
        await page.waitForSelector('#dashboard');
    });
});
