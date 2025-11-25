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

    it('should load the admin left menu correctly on mobile', async function() {
        await page.webpage.setViewport({ width: 815, height: 1500 });
        await page.goto("?module=CoreAdminHome&action=home");
        await page.waitForNetworkIdle();
        await page.click('[data-target="mobile-left-menu"]');
        await page.waitForTimeout(150);
        await page.click('ul#mobile-left-menu > li:nth-child(1) a');
        await page.click('ul#mobile-left-menu > li:nth-child(2) a');
        await page.click('ul#mobile-left-menu > li:nth-child(3) a');
        await page.click('ul#mobile-left-menu > li:nth-child(4) a');
        await page.click('ul#mobile-left-menu > li:nth-child(5) a');
        await page.click('ul#mobile-left-menu > li:nth-child(6) a');
        await page.waitForTimeout(500);

        expect(await page.screenshotSelector('#mobile-left-menu')).to.matchImage('mobile_left_admin');
    });

    it('should load the admin top menu correctly on mobile', async function() {
        await page.webpage.setViewport({ width: 768, height: 512 });
        await page.reload();
        await page.waitForNetworkIdle();
        await page.evaluate(function(){
            $('.activateTopMenu>span').click();
        });
        await page.waitForTimeout(250);

        expect(await page.screenshotSelector('#mobile-top-menu')).to.matchImage('mobile_top');
    });

    it('should load the left reporting menu correctly on mobile', async function() {
        await page.webpage.setViewport({ width: 768, height: 1200 });
        await page.goto("?" + generalParams + "&module=CoreHome&action=index#?category=General_Visitors&subcategory=General_Overview");
        await page.waitForNetworkIdle();
        await page.evaluate(function(){
            $('.activateLeftMenu>span').click();
        });
        await page.waitForTimeout(250);
        await (await page.jQuery('#mobile-left-menu>li>ul:contains(Goals)')).click();
        await page.waitForTimeout(500);

        expect(await page.screenshotSelector('#mobile-left-menu')).to.matchImage('mobile_left');
    });
});
