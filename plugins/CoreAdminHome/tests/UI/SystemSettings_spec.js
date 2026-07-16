/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SystemSettings", function () {

    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    var baseUrl = '?module=CoreAdminHome&action=generalSettings';

    async function screenshotPageWrap() {
        const pageWrap = await page.$('.pageWrap');
        const screenshot = await pageWrap.screenshot();
        return screenshot;
    }

    async function setCnilPolicyEnforced(enforced) {
        if (enforced) {
            testEnvironment.overrideConfig('CnilPolicy', 'cnil_v1_policy_enabled', 1);
        } else {
            delete testEnvironment.configOverride.CnilPolicy;
        }
        await testEnvironment.save();
    }

    it('should load the system settings correctly', async function () {
        await page.goto(baseUrl);

        expect(await screenshotPageWrap()).to.matchImage('settings');
    });

    it('should display compliance info for policy controlled settings', async function () {
        await setCnilPolicyEnforced(true);
        await page.goto(baseUrl);
        await page.waitForNetworkIdle();
        await setCnilPolicyEnforced(false);

        expect(await screenshotPageWrap()).to.matchImage('compliance_info');
    });

    describe('mail settings password confirmation', function () {
        var smtpSaveButton = '[vue-entry="CoreAdminHome.SmtpSettings"] .matomo-save-button';

        async function openMailSettingsConfirmation() {
            // ensure no leftover modal/overlay from a previous interaction can swallow the click
            await page.waitForFunction(
                () => $('.modal.open').length === 0 && $('.modal-overlay').length === 0
            );
            await page.click(smtpSaveButton);
            await page.waitForSelector('.modal.open #currentUserPassword', { visible: true });
            await page.waitForTimeout(250);
        }

        it('should require password confirmation when saving the mail settings', async function () {
            await page.goto(baseUrl);
            await page.waitForNetworkIdle();

            await openMailSettingsConfirmation();

            const passwordFieldVisible = await page.evaluate(
                () => $('.modal.open #currentUserPassword:visible').length > 0
            );
            expect(passwordFieldVisible).to.equal(true);
        });

        it('should not save the mail settings when the confirmation is aborted', async function () {
            await (await page.jQuery('.confirm-password-modal .modal-close.modal-no:visible')).click();
            await page.waitForFunction(() => $('.modal.open').length === 0);

            const hasSuccessNotification = await page.evaluate(
                () => $('.notification-success').length > 0
            );
            expect(hasSuccessNotification).to.equal(false);
        });

        it('should not save the mail settings when the confirmed password is wrong', async function () {
            await openMailSettingsConfirmation();

            await page.type('.modal.open #currentUserPassword', 'wrongpassword');
            await (await page.jQuery('.confirm-password-modal .confirm-password-btn:visible')).click();
            await page.waitForNetworkIdle();

            await page.waitForSelector('.notification-error', { visible: true });

            const notificationHtml = await page.evaluate(
                () => $('.notification-error>div>div').html()
            );
            expect(notificationHtml).to.equal('The current password you entered is not correct.');
        });

        it('should save the mail settings when the correct password is confirmed', async function () {
            await page.evaluate(() => $('.notification-error .close').click());

            await openMailSettingsConfirmation();

            await page.type('.modal.open #currentUserPassword', superUserPassword);
            await (await page.jQuery('.confirm-password-modal .confirm-password-btn:visible')).click();
            await page.waitForNetworkIdle();

            await page.waitForSelector('.notification-success', { visible: true });

            const hasSuccessNotification = await page.evaluate(
                () => $('.notification-success').length > 0
            );
            expect(hasSuccessNotification).to.equal(true);
        });
    });
});
