/*!
 * Piwik - free/libre analytics platform
 *
 * transitions screenshot tests
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Transitions", function () {
    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        urlBase = 'module=CoreHome&action=index&' + generalParams
        ;

    it('should load the transitions popup correctly for the page titles report', function (done) {
        expect.screenshot('transitions_popup_titles').to.be.captureSelector('.ui-dialog', function (page) {
            page.load("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPageTitles");

            page.mouseMove('div.dataTable tbody tr:eq(2)');
            page.mouseMove('a.actionTransitions:visible'); // necessary to get popover to display
            page.click('a.actionTransitions:visible');
        }, done);
    });

    it('should load the transitions popup correctly for the page urls report', function (done) {
        expect.screenshot('transitions_popup_urls').to.be.captureSelector('.ui-dialog', function (page) {
            page.load("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages&"
                    + "popover=RowAction$3ATransitions$3Aurl$3Ahttp$3A$2F$2Fpiwik.net$2Fdocs$2Fmanage-websites$2F");
            page.mouseMove('.Transitions_CurveTextRight');
        }, done);
    });
});