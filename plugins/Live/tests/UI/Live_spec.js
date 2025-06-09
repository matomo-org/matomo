/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Live", function () {
    this.fixture = "Piwik\\Plugins\\Live\\tests\\Fixtures\\VisitsWithAllActionsAndDevices";

    after(function () {
        if (testEnvironment.configOverride.Deletelogs) {
            delete testEnvironment.configOverride.Deletelogs;
            testEnvironment.save();
        }
    });

    it('should show visitor log', async function() {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");

        await page.waitForNetworkIdle();
        await page.waitForSelector('.dataTableVizVisitorLog');

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('visitor_log');
    });

    it('should expand grouped actions', async function() {
        await page.evaluate(() => $('.dataTableVizVisitorLog .repeat.icon-refresh').click());
        await page.mouse.move(-10, -10);

        const report = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(1)');
        expect(await report.screenshot()).to.matchImage('visitor_log_expand_actions');
    });

    it('should expand collapsed pageview actions', async function() {
        const link = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(1) .show-more-actions:visible');
        await link.click();

        await page.mouse.move(-10, -10);

        const report = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(1)');
        expect(await report.screenshot()).to.matchImage('visitor_log_expand_pageview_actions');
    });

    it('should expand collapsed content actions', async function() {
        // collapse previously expanded section
        const prevlink = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(1) .show-less-actions:visible');
        await prevlink.click();

        const link = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(2) .collapsed-contents:visible');
        await link.click();

        await page.mouse.move(-10, -10);

        const report = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(2)');
        expect(await report.screenshot()).to.matchImage('visitor_log_expand_content_actions');
    });

    it('should show visitor profile', async function() {
        // collapse previously expanded section
        const prevlink = await page.jQuery('.dataTableVizVisitorLog .card.row:eq(2) .collapsed-contents:visible');
        await prevlink.click();

        await (await page.jQuery('.card:eq(0) .visitor-log-visitor-profile-link')).click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile');
    });

    it('should load additional visits in visitor log', async function() {

        await page.click('.visitor-profile-more-info a');

        await page.waitForNetworkIdle();

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_more_visits');
    });

    it('should hide all action details', async function() {
        await (await page.jQuery('.visitor-profile-toggle-actions')).click();

        await page.mouse.move(0, 0);

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_actions_hidden');
    });

    it('should show visit details', async function() {
        await (await page.jQuery('.visitor-profile-visit-title:eq(0)')).click();

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_visit_details');
    });

    it('should show action details', async function() {
        await page.click('.visitor-profile-visits li:first-child .visitor-profile-show-actions');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_action_details');
    });

    it('should show action tooltip', async function() {
        await page.hover('.visitor-profile-visits li:first-child .visitor-profile-actions .action:first-child');
        await page.waitForSelector('.ui-tooltip', {visible: true, timeout: 250});

        expect(await page.screenshotSelector('.ui-tooltip')).to.matchImage('visitor_profile_action_tooltip');
    });

    it('should show limited profile message', async function () {
        // Limit number of shown visits to 5
        testEnvironment.overrideConfig('General', 'live_visitor_profile_max_visits_to_aggregate', 5);
        testEnvironment.save();

        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
        await (await page.jQuery('.card:eq(0) .visitor-log-visitor-profile-link')).click();

        await page.waitForSelector('.ui-dialog', {visible: true});
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_limited');
    });

    it('should show visitor log next page', async function() {
        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");

        await page.waitForNetworkIdle();
        await page.waitForSelector('.dataTableVizVisitorLog');

        const link = await page.jQuery('.dataTableNext');
        await link.click();
        await page.waitForNetworkIdle();

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('visitor_log_page_next');
    });

    it('should show visitor log purge message when purged and no data', async function() {
        testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', 1);
        testEnvironment.overrideConfig('Deletelogs', 'delete_logs_older_than', 4000);
        testEnvironment.save();

        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2005-01-03#?idSite=1&period=year&date=2005-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.waitForNetworkIdle();

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('visitor_log_purged');
    });
});
