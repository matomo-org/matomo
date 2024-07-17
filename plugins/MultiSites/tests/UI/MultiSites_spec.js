/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests for MultiSites.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("MultiSitesTest", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    var generalParams = 'idSite=1&period=year&date=2012-08-09';
    var rangeParams = 'idSite=1&period=range&date=2012-08-05,2012-08-15';
    var selector = '#multisites,.expandDataTableFooterDrawer';

    var createdSiteIds = [];

    before(async function() {
        var response = await testEnvironment.callApi("SitesManager.addSite", {
            siteName: '%3CMy%20website%22%27%3E%3B%2C%3F with a very very very very long stupid name',
            urls: 'http%3A%2F%2Fpiwik.org%2F'
        });

        createdSiteIds.push(response.value);

        for (var i = 0; i < 50; i++) {
            var response = await testEnvironment.callApi("SitesManager.addSite", {
                siteName: 'dynamically created page ' + i,
                urls: 'http%3A%2F%2Fpiwik.org%2F' + i
            });

            createdSiteIds.push(response.value);
        }
    });

    after(async function() {
        const promises = createdSiteIds.map(async function(createdSiteId) {
            return testEnvironment.callApi("SitesManager.deleteSite", {idSite: createdSiteId});
        });

        await Promise.all(promises);
    });

    it('should load the all websites dashboard correctly', async function() {
        await page.goto("?" + generalParams + "&module=MultiSites&action=index");
        await page.waitForTimeout(500);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selector)).to.matchImage('all_websites');
    });

    it('should load next page correctly', async function() {
        await page.click('.paging .next');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selector)).to.matchImage('all_websites_page_1');
    });

    it('should search correctly', async function() {
        await page.type('.site_search input', 'Site');
        await page.evaluate(function() {
            $('.site_search .search_ico').click();
        });
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selector)).to.matchImage('all_websites_search');
    });

    it('should toggle sort order when click on current metric', async function() {
        await page.click('#visits .heading');
        await page.mouse.move(-10, -10);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector(selector)).to.matchImage('all_websites_changed_sort_order');
    });

    it('should load the all websites dashboard correctly when period is range', async function () {
        await page.goto("?" + rangeParams + "&module=MultiSites&action=index");
        await page.waitForNetworkIdle();
        expect(await page.screenshotSelector(selector)).to.matchImage('all_websites_range');
    });
});
