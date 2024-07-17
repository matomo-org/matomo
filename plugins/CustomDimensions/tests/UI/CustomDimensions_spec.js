/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CustomDimensions", function () {
    this.timeout(5*60*1000); // timeout of 5 minutes per test

    this.fixture = "Piwik\\Plugins\\CustomDimensions\\tests\\Fixtures\\TrackVisitsWithCustomDimensionsFixture";

    var generalParams = 'idSite=1&period=year&date=2013-01-23',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    var reportUrl = "?" + urlBase + "#?" + generalParams;

    var reportUrlDimension2 = reportUrl + "&category=General_Visitors&subcategory=customdimension2";
    var reportUrlDimension3 = reportUrl + "&category=General_Actions&subcategory=customdimension3";
    var reportUrlDimension4 = reportUrl + "&category=General_Actions&subcategory=customdimension4";

    var popupSelector = '.ui-dialog:visible';

    async function capturePageWrap (screenName, test) {
        await captureSelector(screenName, '.pageWrap', test)
    }

    async function captureSelector (screenName, selector, test) {
        await page.webpage.setViewport({
            width: 1350,
            height: 768,
        });
        await test();
        expect(await page.screenshotSelector(selector)).to.matchImage(screenName);
    }

    async function closeOpenedPopover()
    {
        await page.waitForTimeout(100);
        const closeButton = await page.jQuery('.ui-dialog:visible .ui-icon-closethick:visible');
        if (!closeButton) {
            return;
        }

        await closeButton.click();
        await page.waitForTimeout(100);
    }

    async function triggerRowAction(labelToClick, nameOfRowActionToTrigger)
    {
        var rowToMatch = 'td.label:contains(' + labelToClick + '):first';

        await (await page.jQuery('table.dataTable tbody ' + rowToMatch)).hover();
        await page.waitForTimeout(100);
        await (await page.jQuery(rowToMatch + ' a.'+ nameOfRowActionToTrigger + ':visible')).hover(); // necessary to get popover to display
        await (await page.jQuery(rowToMatch + ' a.' + nameOfRowActionToTrigger + ':visible')).click();
        await page.mouse.move(-10, -10);
        await page.waitForTimeout(250); // wait for animation
        await page.waitForNetworkIdle();
    }

    before(function () {
        testEnvironment.pluginsToLoad = ['CustomDimensions'];
        testEnvironment.save();
    });

    /**
     * VISIT DIMENSION REPORTS
     */

    it('should show the report for the selected visit dimension', async function () {
        await capturePageWrap('report_visit', async function () {
            await page.goto(reportUrlDimension2);
        });
    });

    it('should add a menu item for each active visit dimension', async function () {
        await captureSelector('report_visit_mainmenu', '#secondNavBar', async function () {
            // we only capture a screenshot of a different part of the page, no need to do anything
        });
    });

    it('should add visit dimensions to goals report', async function () {
        await captureSelector('report_goals_overview', '.reportsByDimensionView', async function () {
            await page.goto( "?" + urlBase + "#?" + generalParams + "&category=Goals_Goals&subcategory=General_Overview");
            await (await page.jQuery('.reportsByDimensionView .dimension:contains(MyName1)')).click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(100);
        });
    });

    /**
     * ACTION DIMENSION REPORTS
     */

    it('should show the report for the selected action dimension', async function () {
        await capturePageWrap('report_action', async function () {
            await page.goto(reportUrlDimension3);
        });
    });

    it('should add a menu item for each active action dimension', async function () {
        await captureSelector('report_actions_mainmenu', '#secondNavBar', async function () {
            // we only capture a screenshot of a different part of the page, no need to do anything
        });
    });

    it('should offer only segmented visitor log and row action for first level entries', async function () {
        await capturePageWrap('report_actions_rowactions', async function () {
            await page.hover('tr:first-child td.label');
        });
    });

    it('should be able to render insights', async function () {
        await capturePageWrap('report_action_insights', async function () {
            await page.mouse.move(0, 0);
            await page.evaluate(function(){
                $('[data-footer-icon-id="insightsVisualization"]').click();
            });
            await page.waitForNetworkIdle();
        });
    });

    it('should show an error when trying to open an inactive dimension', async function () {
        await page.goto(reportUrlDimension4);
        await page.waitForFunction('$(".pageWrap:contains(\'This page does not exist\')").length > 0');
    });

    it('should be able to open segmented visitor log', async function () {
        await captureSelector('report_actions_segmented_visitorlog', popupSelector, async function () {
            await page.goto(reportUrlDimension3);
            await triggerRowAction('en', 'actionSegmentVisitorLog');
        });
    });

    it('should be able to open row evolution', async function () {
        await captureSelector('report_actions_rowevolution', popupSelector, async function () {
            await page.goto(reportUrlDimension3);
            await triggerRowAction('en', 'actionRowEvolution');
        });
    });

    it('should be able to show subtable and offer all row actions if scope is action', async function () {
        await capturePageWrap('report_action_subtable', async function () {
            await page.goto(reportUrlDimension3);
            await (await page.jQuery('.dataTable .subDataTable .value:contains(en):first')).click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(500);
            await (await page.jQuery('td.label:contains(en_US)')).hover();
            await page.waitForTimeout(100);
        });
    });

    it('should be able to show row evolution for subtable', async function () {
        await captureSelector('report_action_subtable_rowevolution', popupSelector, async function () {
            await triggerRowAction('en_US', 'actionRowEvolution');
        });
    });

    it('should be able to show segmented visitor log for subtable', async function () {
        await captureSelector('report_action_subtable_segmented_visitor_log', popupSelector, async function () {
            await closeOpenedPopover();
            await triggerRowAction('en_US', 'actionSegmentVisitorLog');
        });
    });

    it('should be able to show transitions for subtable', async function () {
        await captureSelector('report_action_subtable_transitions', popupSelector, async function () {
            await page.goto('about:blank');
            await page.goto(reportUrlDimension3);
            await (await page.jQuery('.dataTable .subDataTable .value:contains(en):first')).click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(200);
            await (await page.jQuery('td.label:contains(en_US):visible')).hover();
            await page.waitForTimeout(200);
            await triggerRowAction('en_US', 'actionTransitions');
        });
    });
});
