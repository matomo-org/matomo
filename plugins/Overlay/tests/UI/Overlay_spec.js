/*!
 * Matomo - free/libre analytics platform
 *
 * Overlay screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("Overlay", function () {
    this.timeout(0);

    var baseUrl;
    var hash;
    var url = null;
    var urlWithSegment;

    async function removeOptOutIframe() {
        await page.evaluate(function () {
            $('iframe#optOutIframe', $('iframe').contents()).remove();
        });
    }

    before(async function () {
        baseUrl = '?module=Overlay&period=year&date=today&idSite=3';
        hash = '#?l=' + encodeURIComponent(testEnvironment.overlayUrl).replace(/[%]/g, "$");

        url = baseUrl + hash;
        urlWithSegment = baseUrl + '&segment=' + encodeURIComponent('visitIp==50.112.3.5') + hash;

        await testEnvironment.callApi("SitesManager.addSiteAliasUrls", {idSite: 3, urls: [config.piwikUrl]});
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        if (testEnvironment.configOverride.General && testEnvironment.configOverride.General.enable_framed_pages) {
            delete testEnvironment.configOverride.General.enable_framed_pages;
        }
        testEnvironment.save();

        await testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []});
    });

    it("should load correctly", async function() {
        await page.goto(url);

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('loaded');
    });

    it("should show clicks when hover over link in iframe", async function() {
        var pos = await page.webpage.evaluate(() => {
            var iframe = $('iframe'),
                innerOffset = $('.btn.btn-large', iframe.contents()).offset();
            return {
                x: iframe.offset().left + innerOffset.left,
                y: iframe.offset().top + innerOffset.top
            };
        });
        await page.mouse.move(pos.x, pos.y);

        await page.evaluate(function () {
            $('div#PIS_StatusBar', $('iframe').contents()).each(function () {
                var html = $(this).html();
                html = html.replace(/localhost\:[0-9]+/g, 'localhost');
                $(this).html(html);
            });
        });
        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('page_link_clicks');
    });

    it("should show stats for new links when dropdown opened", async function() {
        await page.reload();
        await page.evaluate(function(){
            $('.dropdown-toggle', $('iframe').contents())[0].click();
        });
        await page.waitForTimeout(1000);

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('page_new_links');
    });

    it("should change page when clicking on internal iframe link", async function() {
        var pos = await page.webpage.evaluate(() => {
            var iframe = $('iframe'),
                innerOffset = $('ul.nav>li:nth-child(2)>a', iframe.contents()).offset();
            return {
                x: iframe.offset().left + innerOffset.left + 32, // position is incorrect for some reason w/o adding pixels
                y: iframe.offset().top + innerOffset.top
            };
        });
        await page.mouse.click(pos.x, pos.y);
        await page.waitForNetworkIdle();

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('page_change');
    });

    it("should change date range when period changed", async function() {
        await page.waitForSelector('#overlayDateRangeSelect');
        await page.webpage.evaluate(function () {
            $('#overlayDateRangeSelect').val('day;yesterday').trigger('change');
        });

        await page.waitForSelector('.overlayMainMetrics,.overlayNoData');
        await page.waitForNetworkIdle();

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('period_change');
    });

    it("should open row evolution popup when row evolution link clicked", async function() {
        await page.evaluate(function(){
            $('#overlayRowEvolution').click();
        });
        await page.waitForTimeout(500); // for modal to appear
        await page.waitForNetworkIdle();
        await page.evaluate(function () {
            $('.jqplot-xaxis').hide(); // xaxis will change every day so hide it
        });

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('row_evolution');
    });

    it("should open transitions popup when transitions link clicked", async function() {
        await page.click('button.ui-dialog-titlebar-close');
        await page.waitForSelector('#overlayTransitions');
        await page.click('#overlayTransitions');
        await page.waitForNetworkIdle();
        await page.waitForTimeout(2000);

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('transitions');
    });

    it("should load an overlay with segment", async function() {
        await page.goto(urlWithSegment);
        await page.waitForNetworkIdle();

        await page.waitForTimeout(2000);

        const frame = page.frames().find(f => f.name() === 'overlayIframe');
        await frame.waitForSelector('.PIS_LinkTag');

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('loaded_with_segment');
    });

    it('should load correctly with token_auth if enable_framed_pages is set', async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.overrideConfig('General', 'enable_framed_pages', 1);
        testEnvironment.save();

        await page.goto(baseUrl + '&token_auth=a4ca4238a0b923820dcc509a6f75849f' + hash);
        await page.waitForSelector('.overlayMainMetrics,.overlayNoData');

        await removeOptOutIframe();
        expect(await page.screenshot({ fullPage: true })).to.matchImage('framed_loaded');
    });
});