/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot tests for main, top and admin menus.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Menus", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\ThreeGoalsOnePageview";

    const generalParams = 'idSite=1&period=year&date=2009-01-04',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    async function openMenuItem(page, menuItem) {
        const element = await page.jQuery('#secondNavBar .navbar a:contains(' + menuItem + '):first');
        await element.click();
    }

    // main menu tests
    it('should load the main reporting menu correctly', async function() {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('mainmenu_loaded');
    });

    it('should change the menu when a upper menu item is clicked in the main menu', async function() {
        await openMenuItem(page, 'Visitors');

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('mainmenu_upper_clicked');
    });

    it('should change the menu when a lower menu item is clicked in the main menu', async function() {
        await openMenuItem(page, 'Custom Variables');

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('mainmenu_lower_clicked');
    });

    // admin menu tests
    it('should load the admin reporting menu correctly', async function() {
        await page.goto("?" + generalParams + "&module=CoreAdminHome&action=generalSettings");

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('admin_loaded');
    });

    it('should change the admin page correctly when an admin menu item is clicked', async function() {
        await openMenuItem(page, 'Manage');
        await page.waitForNetworkIdle();

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('admin_changed');
    });

    // top menu on mobile
    it('should load the admin reporting menu correctly', async function() {
        page.webpage.setViewport({ width: 768, height: 512 });
        await page.goto("?" + generalParams + "&module=CoreAdminHome&action=index");
        await page.evaluate(function(){
            $('.activateTopMenu').click();
        });
        await page.waitFor(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_top');
    });
});
