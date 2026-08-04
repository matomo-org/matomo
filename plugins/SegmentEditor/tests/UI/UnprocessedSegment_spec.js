/*!
 * Matomo - free/libre analytics platform
 *
 * SegmentEditor screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UnprocessedSegmentTest", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\OneVisitorTwoVisits';

    var generalParams = 'idSite=1&period=range&date=2010-03-06,2010-03-08';
    var segment = 'browserCode==ff';
    var customSegment = 'languageCode==fr';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=General_Overview';

    before(async function () {
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
            enable_browser_archiving_triggering: '0',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();

        await testEnvironment.callApi('SegmentEditor.add', {
            name: '<script>alert("testsegment");</script>',
            definition: segment,
            idSite: 1,
            autoArchive: 1,
            enableAllUsers: 1,
        });
    });

    after(async function () {
        await testEnvironment.callApi('SegmentEditor.delete', { idSegment: 1 });
    });

    it('should not show a notification for custom segments that are not preprocessed', async function () {

        await page.goto(url + '&segment=' + encodeURIComponent(customSegment));

        expect(await page.screenshotSelector('.pageWrap,#notificationContainer')).to.matchImage('custom_segment');
    });

    it("should show a notification for unprocessed segments", async function () {
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
            enable_browser_archiving_triggering: '0',
            rearchive_reports_in_past_last_n_months: '0',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();

        await page.goto(url + '&segment=' + encodeURIComponent(segment));
        expect(await page.screenshotSelector('.pageWrap,#notificationContainer')).to.matchImage('unprocessed_default_segment');
    });


    it("should show a notification for unprocessed segments, caused by re archive date", async function () {
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
            enable_browser_archiving_triggering: '0',
            rearchive_reports_in_past_last_n_months: '1',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();

        await page.goto(url + '&segment=' + encodeURIComponent(segment));

        expect(await page.screenshotSelector('.pageWrap,#notificationContainer')).to.matchImage('unprocessed_segment');
    });
});
