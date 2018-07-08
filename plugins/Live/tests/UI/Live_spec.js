/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Live", function () {
    this.timeout(0);

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

        var report = await page.$('.reporting-page');
        expect(await report.screenshot()).to.matchImage('visitor_log');
    });

    it('should expand grouped actions', async function() {
        await page.click('.dataTableVizVisitorLog .repeat.icon-refresh');
        await page.mouse.move(-10, -10);

        var report = await page.$('.dataTableVizVisitorLog .card.row:first-child');
        expect(await report.screenshot()).to.matchImage('visitor_log_expand_actions');
    });

    it('should show visitor profile', async function() {
        await page.evaluate(function(){
            $('.card:first-child .visitor-log-visitor-profile-link').click();
        });

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile');
    });

    it('should hide all action details', async function() {
        await page.evaluate(function(){
            $('.visitor-profile-toggle-actions').click();
        });

        await page.mouse.move(0, 0);

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_actions_hidden');
    });

    it('should show visit details', async function() {
        await page.evaluate(function(){
            $('.visitor-profile-visit-title')[0].click();
        });

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_visit_details');
    });

    it('should show action details', async function() {
        await page.click('.visitor-profile-visits li:first-child .visitor-profile-show-actions');

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_action_details');
    });

    it('should show action tooltip', async function() {
        var action = await page.jQuery('.visitor-profile-visits li:first-child .visitor-profile-actions .action:first-child');
        await action.hover();
        await page.waitForSelector('.ui-tooltip');

        expect(await page.screenshotSelector('.ui-tooltip')).to.matchImage('visitor_profile_action_tooltip');
    });

    it('should show limited profile message', async function () {
        // Limit number of shown visits to 5
        testEnvironment.overrideConfig('General', 'live_visitor_profile_max_visits_to_aggregate', 5);
        testEnvironment.save();

        await page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
        await page.evaluate(function(){
            $('.card:first-child .visitor-log-visitor-profile-link').click();
        });

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();
        await page.mouse.move(-10, -10);

        var dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('visitor_profile_limited');
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
