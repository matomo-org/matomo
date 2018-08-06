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

    it('should display the manage users page correctly', function (done) {
        expect.screenshot("load").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.load(url);
        }, done);
    });

    it('should change the results page when next is clicked', function (done) {
        expect.screenshot("next_click").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.usersListPagination .btn.next');
        }, done);
    });

    it('should filter by username and access level when the inputs are filled', function (done) {
        expect.screenshot("filters").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.sendKeys('#user-text-filter', 'ight');
            page.evaluate(function () {
                $('select[name=access-level-filter]').val('string:view').change();
            });
        }, done);
    });

    it('should display access for a different site when the roles for select is changed', function (done) {
        expect.screenshot("role_for").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            // remove access filter
            page.evaluate(function () {
                $('select[name=access-level-filter]').val('string:').change();
            });

            page.click('th.role_header .siteSelector a.title');
            page.click('.siteSelector .custom_select_container a:contains(relentless)');
        }, done);
    });

    it('should select rows when individual row select is clicked', function (done) {
        expect.screenshot("rows_selected").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('td.select-cell label:eq(0)');
            page.click('td.select-cell label:eq(3)');
            page.click('td.select-cell label:eq(8)');
        }, done);
    });

    it('should select all rows when all row select is clicked', function (done) {
        expect.screenshot("all_rows_selected").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('th.select-cell label');
        }, done);
    });

    it('should select all rows in search when link in table is clicked', function (done) {
        expect.screenshot("all_rows_in_search").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.toggle-select-all-in-search');
            page.sendMouseEvent('mousemove', { x: 0, y: 0 });
        }, done);
    });

    it('should deselect all rows in search except for displayed rows when link in table is clicked again', function (done) {
        expect.screenshot("all_rows_selected").to.be.captureSelector('all_rows_in_search_deselected', '.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.toggle-select-all-in-search');
            page.sendMouseEvent('mousemove', { x: 0, y: 0 });
        }, done);
    });

    it('should show bulk action confirm when bulk change access option used', function (done) {
        expect.screenshot("bulk_set_access_confirm").to.be.captureSelector('.change-user-role-confirm-modal', function (page) {
            page.setViewportSize(1250);

            // remove filters
            page.evaluate(function () {
                $('select[name=access-level-filter]').val('string:').change();
            });

            page.click('.toggle-select-all-in-search'); // reselect all in search

            page.click('.bulk-actions.btn');
            page.mouseMove('#user-list-bulk-actions>li:first');
            page.click('#bulk-set-access a:contains(Admin)');
        }, done);
    });

    it('should change access for all rows in search when confirmed', function (done) {
        expect.screenshot("bulk_set_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.change-user-role-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should remove access to the currently selected site when the bulk remove access option is clicked', function (done) {
        expect.screenshot("bulk_remove_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('th.select-cell label'); // select displayed rows

            page.click('.bulk-actions.btn');
            page.click('#user-list-bulk-actions a:contains(Remove Permissions)');
            page.click('.change-user-role-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should go back to first page when previous button is clicked', function (done) {
        expect.screenshot("previous").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.usersListPagination .btn.prev');
        }, done);
    });

    it('should delete a single user when the modal is confirmed is clicked', function (done) {
        expect.screenshot("delete_single").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.deleteuser:eq(0)');
            page.click('.delete-user-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should delete selected users when delete users bulk action is used', function (done) {
        expect.screenshot("delete_bulk_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('th.select-cell label'); // select displayed rows

            page.click('.bulk-actions.btn');
            page.click('#user-list-bulk-actions a:contains(Delete Users)');
            page.click('.delete-user-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should show the add new user form when the add new user button is clicked', function (done) {
        expect.screenshot("add_new_user_form").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.add-user-container .btn');
        }, done);
    });

    it('should create a user and show the edit user form when the create user button is clicked', function (done) {
        expect.screenshot("user_created").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.sendKeys('#user_login', '000newuser');
            page.sendKeys('#user_password', 'thepassword');
            page.sendKeys('#user_email', 'theuser@email.com');

            page.click('piwik-user-edit-form .siteSelector a.title');
            page.click('piwik-user-edit-form .siteSelector .custom_select_container a:eq(1)');

            page.click('piwik-user-edit-form [piwik-save-button]');
        }, done);
    });

    it('should show the permissions edit when the permissions tab is clicked', function (done) {
        expect.screenshot("permissions_edit").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userEditForm .menuPermissions');
            page.sendMouseEvent('mousemove', { x: 0, y: 0 });
        }, done);
    });

    it('should select all sites in search when in table link is clicked', function (done) {
        expect.screenshot("permissions_all_rows_in_search").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            // remove filters
            page.evaluate(function () {
                $('div.site-filter>input').val('').change();
                $('.access-filter select').val('string:').change();
            });

            page.click('.userPermissionsEdit th.select-cell label');
            page.click('.userPermissionsEdit tr.select-all-row a');
        }, done);
    });

    it('should add access to all websites when bulk access is used on all websites in search', function (done) {
        expect.screenshot("permissions_all_sites_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
            page.mouseMove('#user-permissions-edit-bulk-actions>li:first');
            page.click('#user-permissions-edit-bulk-actions a:contains(Write)');

            page.click('.change-access-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should go to the next results page when the next button is clicked', function (done) {
        expect.screenshot("permissions_next").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.sites-for-permission-pagination a.next');
        }, done);
    });

    it('should remove access to a single site when the trash icon is used', function (done) {
        expect.screenshot("permissions_remove_single").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('#sitesForPermission .deleteaccess');
            page.click('.delete-access-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should select multiple rows when individual row selects are clicked', function (done) {
        expect.screenshot("permissions_select_multiple").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('#sitesForPermission td.select-cell label:eq(0)');
            page.click('#sitesForPermission td.select-cell label:eq(3)');
            page.click('#sitesForPermission td.select-cell label:eq(8)');
        }, done);
    });

    it('should set access to selected sites when set bulk access is used', function (done) {
        expect.screenshot("permissions_bulk_access_set").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
            page.mouseMove('#user-permissions-edit-bulk-actions>li:first');
            page.click('#user-permissions-edit-bulk-actions a:contains(Admin)');

            page.click('.change-access-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should filter the permissions when the filters are used', function (done) {
        expect.screenshot("permissions_filters").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.sendKeys('div.site-filter>input', 'nova');
            page.evaluate(function () {
                $('.access-filter select').val('string:admin').change();
            });
        }, done);
    });

    it('should select all displayed rows when the select all checkbox is clicked', function (done) {
        expect.screenshot("permissions_select_all").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userPermissionsEdit th.select-cell label');
        }, done);
    });

    it('should set access to all sites selected when set bulk access is used', function (done) {
        expect.screenshot("permissions_bulk_access_set_all").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
            page.mouseMove('#user-permissions-edit-bulk-actions>li:first');
            page.click('#user-permissions-edit-bulk-actions a:contains(View)');

            page.click('.change-access-confirm-modal .modal-close:not(.modal-no)');

            page.evaluate(function () { // remove filter
                $('.access-filter select').val('string:some').change();
            });
        }, done);
    });

    it('should set access to single site when select in table is used', function (done) {
        expect.screenshot("permissions_single_site_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.evaluate(function () {
                $('.userPermissionsEdit tr select:eq(0)').val('string:admin').change();
            });

            page.click('.change-access-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should remove access to displayed rows when remove bulk access is clicked', function (done) {
        expect.screenshot("permissions_remove_access").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            // remove filters
            page.evaluate(function () {
                $('div.site-filter>input').val('').change();
                $('.access-filter select').val('string:').change();
            });

            page.click('.userPermissionsEdit th.select-cell label');
            page.click('.userPermissionsEdit tr.select-all-row a');

            page.click('.userPermissionsEdit .bulk-actions > .dropdown-trigger.btn');
            page.click('.userPermissionsEdit a:contains(Remove Permissions)');

            page.click('.delete-access-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should display the superuser access tab when the superuser tab is clicked', function (done) {
        expect.screenshot("superuser_tab").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userEditForm .menuSuperuser');
            page.sendMouseEvent('mousemove', { x: 0, y: 0 });
        }, done);
    });

    it('should show superuser confirm modal when the superuser toggle is clicked', function (done) {
        expect.screenshot("superuser_confirm").to.be.captureSelector('.superuser-confirm-modal', function (page) {
            page.setViewportSize(1250);

            page.click('.userEditForm #superuser_access+label');
        }, done);
    });

    it('should give the user superuser access when the superuser modal is confirmed', function (done) {
        expect.screenshot("superuser_set").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.superuser-confirm-modal .modal-close:not(.modal-no)');
        }, done);
    });

    it('should go back to the manage users page when the back link is clicked', function (done) {
        expect.screenshot("manage_users_back").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('.userEditForm .entityCancelLink');

            page.evaluate(function () { // remove filter so new user shows
                $('#user-text-filter').val('').change();
            });
        }, done);
    });

    it('should show the edit user form when the edit icon in a row is clicked', function (done) {
        expect.screenshot("edit_user_form").to.be.captureSelector('.admin#content', function (page) {
            page.setViewportSize(1250);

            page.click('button.edituser:eq(0)');
        }, done);
    });

    // admin user tests
    describe('UsersManager_admin_view', function () {
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

        it('should hide columns & functionality if an admin user views the manage user page', function (done) {
            expect.screenshot("admin_load").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.load(url);
            }, done);
        });

        it('should show the add user form for admin users', function (done) {
            expect.screenshot("admin_add_user").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.click('.add-user-container .btn');
            }, done);
        });

        it('should not allow editing basic info for admin users', function (done) {
            expect.screenshot("edit_user_basic_info").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.evaluate(function () {
                    $('.userEditForm .entityCancelLink').click();
                });
                page.click('button.edituser:eq(0)');
            }, done);
        });

        it('should allow editing user permissions for admin users', function (done) {
            expect.screenshot("admin_edit_permissions").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.click('.userEditForm .menuPermissions');
            }, done);
        });

        it('should show the add existing user modal', function (done) {
            expect.screenshot("admin_existing_user_modal").to.be.captureSelector('.add-existing-user-modal', function (page) {
                page.setViewportSize(1250);

                page.evaluate(function () {
                    $('.userEditForm .entityCancelLink').click();
                });

                page.click('.add-existing-user');
            }, done);
        });

        it('should add a user by email when an email is entered', function (done) {
            expect.screenshot("admin_add_user_by_email").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.sendKeys('input[name=add-existing-user-email]', '0_login3conchords@example.com');
                page.click('.add-existing-user-modal .modal-close:not(.modal-no)');

                page.evaluate(function () { // show new user
                    $('#user-text-filter').val('0_login3conchords@example.com').change();
                });
            }, done);
        });

        it('should add a user by username when a username is entered', function (done) {
            expect.screenshot("admin_add_user_by_login").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.click('.add-existing-user');
                page.sendKeys('input[name=add-existing-user-email]', '10_login8');
                page.click('.add-existing-user-modal .modal-close:not(.modal-no)');

                page.evaluate(function () { // show new user
                    $('#user-text-filter').val('10_login8').change();
                });
            }, done);
        });

        it('should fail if an email/username that does not exist is entered', function (done) {
            expect.screenshot("admin_add_user_not_exists").to.be.captureSelector('.admin#content', function (page) {
                page.setViewportSize(1250);

                page.click('.add-existing-user');
                page.sendKeys('input[name=add-existing-user-email]', 'sldkjfsdlkfjsdkl');
                page.click('.add-existing-user-modal .modal-close:not(.modal-no)');

                page.evaluate(function () { // show no user added
                    $('#user-text-filter').val('sldkjfsdlkfjsdkl').change();
                });
            }, done);
        });
    });
});