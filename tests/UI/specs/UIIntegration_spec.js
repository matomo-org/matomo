/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("UIIntegrationTest", function () { // TODO: Rename to Piwik?
    var parentSuite = this;

    this.timeout(0);

    var generalParams = 'idSite=1&period=year&date=2012-08-09',
        idSite2Params = 'idSite=2&period=year&date=2012-08-09',
        idSite3Params = 'idSite=3&period=year&date=2012-08-09',
        evolutionParams = 'idSite=1&period=day&date=2012-01-31&evolution_day_last_n=30',
        urlBaseGeneric = 'module=CoreHome&action=index&',
        urlBase = urlBaseGeneric + generalParams,
        widgetizeParams = "module=Widgetize&action=iframe",
        segment = encodeURIComponent("browserCode==FF") // from OmniFixture
        ;

    before(async function () {
        testEnvironment.queryParamOverride = {
            forceNowValue: testEnvironment.forcedNowTimestamp,
            visitorId: testEnvironment.forcedIdVisitor,
            realtimeWindow: 'false'
        };
        testEnvironment.completeNoChallenge = true;
        testEnvironment.pluginsToLoad = ['CustomDirPlugin'];
        testEnvironment.save();

        await testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []});
    });

    beforeEach(function () {
        if (testEnvironment.configOverride.database) {
            delete testEnvironment.configOverride.database;
        }
        if (testEnvironment.configOverride.General) {
            delete testEnvironment.configOverride.General;
        }
        if (testEnvironment.idSitesViewAccess) {
            delete testEnvironment.idSitesViewAccess;
        }
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    after(function () {
        delete testEnvironment.queryParamOverride;
        delete testEnvironment.completeNoChallenge;
        testEnvironment.testUseMockAuth = 1;
        testEnvironment.save();
    });

    async function screenshotPageWrap() {
        const pageWrap = await page.$('.pageWrap');
        const screenshot = await pageWrap.screenshot();
        return screenshot;
    }

    describe("misc", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        afterEach(async () => {
            await page.setUserAgent(page.originalUserAgent);
        });

        it("should load the page of a plugin located in a custom directory", async function () {
            await page.goto("?module=CustomDirPlugin&action=index&idSite=1&period=day&date=yesterday");

            expect(await screenshotPageWrap()).to.matchImage('customdirplugin');
        });

        // shortcuts help
        it("should show shortcut help", async function () {
            await page.setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36");
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=Dashboard_Dashboard&subcategory=1");
            await page.waitForNetworkIdle();
            await page.keyboard.press('?');
            await page.waitForTimeout(500); // wait for animation to end

            modal = await page.$('.modal.open');
            expect(await modal.screenshot()).to.matchImage('shortcuts');
        });

        it('should show category help correctly', async function () {
            await page.goto('about:blank');
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=General_Overview");
            await page.waitForSelector('.dataTable');
            await (await page.jQuery('#secondNavBar ul ul li[role=menuitem]:contains(Overview):eq(0)')).hover();
            await (await page.jQuery('#secondNavBar ul ul li[role=menuitem]:contains(Overview):eq(0) .item-help-icon')).click();
            expect(await page.screenshotSelector('#secondNavBar,#notificationContainer')).to.matchImage('category_help');
        });

        // one page w/ segment
        it('should load the visitors > overview page correctly when a segment is specified', async function () {
            await page.goto('about:blank');
            testEnvironment.overrideConfig('General', {
                enable_segments_cache: 0
            });
            testEnvironment.save();

            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=General_Overview&segment=" + segment);

            // check that segment is selected in selector
            const segmentTitle = await page.evaluate(() => $('.segmentationTitle').text());
            expect(segmentTitle).to.match(/<script>_x\(\d+\)<\/script>/);

            expect(await page.screenshotSelector('#content')).to.matchImage('visitors_overview_segment');
        });


        // Notifications
        it('should load the notifications page correctly', async function() {
            await page.goto("?" + generalParams + "&module=ExampleUI&action=notifications&idSite=1&period=day&date=yesterday");
            expect(await screenshotPageWrap()).to.matchImage('notifications');
        });

        // Fatal error safemode
        it('should load the safemode fatal error page correctly', async function() {
            const message = "Call%20to%20undefined%20function%20Piwik%5CPlugins%5CFoobar%5CPiwik_Translate()";
            const file = "%2Fhome%2Fvagrant%2Fwww%2Fpiwik%2Fplugins%2FFoobar%2FFoobar.php%20line%205";
            const line = 58;

            await page.goto("?" + generalParams + "&module=CorePluginsAdmin&action=safemode&idSite=1&period=day&date=yesterday&activated"
                + "&error_message=" + message + "&error_file=" + file + "&error_line=" + line + "&tests_hide_piwik_version=1");
            await page.evaluate(function () {
                var elements = document.querySelectorAll('table tr td:nth-child(2)');
                for (var i in elements) {
                    if (elements.hasOwnProperty(i) && elements[i].innerText.match(/^[0-9]\.[0-9]\.[0-9]$/)) {
                        elements[i].innerText = '3.0.0'
                    }
                }
            });

            expect(await page.screenshot({ fullPage: true })).to.matchImage('fatal_error_safemode');
        });

        it('should the error page instead of safemode when error while rendering view is not a twig error', async function() {

            await page.goto("?" + generalParams + "&module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&segment=userid%3D%3D35745");
            expect(await page.screenshot({ fullPage: true })).to.matchImage('view_render_error_user_input');
        });

        // not logged in
        it('should show login form for non super user if invalid idsite given', async function() {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.save();

            await page.goto("?module=CoreHome&action=index&idSite=1&period=week&date=2017-06-04");

            expect(await page.screenshot({ fullPage: true })).to.matchImage('not_logged_in');
        });

        // invalid site parameter
        it('should show login form for non super user if invalid idsite given', async function() {
            testEnvironment.idSitesViewAccess = [1, 2];
            testEnvironment.save();

            await page.goto("?module=CoreHome&action=index&idSite=10006&period=week&date=2017-06-04");

            expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_idsite');
        });

        it('should show error for super user if invalid idsite given', async function() {
            await page.goto("?module=CoreHome&action=index&idSite=10006&period=week&date=2017-06-04");

            expect(await page.screenshot({ fullPage: true })).to.matchImage('invalid_idsite_superuser');
        });
    });

    describe("VisitorsPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // visitors pages
        it('should load visitors > overview page correctly', async function () {
            await page.keyboard.press('Escape'); // close shortcut screen

            testEnvironment.ignoreClearAllViewDataTableParameters = 1;
            testEnvironment.save();

            // use columns query param to make sure columns works when supplied in URL fragment
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=General_Overview&columns=nb_visits,nb_actions");
            await page.waitForNetworkIdle();
            await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
              $('.dataTableScroller').css('overflow-x', 'scroll');
            });
            expect(await screenshotPageWrap()).to.matchImage('visitors_overview_columns');
        });

        it('should reload the visitors > overview page when clicking on the visitors overview page element again', async function () {
            await page.click('#secondNavBar ul li.active li.active a.item');
            await page.waitForNetworkIdle();
            await page.waitForSelector('.piwik-graph');

            expect(await screenshotPageWrap()).to.matchImage('visitors_overview');
        });

        it('should be possible to change the limit of evolution chart', async function () {
            await page.hover('.dataTableFeatures');
            await page.click('.limitSelection input');
            await page.evaluate(function () {
                $('.limitSelection ul li:contains(10) span').click();
            });
            await page.mouse.move(0, 0);
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_overview_limit');
        });

        it('should keep the limit when reload the page', async function () {
            await page.reload();

            delete testEnvironment.ignoreClearAllViewDataTableParameters;
            testEnvironment.save();

            expect(await screenshotPageWrap()).to.matchImage('visitors_overview_limit');
        });

        it('should load the visitors > devices page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=DevicesDetection_Devices");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_devices');
        });

        it('should load visitors > locations & provider page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=UserCountry_SubmenuLocations");
            await page.waitForNetworkIdle();
            await page.waitForTimeout(500); // wait for map widget to render

            expect(await screenshotPageWrap()).to.matchImage('visitors_locations_provider');
        });

        it('should load the visitors > software page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=DevicesDetection_Software");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_software');
        });

        it('should load the visitors > times page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=VisitTime_SubmenuTimes");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_times');
        });

        it('should load the visitors > engagement page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=VisitorInterest_Engagement");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_engagement');
        });

        it('should load the visitors > custom variables page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=CustomVariables_CustomVariables");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('visitors_custom_vars');
        });

        it('should load the visitors > real-time map page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + idSite2Params + "&category=General_Visitors&subcategory=UserCountryMap_RealTimeMap"
                + "&showDateTime=0&realtimeWindow=last2&changeVisitAlpha=0&enableAnimation=0&doNotRefreshVisits=1"
                + "&removeOldVisits=0");

            await page.waitForSelector('circle');
            await page.waitForTimeout(250); // rendering
            await (await page.jQuery('circle:eq(0)')).hover();
            await page.waitForSelector('.ui-tooltip', {visible: true}); // wait for tooltip
            await page.evaluate(function () {
                $('.ui-tooltip:visible .rel-time').data('actiontime', (Date.now() - (4 * 24 * 60 * 60 * 1000)) / 1000);
            });

            // updating the time might take up to one second
            await page.waitForTimeout(1000);

            expect(await page.screenshotSelector('.pageWrap,.ui-tooltip')).to.matchImage('visitors_realtime_map');
        });

        it('should load the visitors > real-time visits page correctly', async function () {
            await page.goto("?" + urlBaseGeneric + idSite3Params + "#?" + idSite3Params + "&category=General_Visitors&subcategory=General_RealTime");
            //await page.waitForNetworkIdle();
            await page.mouse.move(-10, -10);
            //await page.click('#pauseImage'); // prevent refreshes breaking the tests
            await page.waitForTimeout(100);

            expect(await screenshotPageWrap()).to.matchImage('visitors_realtime_visits');
        });
    });

    describe("ActionsPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // actions pages
        it('should load the actions > pages page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");
            await page.mouse.move(-10, -10);
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_pages');
        });

        // actions pages
        it('should load the actions > pages help tooltip, including the "Report generated time"', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Pages");
            await page.waitForSelector('.enrichedHeadline');
            elem = await page.$('.enrichedHeadline');
            await elem.hover();
            await page.click('.helpIcon');
            await page.waitForTimeout(100);
            await page.evaluate(function () {
                $('.helpDate:visible').html('Report generated xx hours xx min ago');
            });
            await page.mouse.move(-10, -10);

            expect(await screenshotPageWrap()).to.matchImage('actions_pages_tooltip_help');
        });

        it('should load the actions > entry pages page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPagesEntry");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_entry_pages');
        });

        it('should load the actions > exit pages page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPagesExit");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_exit_pages');
        });

        it('should load the actions > page titles page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuPageTitles");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_page_titles');
        });

        it('should load the actions > site search page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Actions_SubmenuSitesearch");
            await page.waitForNetworkIdle();
            await page.waitForTimeout(150);

            expect(await screenshotPageWrap()).to.matchImage('actions_site_search');
        });

        it('should load the actions > outlinks page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Outlinks");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_outlinks');
        });

        it('should load the segmented vlog correctly for outlink containing a &', async function () {
            await (await page.jQuery('#widgetActionsgetOutlinks .value:contains("outlinks.org")')).click();
            await page.waitForNetworkIdle();

            const row = 'tr:contains("&pk") ';
            const first = await page.jQuery(row + 'td.column:first');
            await first.hover();
            const second = await page.jQuery(row + 'td.label .actionSegmentVisitorLog');
            await second.hover();
            await second.click();
            await page.waitForNetworkIdle();
            await page.mouse.move(0, 0);

            pageWrap = await page.$('.ui-dialog');
            await page.waitForTimeout(150);
            expect(await pageWrap.screenshot()).to.matchImage('actions_outlinks_vlog');
        });

        it('should load the actions > downloads page correctly', async function () {
            await page.goto('about:blank');
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=General_Downloads");
            await page.waitForTimeout(500);
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_downloads');
        });

        it('should load the actions > contents page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Contents_Contents&period=day&date=2012-01-01");
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_contents');
        });

        it("should show all corresponding content pieces when clicking on a content name", async function () {
            elem = await page.jQuery('.dataTable .subDataTable .value:contains(ImageAd)');
            await elem.click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(500);
            await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
              $('.dataTableScroller').css('overflow-x', 'scroll');
            });
            await page.mouse.move(-10, -10);

            expect(await screenshotPageWrap()).to.matchImage('actions_content_name_piece');
        });

        it("should show all tracked content pieces when clicking on the table", async function () {
            elem = await page.jQuery('.reportDimension .dimension:contains(Content Piece)');
            await elem.click();
            await page.waitForNetworkIdle();

            expect(await screenshotPageWrap()).to.matchImage('actions_content_piece');
        });

        it("should show all corresponding content names when clicking on a content piece", async function () {
            elem = await page.jQuery('.dataTable .subDataTable .value:contains(Click NOW)');
            await elem.click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(500);
            await page.evaluate(() => { // give table headers constant width so the screenshot stays the same
              $('.dataTableScroller').css('overflow-x', 'scroll');
            });
            await page.mouse.move(-10, -10);

            expect(await screenshotPageWrap()).to.matchImage('actions_content_piece_name');
        });
    });

    describe("EventsPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // Events pages
        it('should load the Events > index page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Actions&subcategory=Events_Events");
            await page.mouse.move(-10, -10);

            expect(await page.screenshotSelector('.pageWrap,.dataTable')).to.matchImage('events_overview');
        });
    });

    describe("ExampleUiPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // example ui pages
        it('should load the example ui > dataTables page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=ExampleUI_GetTemperaturesDataTable");
            await page.mouse.move(-10, -10);

            expect(await screenshotPageWrap()).to.matchImage('exampleui_dataTables');
        });

        it('should load the example ui > barGraph page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Bar%20graph");

            expect(await screenshotPageWrap()).to.matchImage('exampleui_barGraph');
        });

        it('should load the example ui > pieGraph page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Pie%20graph");

            expect(await screenshotPageWrap()).to.matchImage('exampleui_pieGraph');
        });

        it('should load the example ui > tagClouds page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Tag%20clouds");

            expect(await screenshotPageWrap()).to.matchImage('exampleui_tagClouds');
        });

        it('should load the example ui > sparklines page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Sparklines");

            expect(await screenshotPageWrap()).to.matchImage('exampleui_sparklines');
        });

        it('should load the example ui > evolution graph page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Evolution%20Graph");

            expect(await screenshotPageWrap()).to.matchImage('exampleui_evolutionGraph');
        });

        it('should load the example ui > treemap page correctly', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=ExampleUI_UiFramework&subcategory=Treemap");
            await page.waitForNetworkIdle();
            await page.waitForTimeout(500);

            expect(await screenshotPageWrap()).to.matchImage('exampleui_treemap');
        });

        it('should load sparklines view correctly even when there is no matching row', async function () {
            await page.goto('?forceView=1&viewDataTable=sparklines&module=ExampleUI&action=getTemperaturesEvolution&label=example32323.matomo.org&'+generalParams+'&segment=&showtitle=1');
            await page.waitForNetworkIdle();

            pageWrap = await page.$('body');
            expect(await pageWrap.screenshot()).to.matchImage('exampleui_sparklines_no_matching_row');
        });
    });

    describe("WidgetizePages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // widgetize
        it('should load the widgetized visitor log correctly', async function () {
            await page.goto("?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Live&actionToWidgetize=getVisitorLog");
            await page.evaluate(function () {
                $('.expandDataTableFooterDrawer').click();
            });
            await page.waitForNetworkIdle();

            expect(await page.screenshot({fullPage: true})).to.matchImage('widgetize_visitor_log');
        });

        it('should load the widgetized all websites dashboard correctly', async function () {
            await page.goto("?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=MultiSites&actionToWidgetize=standalone");
            await page.waitForNetworkIdle();

            expect(await page.screenshot({fullPage: true})).to.matchImage('widgetize_allwebsites');
        });

        it('should widgetize the ecommerce log correctly', async function () {
            await page.goto("?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Ecommerce&actionToWidgetize=getEcommerceLog&filter_limit=-1");

            expect(await page.screenshot({fullPage: true})).to.matchImage('widgetize_ecommercelog');
        });

        // Do not allow API response to be displayed
        it('should not allow to widgetize an API call', async function () {
            await page.goto("?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=API&actionToWidgetize=index&method=SitesManager.getImageTrackingCode&piwikUrl=test");

            expect(await page.screenshot({fullPage: true})).to.matchImage('widgetize_apidisallowed');
        });

        it('should not display API response in the content and redirect to dashboard instead', async function () {
            var url = "?" + urlBase + "#?" + generalParams + "&module=API&action=SitesManager.getImageTrackingCode";
            await page.goto(url);
            await page.waitForNetworkIdle();

            // check dashboard is present
            await page.waitForSelector('#dashboardWidgetsArea');
        });
    });

    describe("EcommercePages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        // Ecommerce
        it('should load the ecommerce overview page', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=General_Overview");

            expect(await page.screenshotSelector('.pageWrap,.dataTable')).to.matchImage('ecommerce_overview');
        });

        it('should load the ecommerce log page', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog");

            const action = await page.jQuery('.dataTableVizVisitorLog .card:eq(1) .actionList li.action');
            await action.hover();
            await page.waitForSelector('.ui-tooltip', {visible: true, timeout: 250});

            var tooltipContent = await page.evaluate(() => {
                return $('.ui-tooltip:visible').text();
            });

            expect(tooltipContent).to.match(/This conversion has been attributed to the Acquisition Channel:.*Direct Entry/);

            await page.mouse.move(0, 0); // move mouse to hide tooltip again

            expect(await screenshotPageWrap()).to.matchImage('ecommerce_log');
        });

        it('should load the ecommerce log page with segment', async function () {
            await page.goto("?" + urlBase + "&segment=countryCode%3D%3DCN#?" + generalParams + "&category=Goals_Ecommerce&subcategory=Goals_EcommerceLog&segment=countryCode%3D%3DCN");

            expect(await screenshotPageWrap()).to.matchImage('ecommerce_log_segmented');
        });

        it('should load the ecommerce products page', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=Goals_Products");

            expect(await page.screenshotSelector('.pageWrap,.dataTable')).to.matchImage('ecommerce_products');
        });

        it('should load the ecommerce sales page', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=Goals_Ecommerce&subcategory=Ecommerce_Sales");

            expect(await page.screenshotSelector('.pageWrap,.dataTable')).to.matchImage('ecommerce_sales');
        });
    });

    describe("AdminPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        it('should not be possible to render any action using token_auth with at least some admin access', async function () {
            await page.goto("?" + generalParams + "&module=CoreAdminHome&action=home&token_auth=c4ca4238a0b923820dcc509a6f75849b");

            expect(await page.screenshot({ fullPage: true })).to.matchImage('admin_home_admintoken_not_allowed');
        });

        it('should load the Admin home page correct', async function () {
            await page.goto("?" + generalParams + "&module=CoreAdminHome&action=home");

            expect(await screenshotPageWrap()).to.matchImage('admin_home');
        });

        // Admin user settings (plugins not displayed)
        it('should load the Manage > Websites admin page correctly', async function () {
            await page.goto("?" + generalParams + "&module=SitesManager&action=index");
            await page.evaluate(function () {
                $('.form-help:contains(UTC time is)').hide();
            });

            expect(await screenshotPageWrap()).to.matchImage('admin_manage_websites');
        });

        it('should load the Settings > General Settings admin page correctly', async function () {
            await page.goto("?" + generalParams + "&module=CoreAdminHome&action=generalSettings");
            await page.waitForSelector('.pageWrap');
            await page.waitForNetworkIdle();
            await page.evaluate(function () {
                $('textarea:eq(0)').trigger('focus');
            });
            await page.waitForTimeout(750);

            expect(await screenshotPageWrap()).to.matchImage('admin_settings_general');
        });

        it('should load the Privacy Opt out iframe correctly', async function () {
            await page.goto("?module=CoreAdminHome&action=optOut&language=de");
            await page.waitForNetworkIdle();

            expect(await page.screenshot({fullPage: true})).to.matchImage('admin_privacy_optout_iframe');
        });

        it('should load the config file page correctly', async function () {
            await page.goto("?" + generalParams + "&module=Diagnostics&action=configfile");

            expect(await screenshotPageWrap()).to.matchImage('admin_diagnostics_configfile');
        });

        it('should load the Settings > Visitor Generator admin page correctly', async function () {
            await page.goto("?" + generalParams + "&module=VisitorGenerator&action=index");
            await page.evaluate(function () {
                var $p = $('#content p:eq(1)');
                $p.text($p.text().replace(/\(change .*\)/g, ''));
            });

            expect(await screenshotPageWrap()).to.matchImage('admin_visitor_generator');
        });
    });

    describe("OtherPages", function () {
        this.title = parentSuite.title; // to make sure the screenshot prefix is the same

        it('should load the glossary correctly', async function () {
            await page.goto("?" + generalParams + "&module=API&action=glossary");

            expect(await screenshotPageWrap()).to.matchImage('glossary');
        });

        it('should load the glossary correctly widgetized', async function () {
            await page.goto("?" + generalParams + "&module=API&action=glossary&widget=1");
            await page.waitForTimeout(200);

            expect(await page.screenshot({fullPage: true})).to.matchImage('glossary_widgetized');
        });

        // DB error message
        it('should fail correctly when db information in config is incorrect', async function () {

            testEnvironment.overrideConfig('database', {
                host: config.phpServer.REMOTE_ADDR,
                username: 'slkdfjsdlkfj',
                password: 'slkdfjsldkfj',
                dbname: 'abcdefg',
                tables_prefix: 'gfedcba'
            });
            testEnvironment.save();

            await page.goto("");

            expect(await page.screenshot({fullPage: true})).to.matchImage('db_connect_error');
        });

        // top bar pages
        it('should load the widgets listing page correctly', async function () {
            await page.goto("?" + generalParams + "&module=Widgetize&action=index");

            visitors = await page.jQuery('.widgetpreview-categorylist>li:contains(Visitors):first');
            await visitors.hover();
            await visitors.click();
            await page.waitForTimeout(100);

            visitorsOT = await page.jQuery('.widgetpreview-widgetlist li:contains(Visits Over Time)');
            await visitorsOT.hover();
            await visitorsOT.click();
            await page.waitForNetworkIdle();

            await page.waitForSelector('.widgetpreview-preview .widget', {visible: true});

            await page.evaluate(function () {
                $('.formEmbedCode').each(function () {
                    var val = $(this).val();
                    val = val.replace(/localhost\:[0-9]+/g, 'localhost');
                    $(this).val(val);
                });
            });

            expect(await screenshotPageWrap()).to.matchImage('widgets_listing');
        });

        it('should load the API listing page correctly', async function () {
            await page.goto("?" + generalParams + "&module=API&action=listAllAPI");

            expect(await screenshotPageWrap()).to.matchImage('api_listing');
        });

        it('should load the email reports page correctly', async function () {
            await page.goto("?" + generalParams + "&module=ScheduledReports&action=index");
            await page.evaluate(function () {
                $('#header').hide();
            });

            expect(await screenshotPageWrap()).to.matchImage('email_reports');
        });

        it('should show the generated report when clicking the download button', async function () {
            await page.evaluate(function () {
                $('#downloadReportForm_7').attr('target', ''); // do not open the download in new windows
            });
            await page.click('#downloadReportForm_7 + a');
            await page.waitForNetworkIdle();

            expect(await page.screenshot({fullPage: true})).to.matchImage('email_reports_download');
        });

        it('should load the scheduled reports when Edit button is clicked', async function () {
            await page.goto("?" + generalParams + "&module=ScheduledReports&action=index");
            await page.click('.entityTable tr:nth-child(3) button[title="Edit"]');

            expect(await screenshotPageWrap()).to.matchImage('email_reports_editor');
        });

        // date range clicked
        it('should reload to the correct date when a date range is selected in the period selector', async function () {
            await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_Visitors&subcategory=VisitTime_SubmenuTimes");
            await page.waitForNetworkIdle();
            await page.click('#date.title');
            await page.click('input#period_id_range');
            await page.evaluate(function () {
                $('#inputCalendarFrom').val('2012-08-02');
                $('#inputCalendarTo').val('2012-08-12');
            });
            await page.waitForTimeout(500);
            await page.evaluate(() => $('#calendarApply').click());

            await page.mouse.move(-10, -10);
            await page.waitForNetworkIdle();

            expect(await page.screenshot({fullPage: true})).to.matchImage('period_select_date_range_click');
        });

        // visitor profile popup
        it('should load the visitor profile popup correctly', async function () {
            await page.goto("?" + widgetizeParams + "&" + idSite2Params + "&moduleToWidgetize=Live&actionToWidgetize=getVisitorProfilePopup"
                + "&enableAnimation=0");

            await page.evaluate(function () {
                $('.visitor-profile-widget-link > span').text('{REPLACED_ID}');
            });

            await (await page.waitForSelector('.visitor-profile-show-map')).click();
            await page.waitForNetworkIdle();
            await page.waitForTimeout(200);

            expect(await page.screenshot({fullPage: true})).to.matchImage('visitor_profile_popup');
        });

        // opt out page
        it('should load the opt out page correctly', async function () {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.save();

            await page.goto("?module=CoreAdminHome&action=optOut&language=en");

            expect(await page.screenshot({fullPage: true})).to.matchImage('opt_out');
        });

        // extra segment tests
        it('should load the row evolution page correctly when a segment is selected', async function () {
            const url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-01-13#?category=General_Visitors&subcategory=CustomVariables_CustomVariables&idSite=1&period=year&date=2012-01-13";
            await page.goto(url);
            const segmentTitle = await page.waitForSelector('.segmentationTitle');
            await segmentTitle.click();
            await page.waitForFunction("$('.segname:contains(From Europe)').length > 0");
            const segment = await page.jQuery('.segname:contains(From Europe)');
            await segment.click();
            await page.waitForNetworkIdle();

            // hovering in puppeteer does not always trigger the mouseenter handler
            await page.evaluate(() => {
              $('.dataTable tbody tr:first-child').trigger('mouseenter');
            });

            const icon = await page.waitForSelector('.dataTable tbody tr:first-child a.actionRowEvolution');
            await icon.click();

            await page.mouse.move(-10, -10);

            await page.waitForSelector('.ui-dialog');
            await page.waitForNetworkIdle();

            await page.mouse.move(-10, -10);

            // test succeeds if the element is present
            await page.waitForSelector('.ui-dialog > .ui-dialog-content > div.rowevolution');
        });

        it('should load the segmented visitor log correctly when a segment is selected', async function () {
            const url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-01-13#?category=General_Visitors&subcategory=CustomVariables_CustomVariables&idSite=1&period=year&date=2012-01-13";
            await page.goto('about:blank');
            await page.goto(url);
            await page.waitForNetworkIdle();
            await page.evaluate(function () {
                $('.segmentationTitle').click();
            });
            await page.waitForTimeout(100);
            await page.evaluate(function () {
                $('.segname:contains(From Europe)').click();
            });
            await page.waitForNetworkIdle();

            elem = await page.$('table.dataTable tbody tr:first-child');
            await elem.hover();
            await page.evaluate(function () {
                var visitorLogLinkSelector = 'table.dataTable tbody tr:first-child a.actionSegmentVisitorLog';
                $(visitorLogLinkSelector).click();
            });
            await page.waitForNetworkIdle();

            await page.mouse.move(-10, -10);

            pageWrap = await page.$('.ui-dialog > .ui-dialog-content > div.dataTableVizVisitorLog');
            expect(await pageWrap.screenshot()).to.matchImage('segmented_visitorlog');
        });

        it('should not apply current segmented when opening visitor log', async function () {
            delete testEnvironment.queryParamOverride.visitorId;
            testEnvironment.save();

            const url = "?" + widgetizeParams + "&" + generalParams + "&moduleToWidgetize=Live&actionToWidgetize=getVisitorLog&segment=visitCount==2&enableAnimation=0";
            await page.goto(url);
            await page.waitForNetworkIdle();

            await page.evaluate(function () {
                $('.visitor-log-visitor-profile-link').first().click();
            });

            await page.waitForNetworkIdle();

            await page.evaluate(function () {
                $('.visitor-profile-widget-link > span').text('{REPLACED_ID}');
            });

            const popover = await page.$('#Piwik_Popover');
            expect(await popover.screenshot()).to.matchImage('visitor_profile_not_segmented');
        });

        it('should display API errors properly without showing them as notifications', async function () {
            var url = "?" + generalParams + "&module=CoreHome&action=index#?" + generalParams + "&category=%7B%7Bconstructor.constructor(%22_x(45)%22)()%7D%7D&subcategory=%7B%7Bconstructor.constructor(%22_x(48)%22)()%7D%7D&forceError=1";
            var adminUrl = "?" + generalParams + "&module=CoreAdminHome&action=home";

            await page.goto(url);
            await page.waitForNetworkIdle();

            await page.goto(adminUrl);
            await page.waitForSelector('#notificationContainer');

            const notificationContent = await page.evaluate(() => $('#notificationContainer').text().trim());

            // ensure no notification is being displayed
            expect(notificationContent).to.be.empty;
        });
    });

    // embedding whole app
    describe('enable_framed_pages', function () {
        beforeEach(function () {
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.overrideConfig('General', 'enable_framed_pages', 1);
            testEnvironment.save();
        });

        afterEach(function () {
            testEnvironment.testUseMockAuth = 1;
            if (testEnvironment.configOverride.General && testEnvironment.configOverride.General.enable_framed_pages) {
                delete testEnvironment.configOverride.General.enable_framed_pages;
            }
            testEnvironment.save();
        });

        it('should allow embedding the entire app', async function () {
            var url = "tests/resources/embed-file.html#" + encodeURIComponent(page.baseUrl + 'index.php?' + urlBase + '&token_auth=a4ca4238a0b923820dcc509a6f75849f');
            await page.goto(url);
            await page.waitForNetworkIdle();

            const frame = page.frames().find(f => f.name() === 'embed');
            await frame.waitForSelector('.widget');
            await page.waitForTimeout(1000); // wait for widgets to render

            expect(await page.screenshot({ fullPage: true })).to.matchImage('embed_whole_app');
        });
    });
});
