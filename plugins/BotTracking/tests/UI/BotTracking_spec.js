/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("BotTracking", function () {
    this.timeout(0);

    this.fixture = "Piwik\\Plugins\\BotTracking\\tests\\Fixtures\\BotTraffic";

    var generalParams = 'idSite=1&period=day&date=2025-02-02',
        urlBase = 'module=CoreHome&action=index&' + generalParams;

    async function waitForRowEvolutionPopover() {
        await page.waitForFunction('$(".ui-dialog:visible .rowevolution").length > 0');
        await page.waitForFunction(
            '$(".ui-dialog:visible .rowevolution table.metrics tr").length > 0'
            + ' && $(".ui-dialog:visible .rowevolution .jqplot-target").length > 0'
        );
        await page.waitForTimeout(250);
    }

    it('should render AI Assistants > AI Chatbots Overview page with evolution and sparkline', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsOverview");
        await page.waitForNetworkIdle();

        await page.hover('.jqplot-seriespicker');

        const availableMetrics = await page.$$('.jqplot-seriespicker input.select');
        expect(availableMetrics.length).to.equal(8);

        await page.mouse.move(0, 0);

        const sparklines = await page.$$('.sparkline-metrics');
        expect(sparklines.length).to.equal(8);

        var elem = await page.$('.pageWrap');
        expect(await elem.screenshot()).to.matchImage('bot_overview');
    });

    it('should not have shown a "no recent tracking requests" message', async function () {
        const notifications = await page.$$('.bot-tracking-no-recent-requests-message');
        expect(notifications.length).to.equal(0);
    })

    it('should not show unique pages and documents metric for higher periods', async function () {
        await page.goto("?" + urlBase + "#?idSite=1&period=week&date=2025-02-02&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsOverview");
        await page.waitForNetworkIdle();

        await page.hover('.jqplot-seriespicker');

        const availableMetrics = await page.$$('.jqplot-seriespicker input.select');
        expect(availableMetrics.length).to.equal(6);

        const sparklines = await page.$$('.sparkline-metrics');
        expect(sparklines.length).to.equal(6);
    });

    it('should render AI Assistants > AI Chatbots Overview bot detail report', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsOverview");
        await page.waitForNetworkIdle();

        const row = await page.jQuery('tr.subDataTable:first');
        await row.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        var elem = await page.$('#widgetBotTrackinggetAIChatbotRequests');
        expect(await elem.screenshot()).to.matchImage('bot_requests');
    });

    it('should switch to secondary dimension when clicked', async function () {
        await page.evaluate(() => $('.datatableRelatedReports li span:contains("Document Requests")').click());
        await page.waitForNetworkIdle();

        const row = await page.jQuery('tr.subDataTable:first');
        await row.click();
        await page.mouse.move(-10, -10);

        await page.waitForNetworkIdle();
        await page.waitForTimeout(250); // rendering

        var elem = await page.$('#widgetBotTrackinggetAIChatbotRequests');
        expect(await elem.screenshot()).to.matchImage('bot_requests_documents');
    });

    it('should render AI Assistants > AI Chatbots Content Requests page', async function () {
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsContentRequests");
        await page.waitForNetworkIdle();

        // Assert the help icon for this subcategory exists in the reporting menu (proves getHelp()
        // returns non-empty content — the icon only renders when subcategory.help is truthy).
        // The active subcategory li in the menu contains the help icon as a sibling of .item.
        const helpIconCount = await page.evaluate(function () {
            return jQuery(
                '.reportingMenu ul.navbar > li.menuTab.active > ul > li.active .item-help-icon'
            ).length;
        });
        expect(helpIconCount).to.equal(1);

        // Click the help icon via jQuery to trigger the help notification for this subcategory.
        // (Direct puppeteer click fails because the icon is CSS-hidden until hover.)
        await page.evaluate(function () {
            jQuery(
                '.reportingMenu ul.navbar > li.menuTab.active > ul > li.active .item-help-icon'
            ).trigger('click');
        });
        // Wait until the help notification is visible in the notification container.
        await page.waitForSelector('#notificationContainer .help-notification', { visible: true });

        // Assert the help notification body contains non-empty text (proving getHelp() returned content).
        const helpNotificationText = await page.evaluate(function () {
            var notif = document.querySelector('#notificationContainer .help-notification .notification-body');
            return notif ? notif.textContent.trim() : '';
        });
        expect(helpNotificationText.length).to.be.above(0);

        // Assert exactly 5 report widgets are present on the page (the wide Pages report on top,
        // then the Documents / Broken pair side-by-side, then the Human-Favoured / AI-Favoured
        // pair side-by-side).
        const widgets = await page.$$('.matomo-widget');
        expect(widgets.length).to.equal(5);

        // Dismiss the help notification so it does not obscure table headers,
        // then wait for the tables to be fully rendered.
        await page.evaluate(function () {
            jQuery('#notificationContainer .notification .close').trigger('click');
        });
        await page.waitForNetworkIdle();

        // Wait explicitly for the Pages widget's table header to appear before querying.
        await page.waitForSelector('#widgetBotTrackinggetAIChatbotContentPages thead th .thDIV', { visible: true });

        // Assert per-widget column counts and header text for Pages widget.
        // The DataTable renders column labels inside <div class="thDIV"> within each <th>.
        // Each widget should display exactly 4 default columns: label + 3 metrics.
        const pagesWidgetId = '#widgetBotTrackinggetAIChatbotContentPages';
        const pagesHeaders = await page.$$eval(pagesWidgetId + ' thead th .thDIV', function (divs) {
            return divs.map(function (d) { return (d.textContent || '').trim(); });
        });
        expect(pagesHeaders.length).to.equal(4);
        expect(pagesHeaders[0]).to.equal('Page URL');
        expect(pagesHeaders[1]).to.equal('Requests');
        expect(pagesHeaders[2]).to.equal('Avg. Server Time');
        expect(pagesHeaders[3]).to.equal('Avg. Response Size');

        // Assert default sort indicator on the Requests column in the Pages widget.
        // The sorted th gets class "columnSorted"; sort direction is a child span with class "sortIcon desc".
        const pagesSortedThCount = await page.$$eval(pagesWidgetId + ' thead th.columnSorted', function (ths) { return ths.length; });
        expect(pagesSortedThCount).to.equal(1);
        const pagesSortedThText = await page.$eval(pagesWidgetId + ' thead th.columnSorted .thDIV', function (div) {
            return (div.textContent || '').trim();
        });
        expect(pagesSortedThText).to.equal('Requests');

        // Assert per-widget column counts and header text for Documents widget.
        const docsWidgetId = '#widgetBotTrackinggetAIChatbotContentDocuments';
        const docsHeaders = await page.$$eval(docsWidgetId + ' thead th .thDIV', function (divs) {
            return divs.map(function (d) { return (d.textContent || '').trim(); });
        });
        expect(docsHeaders.length).to.equal(4);
        expect(docsHeaders[0]).to.equal('Document URL');
        expect(docsHeaders[1]).to.equal('Requests');
        expect(docsHeaders[2]).to.equal('Avg. Server Time');
        expect(docsHeaders[3]).to.equal('Avg. Response Size');

        // Assert default sort indicator on the Requests column in the Documents widget.
        const docsSortedThCount = await page.$$eval(docsWidgetId + ' thead th.columnSorted', function (ths) { return ths.length; });
        expect(docsSortedThCount).to.equal(1);
        const docsSortedThText = await page.$eval(docsWidgetId + ' thead th.columnSorted .thDIV', function (div) {
            return (div.textContent || '').trim();
        });
        expect(docsSortedThText).to.equal('Requests');

        // Assert per-widget column counts and header text for Broken Content widget.
        const brokenWidgetId = '#widgetBotTrackinggetAIChatbotBrokenContent';
        const brokenHeaders = await page.$$eval(brokenWidgetId + ' thead th .thDIV', function (divs) {
            return divs.map(function (d) { return (d.textContent || '').trim(); });
        });
        expect(brokenHeaders.length).to.equal(4);
        expect(brokenHeaders[0]).to.equal('URL');
        expect(brokenHeaders[1]).to.equal('Total Broken Requests');
        expect(brokenHeaders[2]).to.equal('Page Not Found (404) Requests');
        expect(brokenHeaders[3]).to.equal('5XX Requests');

        // Assert default sort indicator on total_broken_requests in the Broken widget.
        const brokenSortedThCount = await page.$$eval(brokenWidgetId + ' thead th.columnSorted', function (ths) { return ths.length; });
        expect(brokenSortedThCount).to.equal(1);
        const brokenSortedThText = await page.$eval(brokenWidgetId + ' thead th.columnSorted .thDIV', function (div) {
            return (div.textContent || '').trim();
        });
        expect(brokenSortedThText).to.equal('Total Broken Requests');

        // Assert per-widget column counts and header text for Human-Favoured widget.
        const humanWidgetId = '#widgetBotTrackinggetAIChatbotHumanFavouredPages';
        await page.waitForSelector(humanWidgetId + ' thead th .thDIV', { visible: true });
        const humanHeaders = await page.$$eval(humanWidgetId + ' thead th .thDIV', function (divs) {
            return divs.map(function (d) { return (d.textContent || '').trim(); });
        });
        expect(humanHeaders.length).to.equal(4);
        expect(humanHeaders[0]).to.equal('Page URL');
        expect(humanHeaders[1]).to.equal('Unique Human Pageviews');
        expect(humanHeaders[2]).to.equal('AI Chatbot Requests');
        expect(humanHeaders[3]).to.equal('Discrepancy Score');

        // Default sort on Discrepancy Score for the Human-Favoured widget.
        const humanSortedThText = await page.$eval(humanWidgetId + ' thead th.columnSorted .thDIV', function (div) {
            return (div.textContent || '').trim();
        });
        expect(humanSortedThText).to.equal('Discrepancy Score');

        // Assert per-widget column counts and header text for AI-Favoured widget.
        const aiWidgetId = '#widgetBotTrackinggetAIChatbotAIFavouredPages';
        const aiHeaders = await page.$$eval(aiWidgetId + ' thead th .thDIV', function (divs) {
            return divs.map(function (d) { return (d.textContent || '').trim(); });
        });
        // The AI-Favoured report leads with its strong-side metric (AI Chatbot Requests), then human.
        expect(aiHeaders.length).to.equal(4);
        expect(aiHeaders[0]).to.equal('Page URL');
        expect(aiHeaders[1]).to.equal('AI Chatbot Requests');
        expect(aiHeaders[2]).to.equal('Unique Human Pageviews');
        expect(aiHeaders[3]).to.equal('Discrepancy Score');

        // Default sort on Discrepancy Score for the AI-Favoured widget.
        const aiSortedThText = await page.$eval(aiWidgetId + ' thead th.columnSorted .thDIV', function (div) {
            return (div.textContent || '').trim();
        });
        expect(aiSortedThText).to.equal('Discrepancy Score');

        // The reporting page auto-pairs consecutive non-wide widgets into a 2-column row (see
        // CoreHome ReportingPage.store). The Documents + Broken reports must share one row, and the
        // Human-Favoured + AI-Favoured reports must share one row.
        const sameRow = function (firstSel, secondSel) {
            return page.evaluate(function (a, b) {
                const first = document.querySelector(a);
                const second = document.querySelector(b);
                if (!first || !second) {
                    return 0;
                }
                const firstRow = first.closest('.row');
                const secondRow = second.closest('.row');
                return firstRow && firstRow === secondRow ? 1 : 0;
            }, firstSel, secondSel);
        };
        expect(await sameRow(docsWidgetId, brokenWidgetId)).to.equal(1);
        expect(await sameRow(humanWidgetId, aiWidgetId)).to.equal(1);

        var elem = await page.$('.pageWrap');
        expect(await elem.screenshot()).to.matchImage('bot_content_requests');
    });

    it('should offer Row Evolution on the Favoured Pages reports and render its chart', async function () {
        // Use a date inside the human/AI overlap window (Feb 3-5) so the evolution chart has a real
        // multi-day series. Row Evolution is only available now the reports are archived.
        await page.goto("?" + urlBase + "#?idSite=1&period=day&date=2025-02-03&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsContentRequests");
        await page.waitForNetworkIdle();

        const humanWidgetId = '#widgetBotTrackinggetAIChatbotHumanFavouredPages';
        const row = await page.waitForSelector(humanWidgetId + ' table.dataTable tbody tr:first-child');
        await row.hover();
        await page.waitForTimeout(100);

        // The Row Evolution row action must now be offered (disable_row_evolution was removed once the
        // data is archived, so the icon is no longer suppressed).
        const icon = await row.$('a.actionRowEvolution');
        expect(icon).to.not.equal(null);

        await icon.hover();
        await icon.click();
        await page.mouse.move(-10, -10);

        await waitForRowEvolutionPopover();

        expect(await page.screenshotSelector('.ui-dialog:visible')).to.matchImage('row_evolution_favoured_pages');
    });

    it('should show segment not supported footer message on AI Chatbots Content Requests page when segmented', async function () {
        const segment = encodeURIComponent('visitConverted==1');
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsContentRequests&segment=" + segment);
        await page.waitForNetworkIdle();

        const expectedMessage = 'Report does not support segmentation. The data displayed is your standard, unsegmented report data.';
        const matchingFooterMessages = await page.$$eval('.datatableFooterMessage', (nodes, expected) => {
            return nodes
                .map((node) => (node.textContent || '').trim())
                .filter((text) => text.includes(expected))
                .length;
        }, expectedMessage);

        // All 5 content-request widgets must show the segment-not-supported footer message.
        expect(matchingFooterMessages).to.be.at.least(5);
    });

    it('should show segment not supported footer message in AI bot reports when segmented', async function () {
        const segment = encodeURIComponent('visitConverted==1');
        await page.goto("?" + urlBase + "#?" + generalParams + "&category=General_AIAssistants&subcategory=BotTracking_AIChatbotsOverview&segment=" + segment);
        await page.waitForNetworkIdle();

        const expectedMessage = 'Report does not support segmentation. The data displayed is your standard, unsegmented report data.';
        const matchingFooterMessages = await page.$$eval('.datatableFooterMessage', (nodes, expected) => {
            return nodes
                .map((node) => (node.textContent || '').trim())
                .filter((text) => text.includes(expected))
                .length;
        }, expectedMessage);

        expect(matchingFooterMessages).to.be.at.least(3);
    });
});
