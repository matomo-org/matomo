/*!
 * Matomo - free/libre analytics platform
 *
 * Bar graph screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("Comparison", function () {
    const generalParams = 'idSite=1&period=range&date=2012-01-12,2012-01-17',
        urlBase = 'module=CoreHome&action=index&' + generalParams,
        dashboardUrl = "?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=5",
        tokenAuth = "c4ca4238a0b923820dcc509a6f75849b", // md5('superUserLogin' . md5('superUserPass'))
        comparePeriod = "&compareDates[]=2012-01-01,2012-01-31&comparePeriods[]=range",
        compareSegment = "&compareSegments[]=continentCode%3D%3Deur",
        compareParams = comparePeriod + compareSegment,
        barGraphUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphVerticalBar&isFooterExpandedInDashboard=1&"
            + compareParams,
        pieGraphUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=graphPie&isFooterExpandedInDashboard=1&"
            + compareParams,
        goalsTableUrl =  "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getKeywords&viewDataTable=tableGoals&filter_limit=5&isFooterExpandedInDashboard=1" + compareParams,
        htmlTableUrl = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getSearchEngines&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1" + compareParams,
        htmlTableUrlNoPeriods = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getSearchEngines&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1" + compareSegment,
        htmlTableUrlNoSegments = "?module=Widgetize&action=iframe&moduleToWidgetize=Referrers&idSite=1&period=year&date=2012-08-09&"
            + "actionToWidgetize=getSearchEngines&viewDataTable=table&filter_limit=5&isFooterExpandedInDashboard=1" + comparePeriod,
        visitOverviewWidget = "?module=Widgetize&action=iframe&containerId=VisitOverviewWithGraph&disableLink=0&widget=1&" +
            "moduleToWidgetize=CoreHome&actionToWidgetize=renderWidgetContainer&disableLink=1&widget=1&" + generalParams + "&" +
            compareParams
    ;

    it('should compare periods correctly when comparing the last period', async () => {
        await page.goto(dashboardUrl);
        await page.waitForNetworkIdle();

        await page.click('#periodString #date');

        await page.waitForSelector('input#comparePeriodTo', { visible: true });
        await page.click('input#comparePeriodTo + span');

        await page.click('#calendarApply');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();
        await page.waitForSelector('.piwik-graph');

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('dashboard_last_period');
    });

    it('should add a segment comparison when the compare icon in the segment list is clicked', async () => {
        await page.click('.segmentationContainer');
        await (await page.jQuery('li[data-idsegment=2] .compareSegment', { waitFor: true })).click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('.widget');
        await page.waitForNetworkIdle();

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('dashboard_last_period_and_segment');
    });

    it('should not show comparisons for pages that do not support it', async () => {
        await (await page.jQuery('li.menuTab:contains(Behaviour)')).click();
        await page.waitForTimeout(100);
        await (await page.jQuery('a.item:contains(Transitions)')).click();
        await page.waitForNetworkIdle();

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('transitions');
    });

    it('should show extra serieses when comparing in evolution graphs and sparklines', async () => {
        await (await page.jQuery('li.menuTab:contains(Visitors)')).click();
        await page.waitForTimeout(100);
        await (await page.jQuery('li.menuTab:contains(Visitors) a.item:contains(Overview)')).click();
        await page.waitForNetworkIdle();
        await page.waitForSelector('.piwik-graph');

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('visitors_overview');
    });

    it('should change the evolution series when the sparkline is clicked', async () => {
        await (await page.jQuery('.sparkline:contains(pageviews):eq(0)')).click();
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('visitors_overview_switched');
    });

    it('should show the tooltip correctly in an evolution graph', async () => {
        await page.hover('.piwik-graph');
        await page.waitForTimeout(250);

        const element = await page.$('.ui-tooltip');
        expect(await element.screenshot()).to.matchImage('visitors_overview_tooltip');
    });

    it('should remove segment comparison when the x button is clicked', async () => {
        await page.click('.card.comparison .remove-button');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('visitors_overview_segment_removed');
    });

    it('should remove period comparison if period is selected w/o compare set', async () => {
        await page.click('#periodString .periodSelector');
        await page.waitForSelector('input#comparePeriodTo', { visible: true });
        await page.click('input#comparePeriodTo + span');

        await page.click('#calendarApply');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage('visitors_overview_no_compare');
    });

    it('should show the bar graph correctly when comparing segments and period', async () => {
        await page.goto(barGraphUrl);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('bar_graph');
    });

    it('should show the pie graph correctly when comparing segments and period', async () => {
        await page.goto(pieGraphUrl);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('pie_graph');
    });

    it('should show the normal html table correctly when comparing segments and periods', async () => {
        await page.goto(htmlTableUrl);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('normal_table');
    });

    it('should show the correct percentages and tooltip during comparison', async () => {
        const element = await page.jQuery('span.ratio:visible:eq(1)');
        await element.hover();
        const tooltip = await page.waitForSelector('.ui-tooltip', { visible: true });
        expect(await tooltip.screenshot()).to.matchImage('totals_tooltip');
    });

    it('should show the normal html table correctly when comparing segments but not periods', async () => {
        await page.goto(htmlTableUrlNoPeriods);
        await page.mouse.move(-10, -10); // mae sure no row is highlighted
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('normal_table_no_periods');
    });

    it('should show the normal html table correctly when comparing periods but not segments', async () => {
        await page.goto(htmlTableUrlNoSegments);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('normal_table_no_segments');
    });

    it('should expand subtables correctly when comparing', async () => {
        (await page.$$('tr.subDataTable'))[0].click();
        await page.waitForNetworkIdle();

        await page.waitForFunction(function () {
            return $('.cellSubDataTable > .dataTable').length === 1;
        });

        await page.mouse.move(-10, -10); // mae sure no row is highlighted

        expect(await page.screenshot({ fullPage: true })).to.matchImage('subtables_loaded');
    });

    it('should advance to the next page when paginating the subtable', async () => {
        await page.click('.cellSubDataTable .dataTableNext');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10); // mae sure no row is highlighted

        expect(await page.screenshot({ fullPage: true })).to.matchImage('subtables_paginate');
    });

    it('should show the row evolution popup for the compared row/segment/period when clicked', async () => {
        const row = await page.jQuery('tbody tr.comparisonRow:visible:eq(1)');
        await row.hover();

        const icon = await page.jQuery('tbody tr.comparisonRow:visible:eq(1) a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('row_evolution');
    });

    it('should show the multirow evolution popup for another comparison series', async () => {
        await page.click('.rowevolution-startmulti');
        await page.waitForTimeout(250);

        const row = await page.jQuery('tbody tr.comparisonRow:visible:eq(0)');
        await row.hover();

        const icon = await page.jQuery('tbody tr.comparisonRow:visible:eq(0) a.actionRowEvolution');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('multi_row_evolution');
    });

    it('should show the segmented visitor log popup for the compared row/segment/period when clicked', async () => {
        await page.click('.ui-dialog-titlebar-close');

        const row = await page.jQuery('tbody tr.comparisonRow:eq(1)');
        await row.hover();

        const icon = await page.jQuery('tbody tr.comparisonRow:eq(1) a.actionSegmentVisitorLog');
        await icon.click();

        await page.waitForSelector('.ui-dialog');
        await page.waitForNetworkIdle();

        await page.mouse.move(-10, -10);

        const dialog = await page.$('.ui-dialog');
        expect(await dialog.screenshot()).to.matchImage('segmented_visitorlog');
    });

    it('should show the goals table correctly when comparing segments and period', async () => {
        await page.goto(goalsTableUrl);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('goals_table');
    });

    it('should load a widgetized sparklines visualization correctly', async () => {
        await page.goto(visitOverviewWidget);
        await page.waitForNetworkIdle();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('visits_overview_widget');
    });
});
