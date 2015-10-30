/*!
 * Piwik - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CustomVariables", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CustomVariables\\tests\\Fixtures\\VisitWithManyCustomVariables";

    it('should show an overview of all used custom variables', function (done) {
        expect.screenshot('manage').to.be.captureSelector('.pageWrap', function (page) {
            page.load("?idSite=1&period=day&date=2010-01-03&module=CustomVariables&action=manage");
        }, done);
    });

    it('should be visible in the menu', function (done) {
        expect.screenshot('link_in_menu').to.be.captureSelector('li:contains(Manage)', function (page) {
        }, done);
    });

    it('should show custom variable creation command in dialog', function (done) {
        expect.screenshot('create').to.be.captureSelector('.ui-dialog', function (page) {
            page.click('.manageCustomVars .addCustomVar')
        }, done);
    });
});