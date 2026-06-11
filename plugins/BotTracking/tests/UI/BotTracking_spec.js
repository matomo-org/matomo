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

        // Assert exactly 3 report widgets are present on the page.
        const widgets = await page.$$('.matomo-widget');
        expect(widgets.length).to.equal(3);

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

        var elem = await page.$('.pageWrap');
        expect(await elem.screenshot()).to.matchImage('bot_content_requests');
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

        // All 3 content-request widgets must show the segment-not-supported footer message.
        expect(matchingFooterMessages).to.be.at.least(3);
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
