/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot tests for main, top and admin menus.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Menus", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBase = 'module=CoreHome&action=index&' + generalParams
        ;

    function openMenuItem(page, menuItem)
    {
        page.click('#secondNavBar .navbar a:contains('+ menuItem + ')');
    }

    // main menu tests
    it('should load the main reporting menu correctly', function (done) {
        expect.screenshot('mainmenu_loaded').to.be.captureSelector('#secondNavBar', function (page) {
            page.load("?" + urlBase + "#" + generalParams + "&module=Actions&action=menuGetPageUrls");
        }, done);
    });

    it('should change the menu when a upper menu item is clicked in the main menu', function (done) {
        expect.screenshot('mainmenu_upper_clicked').to.be.captureSelector('#secondNavBar', function (page) {
            page.click('#secondNavBar .navbar > li:eq(1) > a');
        }, done);
    });

    it('should change the menu when a lower menu item is clicked in the main menu', function (done) {
        expect.screenshot('mainmenu_lower_clicked').to.be.captureSelector('#secondNavBar', function (page) {
            page.click('#secondNavBar .navbar > li:eq(1) > ul > li:eq(1) > a');
        }, done);
    });

    // user menu tests
    it('should load the user reporting menu correctly', function (done) {
        expect.screenshot('user_loaded').to.be.captureSelector('#secondNavBar', function (page) {
            page.load("?" + generalParams + "&module=UsersManager&action=userSettings");
        }, done);
    });

    it('should change the user page correctly when a user menu item is clicked', function (done) {
        expect.screenshot('user_changed').to.be.captureSelector('#secondNavBar', function (page) {
            openMenuItem(page, 'Platform');
            openMenuItem(page, 'API');
        }, done);
    });

    // admin menu tests
    it('should load the admin reporting menu correctly', function (done) {
        expect.screenshot('admin_loaded').to.be.captureSelector('#secondNavBar', function (page) {
            page.load("?" + generalParams + "&module=CoreAdminHome&action=generalSettings");
        }, done);
    });

    it('should change the admin page correctly when an admin menu item is clicked', function (done) {
        expect.screenshot('admin_changed').to.be.captureSelector('#secondNavBar', function (page) {
            openMenuItem(page, 'Administration');
            openMenuItem(page, 'Websites');
        }, done);
    });
});