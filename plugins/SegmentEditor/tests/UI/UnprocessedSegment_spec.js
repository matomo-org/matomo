/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UnprocessedSegmentTest", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';
    this.timeout(0);

    var now = new Date();
    var today = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
    var generalParams = 'idSite=1&period=year&date=' + today;
    var segment = 'browserCode==ff';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=General_Overview&segment=' + encodeURIComponent(segment);

    before(function (done) {
        testEnvironment.callApi('SegmentEditor.add', {
            name: '<script>alert("testsegment");</script>',
            definition: segment,
            idSite: 1,
            autoArchive: 1,
            enableAllUsers: 1,
        }, done);
    });

    before(function () {
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '1',
        };
        testEnvironment.save();
    });

    after(function (done) {
        testEnvironment.callApi('SegmentEditor.delete', { idSegment: 1 }, done);
    });

    it("should show a notification for unprocessed segments", function (done) {
        expect.screenshot("unprocessed_segment").to.be.captureSelector('#content.home', function (page) {
            page.load(url);
        }, done);
    });

    function pad(val) {
        return ("00" + val).slice(-2);
    }
});
