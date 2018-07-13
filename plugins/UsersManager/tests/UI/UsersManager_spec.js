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

    before(function () {
        testEnvironment.idSitesAdminAccess = [1,2];
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.idSitesAdminAccess;
        testEnvironment.save();
    });

    it('should display the manage users page correctly', function () {
        // TODO
    });

    it('should change the results page when next is clicked', function () {
        // TODO
    });

    it('should filter by username and access level when the inputs are filled', function () {
        // TODO
    });

    it('should display access for a different site when the roles for select is changed', function () {
        // TODO
    });

    it('should select rows when individual row select is clicked', function () {
        // TODO
    });

    it('should select all rows when all row select is clicked', function () {
        // TODO
    });

    it('should select all rows in search when link in table is clicked', function () {
        // TODO
    });

    it('should change access for all rows in search when bulk change access option used', function () {
        // TODO
    });

    it('should deselect all rows in search except for displayed rows when link in table is clicked again', function () {
        // TODO
    });

    it('should remove access to the currently selected site when the bulk remove access option is clicked', function () {
        // TODO
    });

    it('should go back to first page when previous button is clicked', function () {
        // TODO
    });

    it('should delete a single user when the delete icon is clicked', function () {
        // TODO
    });

    it('should delete selected users when delete users bulk action is used', function () {
        // TODO
    });

    it('should show the add new user form when the add new user button is clicked', function () {
        // TODO
    });

    it('should create a user and show the edit user form when the create user button is clicked', function () {
        // TODO
    });

    it('should show the permissions edit when the permissions tab is clicked', function () {
        // TODO
    });

    it('should add access to all websites when all websites selected in the site selector', function () {
        // TODO
    });

    it('should go to the next results page when the next button is clicked', function () {
        // TODO
    });

    it('should filter the permissions when the filters are used', function () {
        // TODO
    });

    it('should remove access to a single site when the trash icon is used', function () {
        // TODO
    });

    it('should select multiple rows when individual row selects are clicked', function () {
        // TODO
    });

    it('should select all displayed rows when the select all checkbox is clicked', function () {
        // TODO
    });

    it('should remove access to displayed rows when remove bulk access is clicked', function () {
        // TODO
    });

    it('should select all sites in search when in table link is clicked', function () {
        // TODO
    });

    it('should set access to single site when select in table is used', function () {
        // TODO
    });

    it('should set access to all sites in search when set bulk access is used', function () {
        // TODO
    });

    it('should display the superuser access tab when the superuser tab is clicked', function () {
        // TODO
    });

    it('should give the user superuser access when the superuser toggle is clicked', function () {
        // TODO
    });

    it('should go back to the manage users page when the back link is clicked', function () {
        // TODO
    });

    it('should show the edit user form when the edit icon in a row is clicked', function () {
        // TODO
    });

    it('should hide columns & functionality if an admin user views the manage user page', function () {
        // TODO
    });

    it('should show the add user form for admin users', function () {
        // TODO
    });

    it('should allow editing user permissions for admin users but not basic info', function () {
        // TODO
    });
});