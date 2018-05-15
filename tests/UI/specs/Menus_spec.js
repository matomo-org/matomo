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

    this.fixture = "Piwik\\Tests\\Fixtures\\ThreeGoalsOnePageview";

    var generalParams = 'idSite=1&period=year&date=2009-01-04',
        urlBase = 'module=CoreHome&action=index&' + generalParams
        ;

    function openMenuItem(page, menuItem)
    {
        page.click('#secondNavBar .navbar a:contains('+ menuItem + '):first');
    }

    // main menu tests
    it('should load the main reporting menu correctly', function (done) {
        expect.screenshot('mainmenu_loaded').to.be.captureSelector('#secondNavBar', function (page) {
            page.load("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");
        }, done);
    });

    it('should change the menu when a upper menu item is clicked in the main menu', function (done) {
        expect.screenshot('mainmenu_upper_clicked').to.be.captureSelector('#secondNavBar', function (page) {
            openMenuItem(page, 'Visitors');
        }, done);
    });

    it('should change the menu when a lower menu item is clicked in the main menu', function (done) {
        expect.screenshot('mainmenu_lower_clicked').to.be.captureSelector('#secondNavBar', function (page) {
            openMenuItem(page, 'Custom Variables');
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
            openMenuItem(page, 'Manage');
        }, done);
    });

    // top menu on mobile
    it('should load the admin reporting menu correctly', function (done) {
        expect.screenshot('mobile_top').to.be.capture(function (page) {
            page.setViewportSize(768, 512);
            page.load("?" + generalParams + "&module=CoreAdminHome&action=index");
            page.evaluate(function(){
                $('.activateTopMenu').click();
            }, 250);
        }, done);
    });
});
