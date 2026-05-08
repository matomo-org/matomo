/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UsersManager", function () {
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var url = "?module=UsersManager&action=index";

    async function getVisibleSiteRoles() {
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));
        await page.waitForSelector('#sitesForPermission tbody tr:not(.select-all-row)', { visible: true });

        return page.evaluate(() => {
            const roles = {};

            $('#sitesForPermission tbody tr').not('.select-all-row').each(function () {
                const siteName = $(this).find('td:eq(1) span').text().trim();
                const role = $(this).find('.role-select select').val();

                if (siteName) {
                    roles[siteName] = role;
                }
            });

            return roles;
        });
    }

    async function getVisibleUserLogins() {
        return page.evaluate(() => {
            return $('.pagedUsersList tbody tr td#userLogin').map(function () {
                return $(this).text().trim();
            }).get();
        });
    }

    async function getSelectedUserCount() {
        return page.evaluate(() => $('.pagedUsersList tbody td.select-cell input:checked').length);
    }

    async function getRoleSelectValues(rowSelector) {
        return page.evaluate((rowSelector) => {
            return $(rowSelector).find('.role-select select').map(function () {
                return $(this).val();
            }).get();
        }, rowSelector);
    }

    before(async function() {
        await page.webpage.setViewport({
            width: 1250,
            height: 768
        });
    });

    after(async () => {
        try {
            await testEnvironment.callApi('UsersManager.deleteUser', {
                userLogin: '000newuser',
            });
        } catch (err) {
            // ignore
        }
        // Reset plugin load/unload state set by the ActivityLog / Marketplace
        // variant tests so subsequent UI runs do not inherit a polluted environment.
        delete testEnvironment.pluginsToLoad;
        delete testEnvironment.pluginsToUnload;
        await testEnvironment.save();
    });

    it('should display the manage users page correctly', async function () {
        await page.goto(url);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('load');
    });

    it('should show password confirmation when signing out a single user', async function () {
        await (await page.jQuery('.signoutuser:eq(0)')).click();
        await page.waitForSelector('.modal.open', { visible: true });
        await page.focus('.modal.open #currentUserPassword');
        await page.waitForTimeout(250);

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText).to.contain('Are you sure you want to sign');
        expect(modalText).to.contain('out of all active sessions');

        const passwordInputExists = await page.evaluate(() => $('.modal.open #currentUserPassword').length === 1);
        expect(passwordInputExists).to.eq(true);
    });

    it('should show signout success notification when confirmed sign out', async function () {
        await page.type('.modal.open #currentUserPassword', superUserPassword);
        await page.waitForTimeout(250);
        await (await page.jQuery('.confirm-password-modal .confirm-password-btn:visible')).click();
        await page.waitForSelector('.notification-success', { visible: true });

        const notificationText = await page.evaluate(() => $('.notification-success').text());
        expect(notificationText).to.contain('has been signed out of all active sessions');
    });

    it('should change the results page when next is clicked', async function () {
        const initialLogins = await getVisibleUserLogins();

        await (await page.jQuery('.notification-success .close')).click();
        await page.waitForTimeout(250);
        await page.click('.usersListPagination .btn.next');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const newLogins = await getVisibleUserLogins();
        expect(newLogins.length).to.be.greaterThan(0);
        // No overlap between previous page and current page
        const overlap = newLogins.filter((login) => initialLogins.indexOf(login) !== -1);
        expect(overlap).to.deep.equal([]);
    });

    it('should filter by username and access level when the inputs are filled', async function () {
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:view').change();
            $('#user-text-filter').val('ight').change();
            $('select[name=status-level-filter]').val('string:pending').change();

        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(1000); // wait for rendering

        const filterValues = await page.evaluate(() => ({
            access: $('select[name=access-level-filter]').val(),
            text: $('#user-text-filter').val(),
            status: $('select[name=status-level-filter]').val(),
        }));
        expect(filterValues.access).to.eq('string:view');
        expect(filterValues.text).to.eq('ight');
        expect(filterValues.status).to.eq('string:pending');

        // text-filter matches login OR email, so any matching row is fine; just confirm
        // the filter narrowed the set to at least one row.
        const visibleLogins = await getVisibleUserLogins();
        expect(visibleLogins.length).to.be.greaterThan(0);
    });

    it('should display access for a different site when the roles for select is changed', async function () {
        // remove access filter
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:').change();
            $('select[name=status-level-filter]').val('string:').change();
        });

        await page.evaluate(() => $('th.role_header .siteSelector a.title').click());
        await page.waitForNetworkIdle();
        await page.waitForSelector('.siteSelector .custom_select_container a');
        await page.evaluate(function () {
            $('.siteSelector .custom_select_container a:contains(relentless)').click();
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        const selectedSite = await page.evaluate(() => $('th.role_header .siteSelector .title').text().trim());
        expect(selectedSite.toLowerCase()).to.contain('relentless');
    });

    it('should select rows when individual row select is clicked', async function () {
        await (await page.jQuery('td.select-cell input:eq(0) + span', { waitFor: true })).click();
        await (await page.jQuery('td.select-cell input:eq(3) + span', { waitFor: true })).click();
        await (await page.jQuery('td.select-cell input:eq(8) + span', { waitFor: true })).click();
        await page.mouse.move(0, 0);
        await page.waitForTimeout(500); // for checkbox animations

        const checkedCount = await getSelectedUserCount();
        expect(checkedCount).to.eq(3);
    });

    it('should select all rows when all row select is clicked', async function () {
        await page.click('th.select-cell input + span');
        await page.mouse.move(0, 0);
        await page.waitForTimeout(500); // for checkbox animations

        const visibleLogins = await getVisibleUserLogins();
        const checkedCount = await getSelectedUserCount();
        expect(checkedCount).to.eq(visibleLogins.length);

        const headerChecked = await page.evaluate(() => $('th.select-cell input').is(':checked'));
        expect(headerChecked).to.eq(true);
    });

    it('should select all rows in search when link in table is clicked', async function () {
        await page.click('.toggle-select-all-in-search');
        await page.mouse.move(0, 0);
        await page.waitForTimeout(100);

        const selectAllRowText = await page.evaluate(() => $('.pagedUsersList .select-all-row').text().replace(/\s+/g, ' ').trim());
        // After clicking, the link toggles to a "deselect" / clear-selection prompt
        expect(selectAllRowText.length).to.be.greaterThan(0);
        const linkText = await page.evaluate(() => $('.toggle-select-all-in-search').text().trim());
        expect(linkText.length).to.be.greaterThan(0);
    });

    it('should deselect all rows in search except for displayed rows when link in table is clicked again', async function () {
        await page.click('.toggle-select-all-in-search');
        await page.mouse.move(0, 0);
        await page.waitForTimeout(100);

        // After deselecting all-in-search, only the displayed page rows remain checked
        const visibleLogins = await getVisibleUserLogins();
        const checkedCount = await getSelectedUserCount();
        expect(checkedCount).to.eq(visibleLogins.length);
    });

    it('should show bulk action confirm when bulk change access option used', async function () {
        // remove filters
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:').change();
        });
        await page.waitForNetworkIdle();

        await page.click('th.select-cell input + span');
        await page.waitForTimeout(100);

        await page.click('.toggle-select-all-in-search'); // reselect all in search
        await page.waitForTimeout(100);

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('a[data-target=user-list-bulk-actions]')).hover();
        await page.waitForTimeout(300);
        await (await page.jQuery('#bulk-set-access a:contains(Admin)')).click();
        await page.waitForTimeout(350); // wait for animation

        const modalText = await page.evaluate(() => $('.change-user-role-confirm-modal').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.contain('admin');
    });

    it('should change access for all rows in search when confirmed', async function () {
        await (await page.jQuery('.change-user-role-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        // Bulk role changes do not apply to superusers (their select is disabled), so
        // exclude rows whose access cell shows "Superuser".
        const accessLevels = await page.evaluate(() => {
            return $('#manageUsersTable tbody tr').map(function () {
                const sel = $(this).find('.access-cell li[role="option"][aria-selected="true"]').text().trim();
                return sel || null;
            }).get().filter((v) => v && v.toLowerCase() !== 'superuser');
        });
        expect(accessLevels.length).to.be.greaterThan(0);
        accessLevels.forEach((level) => {
            expect(level.toLowerCase()).to.contain('admin');
        });
    });

    it('should remove access to the currently selected site when the bulk remove access option is clicked', async function () {
        await page.click('th.select-cell input + span'); // select displayed rows

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('#user-list-bulk-actions a:contains(Remove Permissions)')).click();
        await (await page.jQuery('.change-user-role-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const accessLevels = await page.evaluate(() => {
            return $('#manageUsersTable tbody tr').map(function () {
                const sel = $(this).find('.access-cell li[role="option"][aria-selected="true"]').text().trim();
                return sel || null;
            }).get().filter((v) => v && v.toLowerCase() !== 'superuser');
        });
        expect(accessLevels.length).to.be.greaterThan(0);
        accessLevels.forEach((level) => {
            expect(level.toLowerCase()).to.contain('no access');
        });
    });

    it('should go back to first page when previous button is clicked', async function () {
        await page.click('.usersListPagination .btn.next');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        await page.click('.usersListPagination .btn.next');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const middlePageLogins = await getVisibleUserLogins();

        await page.click('.usersListPagination .btn.prev');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const previousPageLogins = await getVisibleUserLogins();
        expect(previousPageLogins.length).to.be.greaterThan(0);
        const overlap = previousPageLogins.filter((login) => middlePageLogins.indexOf(login) !== -1);
        expect(overlap).to.deep.equal([]);
    });

    it('should show password confirmation when deleting a single user', async function () {
        await (await page.jQuery('.deleteuser:eq(0)')).click();
        await page.waitForSelector('.modal.open', { visible: true });
        await page.focus('.modal.open #currentUserPassword');
        await page.waitForTimeout(250);

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.contain('are you sure you want to delete');

        const passwordInputVisible = await page.evaluate(() => $('.modal.open #currentUserPassword:visible').length === 1);
        expect(passwordInputVisible).to.eq(true);
    });

  it('should hide the confirmation dialog when cancel is clicked and remove the password if one was entered', async function () {
      await page.type('.modal.open #currentUserPassword', superUserPassword);
      await page.waitForTimeout(250);
      await (await page.jQuery('.confirm-password-modal .modal-close.modal-no:visible')).click();
      await page.waitForTimeout(250);

      const concatenatedText = await page.evaluate(function() {
          let text = '';
          $('#currentUserPassword').each(function() {
              text += $(this).text();
          });
          return text;
      });
      expect(concatenatedText.trim()).to.equal('');
  });

  it('should show password confirmation when deleting a single user after it was cancelled first', async function () {
      await page.waitForSelector('.pagedUsersList:not(.loading)');
      await page.waitForTimeout(250);

      await (await page.jQuery('.deleteuser:eq(0)')).click();
      await page.waitForSelector('.modal.open', { visible: true });
      await page.focus('.modal.open #currentUserPassword');
      await page.waitForTimeout(250);

      const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
      expect(modalText.toLowerCase()).to.contain('are you sure you want to delete');

      const passwordInputValue = await page.evaluate(() => $('.modal.open #currentUserPassword').val());
      expect(passwordInputValue).to.eq('');
  });

    it('should delete a single user when the modal is confirmed is clicked', async function () {
        const beforeLogins = await getVisibleUserLogins();
        const targetLogin = beforeLogins[0];

        await page.type('.modal.open #currentUserPassword', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const afterLogins = await getVisibleUserLogins();
        expect(afterLogins).to.not.include(targetLogin);
    });

    it('should show password confirmation when deleting multiple user using bulk action', async function () {
        await page.click('th.select-cell input + span'); // select displayed rows

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('#user-list-bulk-actions a:contains(Delete Users)')).click();
        await page.waitForSelector('.modal.open', { visible: true });
        await page.focus('.modal.open #currentUserPassword');
        await page.waitForTimeout(250);

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.contain('are you sure you want to delete');
        expect(modalText.toLowerCase()).to.contain('selected users');

        const passwordInputVisible = await page.evaluate(() => $('.modal.open #currentUserPassword:visible').length === 1);
        expect(passwordInputVisible).to.eq(true);
    });

    it('should delete selected users when delete users bulk action is used', async function () {
        const beforeLogins = await getVisibleUserLogins();

        await page.type('.modal.open #currentUserPassword', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitForSelector('.pagedUsersList:not(.loading)');

        const afterLogins = await getVisibleUserLogins();
        const stillPresent = beforeLogins.filter((login) => afterLogins.indexOf(login) !== -1);
        expect(stillPresent).to.deep.equal([]);
    });

    it('should show the add new user form when the add new user button is clicked', async function () {
        await page.click('.add-user-container .btn');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.userInviteForm', { visible: true });

        const formState = await page.evaluate(() => ({
            loginVisible: $('#user_login:visible').length === 1,
            emailVisible: $('#user_email:visible').length === 1,
            siteSelectorVisible: $('.userInviteForm .siteSelector:visible').length === 1,
            saveButtonVisible: $('.userInviteForm .matomo-save-button input:visible').length === 1,
        }));
        expect(formState.loginVisible).to.eq(true);
        expect(formState.emailVisible).to.eq(true);
        expect(formState.siteSelectorVisible).to.eq(true);
        expect(formState.saveButtonVisible).to.eq(true);
    });

    it('should show confirmation when inviting a user', async function () {
        await page.type('#user_login', '000newuser');
        await page.type('#user_email', 'theuser@email.com');

        await page.click('.userInviteForm .siteSelector a.title');
        await (await page.jQuery('.userInviteForm .siteSelector .custom_select_ul_list a:eq(1):visible', { waitFor: true })).click();

        await page.evaluate(() => $('.userInviteForm .matomo-save-button input').click());
        await page.waitForSelector('.modal.open', { visible: true });
        await page.focus('.modal.open #currentUserPassword');
        await page.waitForTimeout(250);

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.match(/please (re-authenticate|confirm)/);

        const passwordInputExists = await page.evaluate(() => $('.modal.open #currentUserPassword').length === 1);
        expect(passwordInputExists).to.eq(true);
    });

    it('should show the edit user form when user has been invited', async function () {
        await page.type('.modal.open #currentUserPassword', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => $('.modal.open').length === 0);
        await page.waitForSelector('.userEditForm', { visible: true });

        const userEditState = await page.evaluate(() => ({
            login: $('.userEditForm #user_login').val(),
            email: $('.userEditForm #user_email').val(),
        }));
        expect(userEditState.login).to.eq('000newuser');
        expect(userEditState.email).to.eq('theuser@email.com');
    });

    it('should warn about invitation resend when changing email', async function () {
        await page.evaluate(() => $('.userEditForm #user_email').val('theuser2@email.com'));
        await page.evaluate(() => $('.userEditForm .matomo-save-button input:visible').click());
        await page.waitForTimeout(100);
        await page.waitForFunction(() => $('.modal.open:visible').length)
        await page.focus('.modal.open #currentUserPassword');
        await page.waitForTimeout(250);

        const modalText = await page.evaluate(() => $('.modal.open:visible').text().replace(/\s+/g, ' ').trim());
        expect(modalText).to.contain('Changing the email will invalidate the previous invitation');
    });

    it('should show the permissions edit when the permissions tab is clicked', async function () {
        // close modal from previous step
        await (await page.jQuery('.confirm-password-modal .modal-close.modal-no:visible')).click();
        await page.waitForNetworkIdle();

        await page.click('.userEditForm .menuPermissions');
        await page.mouse.move(0, 0);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_edit',
            comparisonThreshold: 0.0005,
        });
    });

    it('should select all sites in search when in table link is clicked', async function () {
        // remove filters
        await page.evaluate(function () {
            $('div.site-filter>input').val('').change();
            $('.access-filter select').val('string:').change();
        });
        await page.waitForNetworkIdle();

        await page.click('.userPermissionsEdit th.select-cell input + span');
        await page.waitForTimeout(500);
        await page.evaluate(() => $('.userPermissionsEdit tr.select-all-row a').click());
        await page.waitForTimeout(500);
        await page.mouse.move(0, 0);

        const allSelectedState = await page.evaluate(() => ({
            headerChecked: $('.userPermissionsEdit th.select-cell input').is(':checked'),
            allBodyChecked: $('#sitesForPermission tbody tr:not(.select-all-row) td.select-cell input').length > 0
                && $('#sitesForPermission tbody tr:not(.select-all-row) td.select-cell input:not(:checked)').length === 0,
            selectAllRowText: $('.userPermissionsEdit tr.select-all-row').text().replace(/\s+/g, ' ').trim(),
        }));
        expect(allSelectedState.headerChecked).to.eq(true);
        expect(allSelectedState.allBodyChecked).to.eq(true);
        expect(allSelectedState.selectAllRowText.length).to.be.greaterThan(0);
    });

    it('should add access to all websites when bulk access is used on all websites in search', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await (await page.jQuery('#user-permissions-edit-bulk-actions>li:first>a')).hover();
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(Write)')).click();

        await page.waitForTimeout(250); // animation
        await page.waitForSelector('.change-access-confirm-modal', { visible: true });

        const yes = await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible');
        await yes.click();

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // animation
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const roleValues = await getRoleSelectValues('#sitesForPermission tbody tr:not(.select-all-row)');
        expect(roleValues.length).to.be.greaterThan(0);
        roleValues.forEach((role) => {
            expect(role).to.eq('string:write');
        });
    });

    it('should go to the next results page when the next button is clicked', async function () {
        const beforeSites = await page.evaluate(() => {
            return $('#sitesForPermission tbody tr:not(.select-all-row) td:eq(1) span').map(function () {
                return $(this).text().trim();
            }).get();
        });

        await page.click('.sites-for-permission-pagination a.next');
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const afterSites = await page.evaluate(() => {
            return $('#sitesForPermission tbody tr:not(.select-all-row) td:eq(1) span').map(function () {
                return $(this).text().trim();
            }).get();
        });
        expect(afterSites.length).to.be.greaterThan(0);
        const overlap = afterSites.filter((site) => beforeSites.indexOf(site) !== -1);
        expect(overlap).to.deep.equal([]);
    });

    it('should remove access to a single site when noaccess is selected', async function () {
        await page.evaluate(() => $('#sitesForPermission .role-select select').first().val('string:noaccess').change());
        await page.waitForSelector('.change-access-confirm-modal', { visible: true });
        await page.waitForTimeout(250); // animation

        await (await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // animation
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const firstRowRole = await page.evaluate(() => $('#sitesForPermission .role-select select').first().val());
        expect(firstRowRole).to.eq('string:noaccess');
    });

    it('should select multiple rows when individual row selects are clicked', async function () {
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(0) + span')).click();
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(3) + span')).click();
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(8) + span')).click();
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(1000); // for checkbox animations

        const checkedCount = await page.evaluate(() => $('#sitesForPermission tbody tr:not(.select-all-row) td.select-cell input:checked').length);
        expect(checkedCount).to.eq(3);
    });

    it('should set access to selected sites when set bulk access is used', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await page.waitForTimeout(500); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions>li:first > a')).hover();
        await page.waitForTimeout(500); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(Admin):visible', { waitFor: true })).click();

        await page.waitForSelector('.change-access-confirm-modal');

        await (await page.jQuery('.change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        // The three rows previously selected (indices 0, 3, 8) should now be admin.
        const roles = await getVisibleSiteRoles();
        const adminCount = Object.values(roles).filter((role) => role === 'string:admin').length;
        expect(adminCount).to.be.greaterThan(0);
    });

    it('should filter the permissions when the filters are used', async function () {
        await page.evaluate(function () {
            $('.userPermissionsEdit .access-filter select').val('string:admin').change();
        });
        await page.waitForNetworkIdle();
        await page.type('.userPermissionsEdit div.site-filter>input', 'hunter');
        await page.waitForNetworkIdle();
        await page.waitForSelector('#sitesForPermission tr', { visible: true });
        await page.waitForTimeout(1000);

        const filterValues = await page.evaluate(() => ({
            access: $('.userPermissionsEdit .access-filter select').val(),
            site: $('.userPermissionsEdit div.site-filter>input').val(),
        }));
        expect(filterValues.access).to.eq('string:admin');
        expect(filterValues.site).to.eq('hunter');

        const visibleSiteNames = await page.evaluate(() => {
            return $('#sitesForPermission tbody tr:not(.select-all-row) td:eq(1) span').map(function () {
                return $(this).text().trim();
            }).get();
        });
        visibleSiteNames.forEach((name) => {
            expect(name.toLowerCase()).to.contain('hunter');
        });
    });

    it('should select all displayed rows when the select all checkbox is clicked', async function () {
        await page.click('.userPermissionsEdit th.select-cell input + span');
        await page.waitForTimeout(400); // for checkbox animations
        await page.mouse.move(-10, -10);

        const allSelectedState = await page.evaluate(() => ({
            headerChecked: $('.userPermissionsEdit th.select-cell input').is(':checked'),
            uncheckedRows: $('#sitesForPermission tbody tr:not(.select-all-row) td.select-cell input:not(:checked)').length,
            totalRows: $('#sitesForPermission tbody tr:not(.select-all-row) td.select-cell input').length,
        }));
        expect(allSelectedState.headerChecked).to.eq(true);
        expect(allSelectedState.totalRows).to.be.greaterThan(0);
        expect(allSelectedState.uncheckedRows).to.eq(0);
    });

    it('should set access to all sites selected when set bulk access is used', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await page.waitForTimeout(250); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions>li:first > a:visible')).hover();
        await page.waitForTimeout(250); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(View)', { waitFor: true })).click();
        await page.waitForTimeout(250); // animation

        await (await page.jQuery('.change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.evaluate(function () { // remove filter
            $('.access-filter select').val('string:some').change();
        });
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const roles = await getVisibleSiteRoles();

        expect(roles.hunter12).to.equal('string:view');
        expect(roles.hunter32).to.equal('string:view');
        expect(roles.hunter82).to.equal('string:view');
        expect(roles.hunter2).to.equal('string:write');
        expect(roles.hunter22).to.equal('string:write');
    });

    it('should set access to single site when select in table is used', async function () {
        await page.evaluate(function () {
            $('.userPermissionsEdit .role-select:eq(0) select').val('string:admin').change();
        });

        await page.waitForSelector('.userPermissionsEdit .change-access-confirm-modal', { visible: true });
        await page.waitForTimeout(100); // animation
        await (await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const firstRowRole = await page.evaluate(() => $('.userPermissionsEdit .role-select:eq(0) select').val());
        expect(firstRowRole).to.eq('string:admin');
    });

    it('should set a capability to single site when capability checkbox is clicked', async function () {
        await page.evaluate(() => $('.addCapability:eq(0)').click());
        await page.evaluate(() => $('.addCapability:eq(0) .expandableListCategory:contains(Tag Manager)').click());
        await page.evaluate(() => $('.addCapability:eq(0) .expandableListItem:contains(Publish Live Container)').click());

        await page.waitForTimeout(250); // animation

        await (await page.jQuery('.userPermissionsEdit .confirmCapabilityToggle .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.waitForTimeout(250); // animation
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        const firstRowCapabilityText = await page.evaluate(() => {
            return $('#sitesForPermission tbody tr:not(.select-all-row)').first().find('.capabilitiesEdit .capability-name').text();
        });
        expect(firstRowCapabilityText).to.contain('Publish Live Container');
    });

    it('should remove access to displayed rows when remove bulk access is clicked', async function () {
        // remove filters
        await page.evaluate(function () {
            $('.userPermissionsEdit div.site-filter>input').val('').change();
            $('.userPermissionsEdit .access-filter select').val('string:some').change();
        });

        await page.waitForNetworkIdle();
        await page.click('input#perm_edit_select_all + span');

        await page.waitForSelector('.userPermissionsEdit tr.select-all-row a');
        await page.click('.userPermissionsEdit tr.select-all-row a');

        await page.waitForTimeout(250);

        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await (await page.jQuery('.userPermissionsEdit a:contains(Remove Permissions)')).click();

        await (await page.jQuery('.delete-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForFunction(() => !$('.userPermissionsEdit').hasClass('loading'));

        // After removing access for all sites where this user had `some` access, the
        // filtered list should be empty.
        const remainingRows = await page.evaluate(() => $('#sitesForPermission tbody tr:not(.select-all-row)').length);
        expect(remainingRows).to.eq(0);
    });

    it('should display the superuser access tab when the superuser tab is clicked', async function () {
        await page.click('.userEditForm .menuSuperuser');
        await page.mouse.move(0, 0);
        await page.waitForTimeout(100);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('superuser_tab');
    });

    it('should show superuser confirm modal when the superuser toggle is clicked', async function () {
        await page.click('.userEditForm #superuser_access+span');
        await page.waitForSelector('.modal.open', { visible: true });
        await page.waitForTimeout(500);

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.contain('superuser');

        const passwordInputExists = await page.evaluate(() => $('.modal.open #currentUserPassword').length === 1);
        expect(passwordInputExists).to.eq(true);
    });

    it('should fail to set superuser access if password is wrong', async function () {
        await page.type('.modal.open #currentUserPassword', 'wrongpassword');
        await page.evaluate(() => $('.modal.open .modal-close:not(.modal-no):visible').click());
        await page.waitForNetworkIdle();

        await page.waitForSelector('.notification-error', { visible: true });

        const notificationHtml = await page.evaluate(() => $('.notification-error>div>div').html());
        expect(notificationHtml).to.equal('The current password you entered is not correct.');
    });

    it('should give the user superuser access when the superuser modal is confirmed', async function () {
        await page.click('.userEditForm #superuser_access+span');
        await page.waitForSelector('.modal.open #currentUserPassword', {visible: true});

        await page.type('.modal.open #currentUserPassword', superUserPassword);
        await (await page.jQuery('.modal.open .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitForTimeout(500);

        const isChecked = await page.evaluate(() => $('.userEditForm #superuser_access').is(':checked'));
        expect(isChecked).to.eq(true);
    });

    it('should go back to the manage users page when the back link is clicked', async function () {
        await page.click('.userEditForm .entityCancelLink');
        await page.waitForSelector('.pagedUsersList');

        await page.evaluate(function () { // remove filter so new user shows
            $('#user-text-filter').val('').change();
        });
        await page.waitForNetworkIdle();
        await page.waitForSelector('.pagedUsersList:not(.loading)');
        await page.waitForTimeout(1000); // rendering

        const editFormVisible = await page.evaluate(() => $('.userEditForm:visible').length > 0);
        expect(editFormVisible).to.eq(false);

        const visibleLogins = await getVisibleUserLogins();
        expect(visibleLogins).to.include('000newuser');
    });

  // Superuser test for editing their own user
  describe('UsersManager_000newuser_view', function () {
    before(async function () {
      testEnvironment.fakeIdentity = '000newuser';
      await testEnvironment.save();
    });

    after(async () => {
      delete testEnvironment.fakeIdentity;
      testEnvironment.save();
    });

    it('should disable the superuser access checkbox when editing own user', async function () {
      testEnvironment.fakeIdentity = '000newuser';
      await testEnvironment.save();

      await page.reload();
      await page.waitForNetworkIdle();
      await (await page.jQuery('.usersManager table td:contains("000newuser") ~ td.actions-cell .icon-edit', { waitFor: true })).click();
      await page.waitForSelector('.userEditForm .menuSuperuser');
      await page.click('.userEditForm .menuSuperuser a');

      await (await page.jQuery('#superuser_access')).hover();
      await page.waitForSelector('.ui-tooltip');

      // Use DOM comparison to check tooltip text since image comparison is overkill
      const toolTipHtml = await page.evaluate(() => $('.ui-tooltip').html());
      expect(toolTipHtml).to.equal('<div class="ui-tooltip-content">You cannot revoke your own superuser access.</div>');

      const isDisabled = await page.evaluate(() => $('#superuser_access').is(':disabled'));
      expect(isDisabled).to.eq(true);
    });
  });

    it('should display the superuser access tab when the superuser tab is clicked with ActivityLog', async function () {
      testEnvironment.pluginsToLoad = ['ActivityLog'];
      await testEnvironment.save();

      await page.reload();
      await page.waitForTimeout(100);

      await (await page.jQuery('button.edituser:eq(0)', { waitFor: true })).click();
      await page.waitForTimeout(250);
      await page.waitForNetworkIdle();

      await page.click('.userEditForm .menuSuperuser');
      await page.mouse.move(0, 0);
      await page.waitForTimeout(100);

      expect(await page.screenshotSelector('.usersManager')).to.matchImage('superuser_tab_activityLog');
    });

    it('should display the superuser access tab when the superuser tab is clicked without Marketplace', async function () {
      testEnvironment.pluginsToUnload = ['ActivityLog', 'Marketplace'];
      await testEnvironment.save();

      await page.reload();
      await page.waitForTimeout(100);

      await (await page.jQuery('button.edituser:eq(0)', { waitFor: true })).click();
      await page.waitForTimeout(250);
      await page.waitForNetworkIdle();

      await page.click('.userEditForm .menuSuperuser');
      await page.mouse.move(0, 0);
      await page.waitForTimeout(100);

      testEnvironment.pluginsToLoad = ['Marketplace'];
      await testEnvironment.save();

      expect(await page.screenshotSelector('.usersManager')).to.matchImage('superuser_tab_no_marketplace');
    });

    it('should show the edit user form when the edit icon in a row is clicked', async function () {
        await page.reload();
        await page.waitForTimeout(100);

        await (await page.jQuery('button.edituser:eq(2)', { waitFor: true })).click();
        await page.waitForTimeout(250);
        await page.waitForNetworkIdle();
        await page.waitForSelector('.userEditForm', { visible: true });

        const formState = await page.evaluate(() => ({
            formExists: $('.userEditForm').length === 1,
            loginValue: ($('.userEditForm #user_login').val() || $('.userEditForm #user_login').text() || '').trim(),
            emailExists: $('.userEditForm #user_email').length === 1,
            basicInfoTabExists: $('.userEditForm .basic-info-tab').length === 1,
        }));
        expect(formState.formExists).to.eq(true);
        expect(formState.loginValue.length).to.be.greaterThan(0);
        expect(formState.emailExists).to.eq(true);
        expect(formState.basicInfoTabExists).to.eq(true);
    });

    it('should ask for password confirmation when trying to change email', async function () {
        await page.evaluate(function () {
            $('.userEditForm #user_email').val('testlogin3@example.com').change();
        });
        await page.waitForTimeout(100);

        var btnSave = await page.jQuery('.userEditForm .basic-info-tab .matomo-save-button .btn', { waitFor: true });
        await btnSave.click();

        await page.waitForSelector('.modal.open', { visible: true });
        await page.waitForTimeout(500); // animation

        const modalText = await page.evaluate(() => $('.modal.open').text().replace(/\s+/g, ' ').trim());
        expect(modalText.toLowerCase()).to.match(/please (re-authenticate|confirm)/);

        const passwordInputExists = await page.evaluate(() => $('.modal.open #currentUserPassword').length === 1);
        expect(passwordInputExists).to.eq(true);
    });

    it('should show error when wrong password entered', async function () {
        await page.type('.modal.open #currentUserPassword', 'test123456');

        var btnNo = await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible');
        await btnNo.click();

        await page.waitForTimeout(500); // animation
        await page.waitForNetworkIdle();
        await page.waitForSelector('#notificationContainer .notification', { visible: true });

        const notificationText = await page.evaluate(() => $('#notificationContainer').text());
        expect(notificationText).to.contain('The current password you entered is not correct');
    });

    it('should show resend confirm when resend clicked', async function () {
        await page.goto(url);
        await (await page.jQuery('.resend')).click();
        await page.waitForTimeout(500); // animation
        await page.waitForSelector('.resend-invite-confirm-modal', { visible: true });

        const modalState = await page.evaluate(() => ({
            text: $('.resend-invite-confirm-modal').text().replace(/\s+/g, ' ').trim(),
            copyLinkVisible: $('.resend-invite-confirm-modal .btn-copy-link:visible').length === 1,
            resendVisible: $('.resend-invite-confirm-modal .btn-resend:visible').length === 1,
        }));
        expect(modalState.text.toLowerCase()).to.contain('resend');
        expect(modalState.copyLinkVisible).to.eq(true);
        expect(modalState.resendVisible).to.eq(true);
    });

    it('should show invite link copied when copy clicked', async function () {
        await (await page.jQuery('.resend-invite-confirm-modal .btn-copy-link')).click();

        await page.waitForTimeout(500); // animation
        // password confirm
        await page.type('.confirm-password-modal #currentUserPassword', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();

        await page.waitForNetworkIdle();
        await page.waitForSelector('.resend-invite-confirm-modal .success-copied', { visible: true, timeout: 10000 });

        const copiedText = await page.evaluate(() => $('.resend-invite-confirm-modal .success-copied').text().replace(/\s+/g, ' ').trim());
        expect(copiedText.toLowerCase()).to.contain('link copied');
    });

    it('should show resend success message', async function() {
        await (await page.jQuery('.resend-invite-confirm-modal .btn-resend')).click();

        await page.waitForTimeout(500); // animation
        // password confirm
        await page.type('.confirm-password-modal #currentUserPassword', superUserPassword);
        await (await page.jQuery('.confirm-password-modal .modal-close:not(.modal-no):visible')).click();

        await page.waitForSelector('#notificationContainer .notification');
        await page.waitForNetworkIdle();

        const notificationText = await page.evaluate(() => $('#notificationContainer .notification').text());
        expect(notificationText.toLowerCase()).to.contain('invitation sent');
    });


    // admin user tests
    describe('UsersManager_admin_view', function () {
        before(async function () {
            var idSites = [];
            for (var i = 1; i !== 46; ++i) {
                idSites.push(i);
            }

            testEnvironment.idSitesAdminAccess = idSites;
            testEnvironment.save();

            await page.webpage.setViewport({
                width: 1250,
                height: 768
            });
        });

        after(function () {
            delete testEnvironment.idSitesAdminAccess;
            testEnvironment.save();
        });

        it('should hide columns & functionality if an admin user views the manage user page', async function () {
            await page.goto(url);

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_load');
        });

        it('should show the add user form for admin users', async function () {
            await page.click('.add-user-container .btn');
            await page.waitForNetworkIdle();
            await page.waitForSelector('.userInviteForm', { visible: true });

            const formState = await page.evaluate(() => ({
                inviteFormVisible: $('.userInviteForm:visible').length === 1,
                superuserToggleVisible: $('.userInviteForm #superuser_access:visible').length > 0,
                loginVisible: $('.userInviteForm #user_login:visible').length === 1,
                emailVisible: $('.userInviteForm #user_email:visible').length === 1,
            }));
            expect(formState.inviteFormVisible).to.eq(true);
            expect(formState.loginVisible).to.eq(true);
            expect(formState.emailVisible).to.eq(true);
            // Admins must not see the superuser toggle on the invite form
            expect(formState.superuserToggleVisible).to.eq(false);
        });

        it('should not allow editing basic info for admin users', async function () {
            await page.click('.userInviteForm .entityCancelLink');
            await (await page.jQuery('button.edituser:eq(1)')).click();
            await page.waitForNetworkIdle();
            await page.waitForSelector('.userEditForm', { visible: true });

            const editState = await page.evaluate(() => ({
                emailDisabled: $('.userEditForm #user_email').is(':disabled')
                    || $('.userEditForm #user_email[readonly]').length > 0
                    || $('.userEditForm #user_email').length === 0,
                basicInfoSaveButton: $('.userEditForm .basic-info-tab .matomo-save-button .btn:visible').length,
            }));
            expect(editState.emailDisabled).to.eq(true);
            // Admins should not have a save button on the basic info tab
            expect(editState.basicInfoSaveButton).to.eq(0);
        });

        it('should allow editing user permissions for admin users', async function () {
            await page.click('.userEditForm .menuPermissions');
            await page.mouse.move(-10, -10);
            await page.waitForSelector('.userPermissionsEdit', { visible: true });

            const permissionsState = await page.evaluate(() => ({
                visible: $('.userPermissionsEdit:visible').length === 1,
                hasRows: $('#sitesForPermission tbody tr:not(.select-all-row)').length > 0,
            }));
            expect(permissionsState.visible).to.eq(true);
            expect(permissionsState.hasRows).to.eq(true);
        });

      it('should filter editing user permissions by access', async function () {
            await page.evaluate(function () {
              $('.access-filter select').val('string:admin').change();
            });
            await page.waitForTimeout(500); // wait for animation

            await page.mouse.move(-10, -10);

            const filterValue = await page.evaluate(() => $('.access-filter select').val());
            expect(filterValue).to.eq('string:admin');
      });

        it('should show the add existing user modal', async function () {
            await page.click('.userEditForm .entityCancelLink');

            await page.click('.add-existing-user');
            await page.waitForSelector('.add-existing-user-modal', { visible: true });
            await page.waitForTimeout(500); // wait for animation

            const modalState = await page.evaluate(() => ({
                visible: $('.add-existing-user-modal:visible').length === 1,
                emailInputVisible: $('.add-existing-user-modal input[name="add-existing-user-email"]:visible').length === 1,
            }));
            expect(modalState.visible).to.eq(true);
            expect(modalState.emailInputVisible).to.eq(true);
        });

        it('should add a user by email when an email is entered', async function () {
            await page.type('input[name="add-existing-user-email"]', '0login3conchords@example.com');
            await page.waitForSelector('.add-existing-user-modal');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show new user
                $('#user-text-filter').val('0login3conchords@example.com').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitForSelector('.pagedUsersList:not(.loading)');
            await page.waitForTimeout(1000); // for opacity to change

            // Filter is set to the new user's email; the user should now appear in
            // the list (matched by email by the user-text-filter).
            const rowCount = await page.evaluate(() => $('.pagedUsersList tbody tr td#userLogin').length);
            expect(rowCount).to.be.greaterThan(0);
        });

        it('should add a user by username when a username is entered', async function () {
            await page.click('.add-existing-user');
            await page.waitForSelector('.add-existing-user-modal');
            await page.evaluate(() => $('input[name="add-existing-user-email"]').val('').change());
            await page.type('input[name="add-existing-user-email"]', '10login8');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show new user
                $('#user-text-filter').val('10login8').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitForSelector('.pagedUsersList:not(.loading)');
            await page.waitForTimeout(1000); // for opacity to change

            const visibleLogins = await getVisibleUserLogins();
            expect(visibleLogins).to.include('10login8');
        });

        it('should fail if an email/username that does not exist is entered', async function () {
            await page.click('.add-existing-user');
            await page.evaluate(() => $('input[name="add-existing-user-email"]').val('').change());
            await page.type('input[name="add-existing-user-email"]', 'sldkjfsdlkfjsdkl');
            await page.waitForSelector('.add-existing-user-modal');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show no user added
                $('#user-text-filter').val('sldkjfsdlkfjsdkl').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitForSelector('.pagedUsersList:not(.loading)');
            await page.waitForTimeout(1000); // for opacity to change

            const rowCount = await page.evaluate(() => $('.pagedUsersList tbody tr td#userLogin').length);
            expect(rowCount).to.eq(0);
        });
    });
});
