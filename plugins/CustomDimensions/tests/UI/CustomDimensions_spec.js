/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("CustomDimensions", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\CustomDimensions\\tests\\Fixtures\\TrackVisitsWithCustomDimensionsFixture";

    var generalParams = 'idSite=1&period=year&date=2013-01-23',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    var reportUrl = "?" + urlBase + "#?" + generalParams;
    var manageUrl = "?" + generalParams + "&module=CustomDimensions&action=manage";

    var reportUrlDimension2 = reportUrl + "&category=General_Visitors&subcategory=customdimension2";
    var reportUrlDimension3 = reportUrl + "&category=General_Actions&subcategory=customdimension3";
    var reportUrlDimension4 = reportUrl + "&category=General_Actions&subcategory=customdimension4";

    var popupSelector = '.ui-dialog:visible';

    async function capturePageWrap (screenName, test) {
        await captureSelector(screenName, '.pageWrap', test)
    }

    async function captureSelector(screenName, selector, test) {
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
     * MANAGE CUSTOM DIMENSIONS
     */

    it('should load initial manange page', async function () {
        await capturePageWrap('manage_inital', async function () {
            await page.goto(manageUrl);
        });
    });

    it('should open a page to create a new visit dimension and not show extractions', async function () {
        await capturePageWrap('manage_new_visit_dimension_open', async function () {
            await page.click('.scope-visit .btn');
        });
    });

    it('should be possible to create new visit dimension', async function () {
        await capturePageWrap('manage_new_visit_dimension_created', async function () {
            await page.type(".editCustomDimension #name", 'My Custom Name');
            await page.click('.editCustomDimension #active');
            await page.click('.editCustomDimension .create');
            await page.waitForNetworkIdle();
        });
    });

    it('should open a page to create a new action dimension', async function () {
        await capturePageWrap('manage_new_action_dimension_open', async function () {
            await page.click('.scope-action .btn');
        });
    });

    it('should be possible to define name, active and extractions for scope action', async function () {
        await capturePageWrap('manage_new_action_dimension_withdata', async function () {
            await page.type(".editCustomDimension #name", 'My Action Name');

            await page.type('.extraction0 #pattern0', 'myPattern_(.+)');

            await page.click('.extraction0 .icon-plus');
            await page.type('.extraction1 #pattern1', 'second pattern_(.+)');

            await page.click('.extraction1 .icon-plus');
            await page.type('.extraction2 #pattern2', 'thirdpattern_(.+)test');
        });
    });

    it('should be possible to remove a defined extraction', async function () {
        await capturePageWrap('manage_new_action_dimension_remove_an_extraction', async function () {
            await page.click('.extraction1 .icon-minus');
        });
    });

    it('should create a new dimension', async function () {
        await capturePageWrap('manage_new_action_dimension_created', async function () {
            await page.click('.editCustomDimension .create');
            await page.waitForNetworkIdle();
        });
    });

    it('should be able to open created dimension and see same data but this time with tracking instructions', async function () {
        await capturePageWrap('manage_edit_action_dimension_verify_created', async function () {
            await page.click('.manageCustomDimensions .customdimension-8 .icon-edit');
        });
    });

    it('should be possible to change an existing dimension', async function () {
        await capturePageWrap('manage_edit_action_dimension_withdata', async function () {
            await page.type(".editCustomDimension #name", 'ABC');
            await page.click('.editCustomDimension #active');
            await page.click('.editCustomDimension #casesensitive');
            await page.click('.extraction0 .icon-minus');
        });
    });

    it('should updated an existing dimension', async function () {
        await capturePageWrap('manage_edit_action_dimension_updated', async function () {
            await page.click('.editCustomDimension .update');
            await page.waitForNetworkIdle();
        });
    });

    it('should have actually updated values', async function () {
        await capturePageWrap('manage_edit_action_dimension_verify_updated', async function () {
            await page.click('.manageCustomDimensions .customdimension-8 .icon-edit');
        });
    });

    it('should go back to list when pressing cancel', async function () {
        await capturePageWrap('manage_edit_action_dimension_cancel', async function () {
            await page.click('.editCustomDimension .cancel');
        });
    });

    it('should disable configure button when no dimensions are left for a scope', async function () {
        await capturePageWrap('manage_configure_button_disabled', async function () {
            await page.click('.scope-visit .btn');
            await page.type(".editCustomDimension #name", 'Last Name');
            await page.click('.editCustomDimension .create');
            await page.waitForNetworkIdle();
        });
    });

    it('should be possible to create a new dimension via URL', async function () {
        await capturePageWrap('manage_create_via_url', async function () {
            await page.goto(manageUrl + '#?idDimension=0&scope=action');
        });
    });

    it('should be possible to open an existing visit dimension via URL', async function () {
        await capturePageWrap('manage_edit_via_url', async function () {
            await page.goto(manageUrl + '#?idDimension=5&scope=action');
        });
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
