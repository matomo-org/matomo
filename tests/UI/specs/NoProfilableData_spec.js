/*!
 * Matomo - free/libre analytics platform
 *
 * OneClickUpdate screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("NoProfilableData", function () {
    this.fixture = 'Piwik\\Tests\\Fixtures\\NonProfilableData';

    this.timeout(0);

    var generalParams = 'idSite=1&period=day&date=2020-04-04',
        urlBaseGeneric = 'module=CoreHome&action=index&',
        urlBase = urlBaseGeneric + generalParams,
        segmentParam = '&segment=visitCount>%3D1',
        url = "?" + urlBase,
        engagementUrl = url + '#?' + generalParams + '&category=General_Actions&subcategory=VisitorInterest_Engagement',
        goalsUrl = url + '#?' + generalParams + '&category=Goals_Goals&subcategory=1',
        visitsLogUrl = url + '#?' + generalParams + '&category=General_Visitors&subcategory=Live_VisitorLog',
        visitsOverviewUrl = url + '#?' + generalParams + '&category=General_Visitors&subcategory=General_Overview',
        devicesUrl = url + '#?' + generalParams + '&category=General_Visitors&subcategory=DevicesDetection_Devices',
        segmentUrl = url + '#?' + generalParams + segmentParam + '&category=General_Visitors&subcategory=General_Overview'
    ;

    it('should show a notification for segments that require profilable data', async () => {
        await page.goto(segmentUrl);
        await page.waitFor('.theWidgetContent');
        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('non_profilable_segment');
    });

    it('should show reports that require profilable data with footer message', async () => {
        await page.click('.segmentationContainer');
        await page.waitFor(100);
        await page.click('.segmentationContainer'); // second click to close segment editor

        await page.goto(engagementUrl);
        await page.waitFor('.theWidgetContent');

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('profilable_data_with_footer');
    });

    it('should show reports with warning footer in report by dimension views when no profilable data', async () => {
        await page.goto(goalsUrl);
        await page.waitForNetworkIdle();
        await page.waitFor('.reportsByDimensionView');

        const menuEntry = await page.jQuery('.reportDimension:contains(Visits to Conversion)');
        await menuEntry.click();

        await page.waitForNetworkIdle();
        await page.waitFor('.reportContainer .theWidgetContent');

        expect(await page.screenshotSelector('.reportsByDimensionView')).to.matchImage('reports_by_dimension');
    });

    it('should not show unique visitors w/o profilable data', async () => {
        await page.goto(devicesUrl);

        expect(await page.screenshotSelector('.reporting-page')).to.matchImage('no_unique_visitors');
    });

    it('should not show dimensions requiring profilable data in visitor log', async () => {
        await page.goto(visitsLogUrl);

        expect(await page.screenshotSelector('.dataTableVizVisitorLog')).to.matchImage('visits_log');
    });

    it('should not show nb_uniq_visitors metrics in sparklines', async () => {
        await page.goto(visitsOverviewUrl);
        await page.waitForNetworkIdle();

        expect(await page.screenshotSelector('#widgetVisitsSummarygetforceView1viewDataTablesparklines')).to.matchImage('visits_log');
    });
});
