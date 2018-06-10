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
        expect.screenshot('visitor_log').to.be.captureSelector('.reporting-page', function (page) {
            page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
            page.wait(4500);
        }, done);
    });

    it('should expand grouped actions', async function() {
        expect.screenshot('visitor_log_expand_actions').to.be.captureSelector('.dataTableVizVisitorLog .card.row:first-child', function (page) {
            page.click('.dataTableVizVisitorLog .repeat.icon-refresh');
        }, done);
    });

    it('should show visitor profile', async function() {
        expect.screenshot('visitor_profile').to.be.captureSelector('.ui-dialog', function (page) {
            page.evaluate(function(){
                $('.card:first-child .visitor-log-visitor-profile-link').click();
            });
            page.wait(6000);
        }, done);
    });

    it('should hide all action details', async function() {
        expect.screenshot('visitor_profile_actions_hidden').to.be.captureSelector('.ui-dialog', function (page) {
            page.evaluate(function(){
                $('.visitor-profile-toggle-actions').click();
            }, 500);
        }, done);
    });

    it('should show visit details', async function() {
        expect.screenshot('visitor_profile_visit_details').to.be.captureSelector('.ui-dialog', function (page) {
            page.evaluate(function(){
                $('.visitor-profile-visit-title')[0].click();
            }, 200);
        }, done);
    });

    it('should show action details', async function() {
        expect.screenshot('visitor_profile_action_details').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('.visitor-profile-visits li:first-child .visitor-profile-show-actions', 200);
        }, done);
    });

    it('should show action tooltip', async function() {
        expect.screenshot('visitor_profile_action_tooltip').to.be.captureSelector('.ui-tooltip:visible', function (page) {
            page.mouseMove('.visitor-profile-visits li:first-child .visitor-profile-actions .action:first-child', 200);
        }, done);
    });

    it('should show limited profile message', async function (done) {
        expect.screenshot('visitor_profile_limited').to.be.captureSelector('.ui-dialog', function (page) {

            // Limit number of shown visits to 5
            testEnvironment.overrideConfig('General', 'live_visitor_profile_max_visits_to_aggregate', 5);
            testEnvironment.save();

            page.load("?module=CoreHome&action=index&idSite=1&period=year&date=2010-01-03#?idSite=1&period=year&date=2010-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
            page.evaluate(function(){
                $('.card:first-child .visitor-log-visitor-profile-link').click();
            });
            page.wait(6000);
        }, done);
    });

    it('should show visitor log purge message when purged and no data', async function() {
        expect.screenshot('visitor_log_purged').to.be.captureSelector('.reporting-page', function (page) {

            testEnvironment.overrideConfig('Deletelogs', 'delete_logs_enable', 1);
            testEnvironment.overrideConfig('Deletelogs', 'delete_logs_older_than', 4000);
            testEnvironment.save();

            page.goto("?module=CoreHome&action=index&idSite=1&period=year&date=2005-01-03#?idSite=1&period=year&date=2005-01-03&category=General_Visitors&subcategory=Live_VisitorLog");
            page.wait(4000);
        }, done);
    });
});
