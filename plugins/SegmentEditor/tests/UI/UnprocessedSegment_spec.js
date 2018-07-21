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

    var generalParams = 'idSite=1&period=day&date=2010-03-06';
    var segment = 'browserCode==ff';
    var customSegment = 'languageCode==fr';
    var url = '?module=CoreHome&action=index&' + generalParams + '#?' + generalParams + '&category=General_Visitors&subcategory=General_Overview';

    before(async function () {
        await testEnvironment.callApi('SegmentEditor.add', {
            name: '<script>alert("testsegment");</script>',
            definition: segment,
            idSite: 1,
            autoArchive: 1,
            enableAllUsers: 1,
        });
    });

    before(async function () {
        testEnvironment.configOverride.General = {
            browser_archiving_disabled_enforce: '1',
            enable_browser_archiving_triggering: '0',
        };
        testEnvironment.optionsOverride = {
            enableBrowserTriggerArchiving: '0',
        };
        testEnvironment.save();
    });

    after(async function () {
        await testEnvironment.callApi('SegmentEditor.delete', { idSegment: 1 });
    });

    it("should show a notification for unprocessed segments", async function () {
        await page.goto(url + '&segment=' + encodeURIComponent(segment));

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('unprocessed_segment');
    });

    it('should not show a notification for custom segments that are not preprocessed', async function () {
        await page.goto(url + '&segment=' + encodeURIComponent(customSegment));

        pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('custom_segment');
    });
});
