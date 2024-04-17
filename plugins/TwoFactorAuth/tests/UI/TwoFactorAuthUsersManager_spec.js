/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("TwoFactorAuthUsersManager", function () {
    this.fixture = "Piwik\\Plugins\\TwoFactorAuth\\tests\\Fixtures\\TwoFactorUsersManagerFixture";

    var generalParams = 'idSite=1&period=day&date=2010-01-03',
        usersManager = '?module=UsersManager&action=index&' + generalParams;

    before(function () {
        testEnvironment.pluginsToLoad = ['TwoFactorAuth'];
        testEnvironment.save();
    });

    it('shows users with 2fa and not 2fa', async function () {
        await page.goto(usersManager);
        await page.evaluate(function () {
            $('td#last_seen').html(''); // fix random test failure
        });
        expect(await page.screenshotSelector('#content,#notificationContainer')).to.matchImage('list');
    });

    it('menu should show 2fa tab', async function () {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
        await page.click('#manageUsersTable #row2 .edituser');
        await page.evaluate(function () {
            $('.userEditForm .menuUserTwoFa a').click();
        });
        await page.waitForTimeout(250);
        await page.waitForSelector('.twofa-reset > p', { visible: true });
        expect(await page.screenshotSelector('#content,#notificationContainer')).to.matchImage('edit_with_2fa');
    });

    it('should ask for confirmation before resetting 2fa', async function () {
        await page.click('.userEditForm .twofa-reset .resetTwoFa .btn');
        const modal = await page.waitForSelector('.modal.open', { visible: true });
        await page.waitForTimeout(1000); // animation
        expect(await modal.screenshot()).to.matchImage('edit_with_2fa_reset_confirm');
    });

    it('should be possible to confirm the reset', async function () {
        await page.type('.confirm-password-modal input[name=currentUserPassword]', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(100); // wait for modal to close
        expect(await page.screenshotSelector('#content,#notificationContainer')).to.matchImage('edit_with_2fa_reset_confirmed');
    });

});
