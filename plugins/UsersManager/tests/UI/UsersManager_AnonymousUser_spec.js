/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe('UsersManager_AnonymousUser', function () {
  this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\AnonymousUser";

  const url = "?module=UsersManager&action=index";

  function getUserAccess(userName) {
    return page.evaluate(
      (userName) =>  {
        return $(`#manageUsersTable #userLogin:contains(${userName}) + .access-cell li[role="option"][aria-selected="true"]`)
          .text()
          .trim()
      },
      userName
    );
  }

  async function abortPasswordConfirmation() {
    await page.$('.confirm-password-modal.open', { visible: true });
    await page.waitForTimeout(300);
    await (await page.jQuery('.confirm-password-modal.open .modal-close.modal-no:visible')).click();
    await page.$('.confirm-password-modal.open', { hidden: true });
    await page.waitForTimeout(300);
  }

  async function confirmPassword() {
    await page.$('.confirm-password-modal.open', { visible: true });
    await page.waitForTimeout(300);

    await page.evaluate((superUserPassword) => {
      $('.confirm-password-modal input[name=currentUserPassword]:visible')
        .val(superUserPassword)
        .change();
    }, superUserPassword);

    await page.waitForTimeout(250);
    await (await page.jQuery('.confirm-password-modal.open .modal-close:not(.modal-no):visible')).click();
    await page.$('.confirm-password-modal.open', { hidden: true });
    await page.waitForTimeout(300);
    await page.waitForNetworkIdle();
  }

  async function confirmRoleChange() {
    await page.$('.change-user-role-confirm-modal.open', { visible: true });
    await page.waitForTimeout(300);
    await (await page.jQuery('.change-user-role-confirm-modal.open .modal-close:not(.modal-no):visible')).click();
    await page.$('.change-user-role-confirm-modal.open', { hidden: true });
    await page.waitForTimeout(300);
    await page.waitForNetworkIdle();
  }

  it('should start with a known list of users', async function () {
    await page.goto(url);
    await page.waitForNetworkIdle();

    expect(await getUserAccess('anonymous')).to.equal('No access');
    expect(await getUserAccess('regularUser')).to.equal('No access');
  });

  describe('single user handling', function () {
    async function setUserAccess(userName, accessString) {
      await page.evaluate(
        (userName, accessString) => {
          $(`#manageUsersTable #userLogin:contains(${userName}) + .access-cell select`)
            .val(accessString)
            .change();
        },
        userName, accessString
      )
    }

    it('should reset selected access if confirmation is aborted', async function () {
      await setUserAccess('anonymous', 'string:view');
      await abortPasswordConfirmation();

      expect(await getUserAccess('anonymous')).to.equal('No access');
    });

    it('should show a password confirmation when giving access to anonymous user', async function () {
      await setUserAccess('anonymous', 'string:view');
      await confirmPassword();

      expect(await getUserAccess('anonymous')).to.equal('View');
    });

    it('should not show a password confirmation when revoking access for anonymous user', async function () {
      await setUserAccess('anonymous', 'string:noaccess');
      await confirmRoleChange();

      expect(await getUserAccess('anonymous')).to.equal('No access');
    });
  });

  describe('bulk user handling', function() {
    async function bulkRemovePermissions() {
      await page.click('.bulk-actions.btn');
      await page.waitForTimeout(350);
      await (await page.jQuery('#user-list-bulk-actions a:contains(Remove Permissions)')).click();
      await page.waitForTimeout(350);
    }

    async function bulkSelectAll() {
      await page.click('th.select-cell input + span');
    }

    async function bulkSetViewAccess() {
      await page.click('.bulk-actions.btn');
      await page.waitForTimeout(350);
      await (await page.jQuery('a[data-target=bulk-set-access]')).hover();
      await page.waitForTimeout(350);
      await (await page.jQuery('#bulk-set-access a:contains(View)')).click();
      await page.waitForTimeout(350);
    }

    it('should reset selected access if confirmation is aborted', async function () {
      await bulkSelectAll();
      await bulkSetViewAccess();
      await abortPasswordConfirmation();

      expect(await getUserAccess('anonymous')).to.equal('No access');
      expect(await getUserAccess('regularUser')).to.equal('No access');
    });

    it('should show a password confirmation giving access to multiple users including anonymous', async function () {
      // all users already selected from previous test
      await bulkSetViewAccess();
      await confirmPassword();

      expect(await getUserAccess('anonymous')).to.equal('View');
      expect(await getUserAccess('regularUser')).to.equal('View');
    });

    it('should not show a password confirmation when revoking access for multiple users including anonymous', async function () {
      await bulkSelectAll();
      await bulkRemovePermissions();
      await confirmRoleChange();

      expect(await getUserAccess('anonymous')).to.equal('No access');
      expect(await getUserAccess('regularUser')).to.equal('No access');
    });
  });
});
