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

    // TODO: lots of modals are missing...
    it('should display the manage users page correctly', function () {
        expect.screenshot("load").to.be.captureSelector('.admin#content', function (page) {
            page.load(url);
        }, done);
    });

    it('should change the results page when next is clicked', function () {
        expect.screenshot("next_click").to.be.captureSelector('.admin#content', function (page) {
            page.click('.usersListPagination .btn.next');
        }, done);
    });

    it('should filter by username and access level when the inputs are filled', function () {
        expect.screenshot("filters").to.be.captureSelector('.admin#content', function (page) {
            page.sendKeys('#user-text-filter', 'ight');
            page.evaluate(function () {
                $('select[name=access-level-filter]').val('string:view').change();
            });
        }, done);
    });

    it('should display access for a different site when the roles for select is changed', function () {
        expect.screenshot("role_for").to.be.captureSelector('.admin#content', function (page) {
            page.click('th.role_header .siteSelector');
            page.click('.siteSelector .custom_select_container a:eq(2)');
        }, done);
    });

    it('should select rows when individual row select is clicked', function () {
        expect.screenshot("rows_selected").to.be.captureSelector('.admin#content', function (page) {
            page.click('td.select-cell label:eq(0)');
            page.click('td.select-cell label:eq(3)');
            page.click('td.select-cell label:eq(8)');
        }, done);
    });

    it('should select all rows when all row select is clicked', function () {
        expect.screenshot("all_rows_selected").to.be.captureSelector('.admin#content', function (page) {
            page.click('th.select-cell label');
        }, done);
    });

    it('should select all rows in search when link in table is clicked', function () {
        expect.screenshot("all_rows_in_search").to.be.captureSelector('.admin#content', function (page) {
            page.click('.toggle-select-all-in-search');
        }, done);
    });

    it('should deselect all rows in search except for displayed rows when link in table is clicked again', function () {
        expect.screenshot("all_rows_in_search_deselected").to.be.captureSelector('.admin#content', function (page) {
            page.click('.toggle-select-all-in-search');
        }, done);
    });

    it('should change access for all rows in search when bulk change access option used', function () {
        expect.screenshot("bulk_set_access").to.be.captureSelector('.admin#content', function (page) {
            page.click('.toggle-select-all-in-search'); // reselect all in search

            // remove filters
            page.sendKeys('#user-text-filter', '');
            page.evaluate(function () {
                $('select[name=access-level-filter]').val('string:').change();
            });

            page.click('.bulk-actions.btn');
            page.click('#bulk-set-access a:contains(Admin)');
        }, done);
    });

    it('should remove access to the currently selected site when the bulk remove access option is clicked', function () {
        expect.screenshot("bulk_remove_access").to.be.captureSelector('.admin#content', function (page) {
            page.click('th.select-cell label'); // select displayed rows

            page.click('.bulk-actions.btn');
            page.click('#bulk-set-access a:contains(Remove Access)');
        }, done);
    });

    it('should go back to first page when previous button is clicked', function () {
        expect.screenshot("previous").to.be.captureSelector('.admin#content', function (page) {
            page.click('.usersListPagination .btn.prev');
        }, done);
    });

    it('should delete a single user when the delete icon is clicked', function () {
        expect.screenshot("delete_single").to.be.captureSelector('.admin#content', function (page) {
            page.click('.deleteuser:eq(0)');
        }, done);
    });

    it('should delete selected users when delete users bulk action is used', function () {
        expect.screenshot("delete_bulk_access").to.be.captureSelector('.admin#content', function (page) {
            page.click('th.select-cell label'); // select displayed rows

            page.click('.bulk-actions.btn');
            page.click('.userListFilters a:contains(Delete Users)');
        }, done);
    });

    it('should show the add new user form when the add new user button is clicked', function () {
        expect.screenshot("add_new_user_form").to.be.captureSelector('.admin#content', function (page) {
            page.click('.add-user-container .btn');
        }, done);
    });

    it('should create a user and show the edit user form when the create user button is clicked', function () {
        expect.screenshot("user_created").to.be.captureSelector('.admin#content', function (page) {
            page.sendKeys('#user_login', 'aaanewuser');
            page.sendKeys('#user_password', 'thepassword');
            page.sendKeys('#user_email', 'theuser@email.com');

            page.click('piwik-user-edit-form .siteSelector');
            page.click('piwik-user-edit-form .siteSelector .custom_select_container a:eq(1)');

            page.click('piwik-user-edit-form [piwik-save-button]');
        }, done);
    });

    it('should show the permissions edit when the permissions tab is clicked', function () {
        expect.screenshot("permissions_edit").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userEditForm .menuPermissions');
        }, done);
    });

    it('should add access to all websites when all websites selected in the site selector', function () {
        expect.screenshot("permissions_all_sites_access").to.be.captureSelector('.admin#content', function (page) {
            page.click('.add-site-selector .siteSelector');
            page.click('.add-site-selector .siteSelector a:contains(All Websites)');

            page.click('.btn-flat .icon-add');
        }, done);
    });

    it('should go to the next results page when the next button is clicked', function () {
        expect.screenshot("permissions_next").to.be.captureSelector('.admin#content', function (page) {
            page.click('.sites-for-permission-pagination a.next');
        }, done);
    });

    it('should remove access to a single site when the trash icon is used', function () {
        expect.screenshot("permissions_remove_single").to.be.captureSelector('.admin#content', function (page) {
            page.click('#sitesForPermission .deleteaccess');
        }, done);
    });

    it('should select multiple rows when individual row selects are clicked', function () {
        expect.screenshot("permissions_select_multiple").to.be.captureSelector('.admin#content', function (page) {
            page.click('td.select-cell label:eq(0)');
            page.click('td.select-cell label:eq(3)');
            page.click('td.select-cell label:eq(8)');
        }, done);
    });

    it('should set access to selected sites when set bulk access is used', function () {
        expect.screenshot("permissions_bulk_access_set").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger-btn');
            page.click('#user-permissions-edit-bulk-actions a:contains(Admin)');
        }, done);
    });

    it('should filter the permissions when the filters are used', function () {
        expect.screenshot("permissions_filters").to.be.captureSelector('.admin#content', function (page) {
            page.sendKeys('div.site-filter>input', 'ight');
            $('.access-filter select').val('string:admin').change();
        }, done);
    });

    it('should select all displayed rows when the select all checkbox is clicked', function () {
        expect.screenshot("permissions_select_all").to.be.captureSelector('.admin#content', function (page) {
            page.click('th.select-cell label');
        }, done);
    });

    it('should remove access to displayed rows when remove bulk access is clicked', function () {
        expect.screenshot("permissions_remove_access").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger-btn');
            page.click('.userPermissionsEdit a:contains(Remove Access)');
        }, done);
    });

    it('should select all sites in search when in table link is clicked', function () {
        expect.screenshot("permissions_all_rows_in_search").to.be.captureSelector('.admin#content', function (page) {
            page.click('tr.select-all-row a');
        }, done);
    });

    it('should set access to all sites in search when set bulk access is used', function () {
        expect.screenshot("permissions_bulk_access_set").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger-btn');
            page.click('#user-permissions-edit-bulk-actions a:contains(View)');

            // remove filters
            page.sendKeys('div.site-filter>input', '');
            $('.access-filter select').val('string:some').change();
        }, done);
    });

    it('should set access to single site when select in table is used', function () {
        expect.screenshot("permissions_single_site_access").to.be.captureSelector('.admin#content', function (page) {
            page.evaluate(function () {
                $('.userPermissionsEdit tr select:eq(0)').val('string:admin').change();
            });
        }, done);
    });

    it('should display the superuser access tab when the superuser tab is clicked', function () {
        expect.screenshot("superuser_tab").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userEditForm .menuSuperuser');
        }, done);
    });

    it('should show superuser confirm modal when the superuser toggle is clicked', function () {
        expect.screenshot("superuser_confirm").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userEditForm #superuser_access+label');
        }, done);
    });

    it('should give the user superuser access when the superuser modal is confirmed', function () {
        expect.screenshot("superuser_set").to.be.captureSelector('.admin#content', function (page) {
            page.click('.superuser-confirm-modal .modal-yes');
        }, done);
    });

    it('should go back to the manage users page when the back link is clicked', function () {
        expect.screenshot("manage_users_back").to.be.captureSelector('.admin#content', function (page) {
            page.click('.userEditForm .entityCancelLink');
        }, done);
    });

    it('should show the edit user form when the edit icon in a row is clicked', function () {
        expect.screenshot("edit_user_form").to.be.captureSelector('.admin#content', function (page) {
            page.click('button.edituser:eq(0)');
        }, done);
    });

    // admin user tests
    describe('admin view', function () {
        before(function () {
            var idSites = [];
            for (var i = 1; i !== 46; ++i) {
                idSites.push(i);
            }

            testEnvironment.idSitesAdminAccess = idSites;
            testEnvironment.save();
        });

        after(function () {
            delete testEnvironment.idSitesAdminAccess;
            testEnvironment.save();
        });

        it('should hide columns & functionality if an admin user views the manage user page', function () {
            expect.screenshot("admin_load").to.be.captureSelector('.admin#content', function (page) {
                page.load(url);
            }, done);
        });

        it('should show the add user form for admin users', function () {
            expect.screenshot("admin_add_user").to.be.captureSelector('.admin#content', function (page) {
                page.click('.add-user-container .btn');
            }, done);
        });

        it('should not allow editing basic info for admin users', function () {
            expect.screenshot("edit_user_basic_info").to.be.captureSelector('.admin#content', function (page) {
                page.click('button.edituser:eq(0)');
            }, done);
        });

        it('should allow editing user permissions for admin users', function () {
            expect.screenshot("admin_edit_permissions").to.be.captureSelector('.admin#content', function (page) {
                page.click('.userEditForm .menuPermissions');
            }, done);
        });

        it('should show the add existing user modal', function () {
            expect.screenshot("admin_existing_user_modal").to.be.captureSelector('.admin#content', function (page) {
                page.click('.userEditForm .add-existing-user');
            }, done);
        });

        it('should add a user by email when an email is entered', function () {
            expect.screenshot("admin_add_user_by_email").to.be.captureSelector('.admin#content', function (page) {
                // TODO
            }, done);
        });

        it('should add a user by username when a username is entered', function () {
            expect.screenshot("admin_add_user_by_login").to.be.captureSelector('.admin#content', function (page) {
                // TODO
            }, done);
        });

        it('should fail if an email/username that does not exist is entered', function () {
            expect.screenshot("admin_add_user_not_exists").to.be.captureSelector('.admin#content', function (page) {
                // TODO
            }, done);
        });
    });
});