/*!
 * Matomo - free/libre analytics platform
 *
 * Microsoft 365 SMTP password login warning tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("SmtpBasicAuthRetirementNotification", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\EmptySite";

    const pageUrl = '?module=CoreAdminHome&action=generalSettings&idSite=1&period=day&date=yesterday';
    const title = 'Microsoft 365 email authentication is changing';
    const microsoftUrl = 'https://techcommunity.microsoft.com/blog/exchange/updated-exchange-online-smtp-auth-basic-authentication-deprecation-timeline/4489835';

    async function getWarning() {
        return page.evaluate((title) => {
            const notification = Array.from(document.querySelectorAll('#notificationContainer .notification'))
                .find((element) => element.querySelector('strong')?.textContent.trim() === title);
            if (!notification) {
                return null;
            }

            const links = notification.querySelectorAll('.notification-body a');

            return {
                body: notification.querySelector('.notification-body').textContent,
                settingsLink: links[0]?.href,
                learnMoreLink: links[1]?.getAttribute('href'),
                learnMoreTarget: links[1]?.getAttribute('target'),
                hasCloseButton: !!notification.querySelector('button.close'),
            };
        }, title);
    }

    before(function () {
        // not a Microsoft server, as the warning goes to every SMTP password login
        testEnvironment.overrideConfig('mail', {
            transport: 'smtp',
            host: 'smtp.example.org',
            type: 'Login',
        });
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.configOverride.mail;
        testEnvironment.save();
    });

    it('should show a closable warning that links to the email settings and to Microsoft', async function () {
        await page.goto(pageUrl);
        await page.waitForNetworkIdle();

        const warning = await getWarning();

        expect(warning, 'the warning is not shown').to.not.be.null;
        expect(warning.body).to.contain('From the end of December 2026, Microsoft will disable password-based SMTP authentication by default.');
        expect(warning.hasCloseButton).to.be.true;

        const settingsLink = new URL(warning.settingsLink);
        expect(settingsLink.searchParams.get('module')).to.equal('CoreAdminHome');
        expect(settingsLink.searchParams.get('action')).to.equal('generalSettings');
        expect(settingsLink.hash).to.equal('#/mailSettings');

        expect(warning.learnMoreLink).to.equal(microsoftUrl);
        expect(warning.learnMoreTarget).to.equal('_blank');

        // the email settings the warning links to let the user switch to OAuth 2.0
        const mailTypes = await page.evaluate(() => Array.from(document.querySelectorAll('select[name="mailType"] option'))
            .map((option) => option.textContent.trim()));
        expect(mailTypes).to.include('OAuth 2.0');
    });

    it('should remove the warning when it is closed, and not show it again in the same session', async function () {
        await page.evaluate((title) => {
            Array.from(document.querySelectorAll('#notificationContainer .notification'))
                .find((element) => element.querySelector('strong')?.textContent.trim() === title)
                .querySelector('button.close')
                .click();
        }, title);
        // closing the warning marks it as read on the server
        await page.waitForNetworkIdle();

        expect(await getWarning(), 'the warning is still shown after closing it').to.be.null;

        await page.goto(pageUrl);
        await page.waitForNetworkIdle();

        expect(await getWarning(), 'the closed warning came back on the next page load').to.be.null;
    });
});
