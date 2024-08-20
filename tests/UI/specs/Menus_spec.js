/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot tests for main, top and admin menus.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Menus", function () {
    this.fixture = "Piwik\\Tests\\Fixtures\\ThreeGoalsOnePageview";

    const generalParams = 'idSite=1&period=year&date=2009-01-04',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    async function openMenuItem(page, menuItem) {
        const element = await page.jQuery('#secondNavBar .navbar a:contains(' + menuItem + '):visible:first');
        await element.click();
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(250);
    }

    beforeEach(function() {
        if (testEnvironment.enableProfessionalSupportAdsForUITests) {
          delete testEnvironment.enableProfessionalSupportAdsForUITests;
          testEnvironment.save();
        }
    });

    // main menu tests
    it('should load the main reporting menu correctly', async function() {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");
        await page.waitForSelector('#secondNavBar', { visible: true });

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('mainmenu_loaded');
    });

    // main menu with plugin promos (reloads the previous test's page with new config)
    it('should load the main reporting menu with plugin promos correctly', async function() {
        testEnvironment.enableProfessionalSupportAdsForUITests = true;
        await testEnvironment.save();

        await page.reload(); // use URL from the previous test and reload to apply the config changes
        await page.waitForSelector('#secondNavBar', { visible: true });

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('mainmenu_loaded_withpromos');
    });

    it('should change the menu when a upper menu item is clicked in the main menu', async function() {
        // reload to remove config override set by previous tests
        await page.reload(); // use URL from the previous test and reload to apply the config changes
        await page.waitForSelector('#secondNavBar', { visible: true });

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
        await page.waitForSelector('#secondNavBar');

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('admin_loaded');
    });

    it('should toggle the submenu visibility when main item is clicked', async function() {
        await openMenuItem(page, 'Website');
        await page.waitForTimeout(500); // wait for animation

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('admin_websites');
    });

    it('should change the admin page correctly when an admin menu item is clicked', async function() {
        await openMenuItem(page, 'Manage');
        await page.waitForNetworkIdle();
        await page.waitForSelector('#secondNavBar');

        const element = await page.jQuery('#secondNavBar');
        expect(await element.screenshot()).to.matchImage('admin_changed');
    });

    // top menu on mobile
    it('should load the admin reporting menu correctly on mobile', async function() {
        page.webpage.setViewport({ width: 768, height: 512 });
        await page.goto("?" + generalParams + "&module=CoreAdminHome&action=index");
        await page.waitForSelector('.pageWrap');
        await page.evaluate(function(){
            $('.activateTopMenu>span').click();
        });
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_top');
    });

    it('should load the TagManager menu correctly on mobile', async function() {
        page.webpage.setViewport({ width: 768, height: 512 });
        await page.goto("?" + generalParams + "&module=CoreAdminHome&action=index");
        await page.waitForSelector('.pageWrap');
        await page.evaluate(function(){
            $('.activateTopMenu>span').click();
            $('#topmenu-tagmanager')[0].click()
        });
        await page.waitForNetworkIdle();
        await page.waitForTimeout(250);
        await page.evaluate(function(){
          $('.icon-configure')[0].click();
        });
        await page.waitForNetworkIdle();
        await page.evaluate(function(){
          $('.activateLeftMenu>span').click();
          $('#mobile-left-menu .icon-chevron-down').click();
        });
        await page.waitForTimeout(250);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_tag_manager_left_menu');
    });

    // left menu on mobile
    it('should load the admin reporting menu correctly on mobile', async function() {
        page.webpage.setViewport({ width: 768, height: 512 });
        await page.goto("?" + generalParams + "&module=CoreHome&action=index");
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();
        await page.evaluate(function(){
            $('.activateLeftMenu>span').click();
        });
        await page.waitForTimeout(250);
        await (await page.jQuery('#mobile-left-menu>li>ul:contains(Goals)')).click();
        await page.waitForTimeout(300);

        expect(await page.screenshot({ fullPage: true })).to.matchImage('mobile_left');
    });
});
