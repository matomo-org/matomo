/*!
 * Matomo - free/libre analytics platform
 *
 * Reset password screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('ResetPassword', function () {
    const parentSuite = this;

    before(async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.save();
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    async function goToForgotPasswordPage() {
        await page.goto('');
        await page.waitForNetworkIdle();
        await page.waitForSelector('a#login_form_nav');
        await page.click('a#login_form_nav');
        await page.waitForNetworkIdle();
    }

    async function readLinkFromPasswordResetMail(action) {
        const expectedMailOutputFile = PIWIK_INCLUDE_PATH + '/tmp/Login.resetPassword.mail.json',
              fileContents = require('fs').readFileSync(expectedMailOutputFile),
              mailSent = JSON.parse(fileContents);

        let resetUrl = mailSent.contents.match(new RegExp('http://[^"]*' + action + '[^"]*"'));

        if (!resetUrl || !resetUrl[0]) {
            throw new Error(`Could not find ${action} URL in email, captured mail info: ${fileContents}`)
        }
        resetUrl = resetUrl[0].replace(/"$/, '');
        resetUrl = await page.evaluate((resetUrl) => {
            return piwikHelper.htmlDecode(resetUrl);
        }, resetUrl);

        return resetUrl;
    }

    async function requestPasswordReset() {
        await page.type('#reset_form_login', superUserLogin);
        await page.type('#reset_form_password', superUserPassword + '2');
        await page.type('#reset_form_password_bis', superUserPassword + '2');
        await page.click('#reset_form_submit');
        await page.waitForNetworkIdle();
    }

    it('should display password reset form when forgot password link clicked', async function () {
        await goToForgotPasswordPage();

        expect(await page.screenshot({ fullPage: true })).to.matchImage('forgot_password');
    });

    it('should show reset password form and error message on error', async function () {
        await goToForgotPasswordPage();

        await page.type('#reset_form_login', superUserLogin);
        await page.type('#reset_form_password', superUserPassword + '2');
        await page.click('#reset_form_submit');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.notification');

        expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_error');
    });

    describe('confirm password reset', function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        before(async function () {
            // make sure we are not logged in
            await page.clearCookies();
        });

        it('should send email when password reset form submitted', async function () {
            await goToForgotPasswordPage();
            await requestPasswordReset();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset');
        });

        it('should show reset password confirmation page when password reset link is clicked', async function () {
            const resetUrl = await readLinkFromPasswordResetMail('confirmResetPassword');

            await page.goto(resetUrl);
            await page.waitForNetworkIdle();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_confirm');
        });

        it('should reset password when password reset link is clicked', async function () {
            await page.type('#mtmpasswordconfirm', superUserPassword + '2');
            await page.click('#login_reset_confirm');
            await page.waitForNetworkIdle();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('password_reset_complete');
        });

        it('should show an error message if an outdated password reset token is used', async function () {
            const resetUrl = await readLinkFromPasswordResetMail('confirmResetPassword');

            await page.goto(resetUrl);
            await page.waitForNetworkIdle();

            const notification = await page.$('.notification-error .notification-body');
            const notificationText = await notification.getProperty('textContent');

            expect(notificationText).to.match(/The token is invalid or has expired/i);
        });

        it('should login successfully when new credentials used', async function () {
            await page.type('#login_form_login', superUserLogin);
            await page.type('#login_form_password', superUserPassword + '2');
            await page.click('#login_form_submit');

            // check dashboard is shown
            await page.waitForNetworkIdle();
            await page.waitForSelector('#dashboard');
        });
    });

    describe('password reset "was not me"', function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        before(async function () {
            // make sure we are not logged in
            await page.clearCookies();
        });

        it('should send email when password reset form submitted', async function () {
            await goToForgotPasswordPage();
            await requestPasswordReset();

            await page.waitForNetworkIdle();
            await page.waitForSelector('.message_container .message');

            const message = await page.$('.message_container .message');
            const messageText = await message.getProperty('textContent');

            expect(messageText).to.match(/Open the confirmation link sent to your e-mail inbox to confirm changing your password/i);
        });

        it('should show confirmation page when "was not me" link is clicked', async function () {
            const cancelUrl = await readLinkFromPasswordResetMail('cancelResetPassword');

            await page.goto(cancelUrl);
            await page.waitForNetworkIdle();

            expect(await page.screenshot({ fullPage: true })).to.matchImage('cancel');
        });

        it('should show an error message if an outdated password reset token is used', async function () {
            const cancelUrl = await readLinkFromPasswordResetMail('cancelResetPassword');

            await page.goto(cancelUrl);
            await page.waitForNetworkIdle();

            const notification = await page.$('.notification-error .notification-body');
            const notificationText = await notification.getProperty('textContent');

            expect(notificationText).to.match(/The token is invalid or has expired/i);
        });
    });
});
