/*!
 * Matomo - free/libre analytics platform
 *
 * UsersManager screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UsersManager", function () {
    this.timeout(0);
    this.fixture = "Piwik\\Plugins\\UsersManager\\tests\\Fixtures\\ManyUsers";

    var url = "?module=UsersManager&action=index";

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
    });

    it('should display the manage users page correctly', async function () {
        await page.goto(url);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('load');
    });

    it('should change the results page when next is clicked', async function () {
        await page.click('.usersListPagination .btn.next');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('next_click');
    });

    it('should filter by username and access level when the inputs are filled', async function () {
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:view').change();
            $('#user-text-filter').val('ight').change();
        });
        await page.waitForNetworkIdle();
        await page.waitFor(1000); // wait for rendering

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('filters');
    });

    it('should display access for a different site when the roles for select is changed', async function () {
        // remove access filter
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:').change();
        });

        await page.evaluate(() => $('th.role_header .siteSelector a.title').click());
        await page.waitForNetworkIdle();
        await page.waitForSelector('.siteSelector .custom_select_container a');
        await page.evaluate(function () {
            $('.siteSelector .custom_select_container a:contains(relentless)').click();
        });
        await page.waitForNetworkIdle();
        await page.waitFor(500);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('role_for');
    });

    it('should select rows when individual row select is clicked', async function () {
        await (await page.jQuery('td.select-cell input:eq(0) + span', { waitFor: true })).click();
        await (await page.jQuery('td.select-cell input:eq(3) + span', { waitFor: true })).click();
        await (await page.jQuery('td.select-cell input:eq(8) + span', { waitFor: true })).click();
        await page.mouse.move(0, 0);
        await page.waitFor(500); // for checkbox animations

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('rows_selected');
    });

    it('should select all rows when all row select is clicked', async function () {
        await page.click('th.select-cell input + span');
        await page.mouse.move(0, 0);
        await page.waitFor(500); // for checkbox animations

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('all_rows_selected');
    });

    it('should select all rows in search when link in table is clicked', async function () {
        await page.click('.toggle-select-all-in-search');
        await page.mouse.move(0, 0);
        await page.waitFor(100);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('all_rows_in_search');
    });

    it('should deselect all rows in search except for displayed rows when link in table is clicked again', async function () {
        await page.click('.toggle-select-all-in-search');
        await page.mouse.move(0, 0);
        await page.waitFor(100);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('all_rows_deselected');
    });

    it('should show bulk action confirm when bulk change access option used', async function () {
        // remove filters
        await page.evaluate(function () {
            $('select[name=access-level-filter]').val('string:').change();
        });

        await page.click('.toggle-select-all-in-search'); // reselect all in search

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('a[data-target=user-list-bulk-actions]')).hover();
        await (await page.jQuery('#bulk-set-access a:contains(Admin)')).click();
        await page.waitFor(350); // wait for animation

        expect(await (await page.$('.change-user-role-confirm-modal')).screenshot()).to.matchImage('bulk_set_access_confirm');
    });

    it('should change access for all rows in search when confirmed', async function () {
        await (await page.jQuery('.change-user-role-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('bulk_set_access');
    });

    it('should remove access to the currently selected site when the bulk remove access option is clicked', async function () {
        await page.click('th.select-cell input + span'); // select displayed rows

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('#user-list-bulk-actions a:contains(Remove Permissions)')).click();
        await (await page.jQuery('.change-user-role-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('bulk_remove_access');
    });

    it('should go back to first page when previous button is clicked', async function () {
        await page.click('.usersListPagination .btn.next');
        await page.waitForNetworkIdle();
        await page.waitFor('.pagedUsersList:not(.loading)');

        await page.click('.usersListPagination .btn.next');
        await page.waitForNetworkIdle();
        await page.waitFor('.pagedUsersList:not(.loading)');

        await page.click('.usersListPagination .btn.prev');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitFor('.pagedUsersList:not(.loading)');

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('previous');
    });

    it('should delete a single user when the modal is confirmed is clicked', async function () {
        await (await page.jQuery('.deleteuser:eq(0)')).click();
        await (await page.jQuery('.delete-user-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitFor('.pagedUsersList:not(.loading)');

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('delete_single');
    });

    it('should delete selected users when delete users bulk action is used', async function () {
        await page.click('th.select-cell input + span'); // select displayed rows

        await page.click('.bulk-actions.btn');
        await (await page.jQuery('#user-list-bulk-actions a:contains(Delete Users)')).click();
        await (await page.jQuery('.delete-user-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);
        await page.waitFor('.pagedUsersList:not(.loading)');

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('delete_bulk_access');
    });

    it('should show the add new user form when the add new user button is clicked', async function () {
        await page.click('.add-user-container .btn');
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('add_new_user_form');
    });

    it('should create a user and show the edit user form when the create user button is clicked', async function () {
        await page.type('#user_login', '000newuser');
        await page.type('#user_password', 'thepassword');
        await page.type('#user_email', 'theuser@email.com');

        await page.click('piwik-user-edit-form .siteSelector a.title');
        await (await page.jQuery('piwik-user-edit-form .siteSelector .custom_select_ul_list a:eq(1):visible', { waitFor: true })).click();

        await page.evaluate(() => $('piwik-user-edit-form [piwik-save-button] input').click());
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('user_created');
    });

    it('should show the permissions edit when the permissions tab is clicked', async function () {
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
        await page.waitFor(500); // for angular to re-render
        await page.evaluate(() => $('.userPermissionsEdit tr.select-all-row a').click());
        await page.waitFor(500); // for angular to re-render

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_all_rows_in_search',
            comparisonThreshold: 0.0005,
        });
    });

    it('should add access to all websites when bulk access is used on all websites in search', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await (await page.jQuery('#user-permissions-edit-bulk-actions>li:first>a')).hover();
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(Write)')).click();

        await page.waitFor(250); // animation
        await page.waitFor('.change-access-confirm-modal', { visible: true });

        const yes = await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible');
        await yes.click();

        await page.waitForNetworkIdle();
        await page.waitFor(250); // animation

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_all_sites_access',
            comparisonThreshold: 0.0005,
        });
    });

    it('should go to the next results page when the next button is clicked', async function () {
        await page.click('.sites-for-permission-pagination a.next');
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_next',
            comparisonThreshold: 0.0005,
        });
    });

    it('should remove access to a single site when noaccess is selected', async function () {
        await page.evaluate(() => $('#sitesForPermission .role-select select').first().val('string:noaccess').change());
        await page.waitFor('.change-access-confirm-modal', { visible: true });
        await page.waitFor(250); // animation

        await (await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitFor(250); // animation

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_remove_single',
            comparisonThreshold: 0.0005,
        });
    });

    it('should select multiple rows when individual row selects are clicked', async function () {
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(0) + span')).click();
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(3) + span')).click();
        await (await page.jQuery('#sitesForPermission td.select-cell input:eq(8) + span')).click();
        await page.mouse.move(-10, -10);
        await page.waitFor(1000); // for checkbox animations

        expect(await (await page.$('.usersManager')).screenshot()).to.matchImage({
            imageName: 'permissions_select_multiple',
            comparisonThreshold: 0.0005,
        });
    });

    it('should set access to selected sites when set bulk access is used', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await page.waitFor(500); // animation
        await page.evaluate(() => $('#user-permissions-edit-bulk-actions>li:first > a:visible').mouseenter());
        await page.waitFor(500); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(Admin):visible', { waitFor: true })).click();

        await page.waitFor('.change-access-confirm-modal');

        await (await page.jQuery('.change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();
        await page.waitFor(100);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_bulk_access_set',
            comparisonThreshold: 0.0008
        });
    });

    it('should filter the permissions when the filters are used', async function () {
        await page.evaluate(function () {
            $('.userPermissionsEdit .access-filter select').val('string:admin').change();
        });
        await page.waitForNetworkIdle();
        await page.type('.userPermissionsEdit div.site-filter>input', 'hunter');
        await page.waitForNetworkIdle();
        await page.waitFor('#sitesForPermission tr', { visible: true });
        await page.waitFor(1000);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_filters',
            comparisonThreshold: 0.0005
        });
    });

    it('should select all displayed rows when the select all checkbox is clicked', async function () {
        await page.click('.userPermissionsEdit th.select-cell input + span');
        await page.waitFor(250); // for checkbox animations

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_select_all',
            comparisonThreshold: 0.0005
        });
    });

    it('should set access to all sites selected when set bulk access is used', async function () {
        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await page.waitFor(250); // animation
        await page.evaluate(() => $('#user-permissions-edit-bulk-actions>li:first > a:visible').mouseenter());
        await page.waitFor(250); // animation
        await (await page.jQuery('#user-permissions-edit-bulk-actions a:contains(View)', { waitFor: true })).click();
        await page.waitFor(250); // animation

        await (await page.jQuery('.change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.evaluate(function () { // remove filter
            $('.access-filter select').val('string:some').change();
        });
        await page.waitForNetworkIdle();
        await page.waitFor(250); // animation

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_bulk_access_set_all',
            comparisonThreshold: 0.0015
        });
    });

    it('should set access to single site when select in table is used', async function () {
        await page.evaluate(function () {
            $('.userPermissionsEdit .role-select:eq(0) select').val('string:admin').change();
        });

        await page.waitFor('.userPermissionsEdit .change-access-confirm-modal', { visible: true });
        await page.waitFor(100); // animation
        await (await page.jQuery('.userPermissionsEdit .change-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_single_site_access',
            comparisonThreshold: 0.0005
        });
    });

    it('should set a capability to single site when capability checkbox is clicked', async function () {
        await page.evaluate(() => $('.addCapability:eq(0)').click());
        await page.evaluate(() => $('.addCapability:eq(0) .expandableListCategory:contains(Tag Manager)').click());
        await page.evaluate(() => $('.addCapability:eq(0) .expandableListItem:contains(Publish Live Container)').click());

        await page.waitFor(250); // animation

        await (await page.jQuery('.userPermissionsEdit .confirmCapabilityToggle .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.waitFor(250); // animation

        expect(await page.screenshotSelector('.usersManager')).to.matchImage({
            imageName: 'permissions_capability_single_site',
            comparisonThreshold: 0.0005
        });
    });

    it('should remove access to displayed rows when remove bulk access is clicked', async function () {
        // remove filters
        await page.evaluate(function () {
            $('.userPermissionsEdit div.site-filter>input').val('').change();
            $('.userPermissionsEdit .access-filter select').val('string:some').change();
        });

        await page.waitForNetworkIdle();
        await page.click('input#perm_edit_select_all + span');

        await page.waitFor('.userPermissionsEdit tr.select-all-row a');
        await page.click('.userPermissionsEdit tr.select-all-row a');

        await page.waitFor(250);

        await page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
        await (await page.jQuery('.userPermissionsEdit a:contains(Remove Permissions)')).click();

        await (await page.jQuery('.delete-access-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('permissions_remove_access');
    });

    it('should display the superuser access tab when the superuser tab is clicked', async function () {
        await page.click('.userEditForm .menuSuperuser');
        await page.mouse.move(0, 0);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('superuser_tab');
    });

    it('should show superuser confirm modal when the superuser toggle is clicked', async function () {
        await page.click('.userEditForm #superuser_access+span');
        await page.waitFor(500);

        const elem = await page.$('.superuser-confirm-modal');
        expect(await elem.screenshot()).to.matchImage('superuser_confirm');
    });

    it('should fail to set superuser access if password is wrong', async function () {
        await page.type('input#currentUserPasswordForSuperUser', 'wrongpassword');
        await (await page.jQuery('.superuser-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();

        await page.waitFor('.notification-error', { visible: true });

        const notificationHtml = await page.evaluate(() => $('.notification-error>div>div').html());
        expect(notificationHtml).to.equal('The current password you entered is not correct.');
    });

    it('should give the user superuser access when the superuser modal is confirmed', async function () {
        await page.click('.userEditForm #superuser_access+span');
        await page.waitFor(500);

        await page.type('input#currentUserPasswordForSuperUser', 'superUserPass');
        await (await page.jQuery('.superuser-confirm-modal .modal-close:not(.modal-no):visible')).click();
        await page.waitForNetworkIdle();
        await page.waitFor(500);

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('superuser_set');
    });

    it('should go back to the manage users page when the back link is clicked', async function () {
        await page.click('.userEditForm .entityCancelLink');
        await page.waitFor('piwik-paged-users-list');

        await page.evaluate(function () { // remove filter so new user shows
            $('#user-text-filter').val('').change();
        });
        await page.waitForNetworkIdle();
        await page.waitFor('.pagedUsersList:not(.loading)');
        await page.waitFor(1000); // rendering

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('manage_users_back');
    });

    it('should show the edit user form when the edit icon in a row is clicked', async function () {
        await (await page.jQuery('button.edituser:eq(1)', { waitFor: true })).click();
        await page.waitFor(250);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('.usersManager')).to.matchImage('edit_user_form');
    });

    it('should ask for password confirmation when trying to change email', async function () {
        await page.evaluate(function () {
            $('.userEditForm #user_email').val('testlogin3@example.com').change();
        });

        var btnSave = await page.jQuery('.userEditForm .basic-info-tab [piwik-save-button] .btn', { waitFor: true });
        await btnSave.click();

        await page.waitFor(500); // animation

        await page.click('.modal.open h2'); // remove focus from input for screenshot

        const elem = await page.$('.modal.open');
        expect(await elem.screenshot()).to.matchImage('edit_user_basic_asks_confirmation');
    });

    it('should show error when wrong password entered', async function () {
        await page.type('.modal.open #currentUserPassword', 'test123456');

        var btnNo = await page.jQuery('.change-password-modal .modal-close:not(.modal-no):visible');
        await btnNo.click();

        await page.waitFor(500); // animation
        await page.waitForNetworkIdle();
        await page.waitFor('#notificationContainer .notification');

        expect(await page.screenshotSelector('.admin#content,#notificationContainer')).to.matchImage('edit_user_basic_confirmed_wrong_password');
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

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_add_user');
        });

        it('should not allow editing basic info for admin users', async function () {
            await page.evaluate(function () {
                $('.userEditForm .entityCancelLink').click();
            });
            await (await page.jQuery('button.edituser:eq(0)')).click();
            await page.waitForNetworkIdle();

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('edit_user_basic_info');
        });

        it('should allow editing user permissions for admin users', async function () {
            await page.click('.userEditForm .menuPermissions');

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_edit_permissions');
        });

        it('should show the add existing user modal', async function () {
            await page.evaluate(function () {
                $('.userEditForm .entityCancelLink').click();
            });

            await page.click('.add-existing-user');
            await page.waitFor(500); // wait for animation

            const elem = await page.$('.add-existing-user-modal');
            expect(await elem.screenshot()).to.matchImage('admin_existing_user_modal');
        });

        it('should add a user by email when an email is entered', async function () {
            await page.type('input[name="add-existing-user-email"]', '0_login3conchords@example.com');
            await page.waitFor('.add-existing-user-modal');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show new user
                $('#user-text-filter').val('0_login3conchords@example.com').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitFor('.pagedUsersList:not(.loading)');
            await page.waitFor(1000); // for opacity to change

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_add_user_by_email');
        });

        it('should add a user by username when a username is entered', async function () {
            await page.click('.add-existing-user');
            await page.waitFor('.add-existing-user-modal');
            await page.evaluate(() => $('input[name="add-existing-user-email"]').val('').change());
            await page.type('input[name="add-existing-user-email"]', '10_login8');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show new user
                $('#user-text-filter').val('10_login8').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitFor('.pagedUsersList:not(.loading)');
            await page.waitFor(1000); // for opacity to change

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_add_user_by_login');
        });

        it('should fail if an email/username that does not exist is entered', async function () {
            await page.click('.add-existing-user');
            await page.evaluate(() => $('input[name="add-existing-user-email"]').val('').change());
            await page.type('input[name="add-existing-user-email"]', 'sldkjfsdlkfjsdkl');
            await page.waitFor('.add-existing-user-modal');
            await (await page.jQuery('.add-existing-user-modal .modal-close:not(.modal-no):visible')).click();
            await page.waitForNetworkIdle();

            await page.evaluate(function () { // show no user added
                $('#user-text-filter').val('sldkjfsdlkfjsdkl').change();
            });

            await page.mouse.move(-10, -10);

            await page.waitForNetworkIdle();
            await page.waitFor('.pagedUsersList:not(.loading)');
            await page.waitFor(1000); // for opacity to change

            expect(await page.screenshotSelector('.usersManager')).to.matchImage('admin_add_user_not_exists');
        });
    });
});