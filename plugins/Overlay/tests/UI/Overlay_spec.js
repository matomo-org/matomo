/*!
 * Matomo - free/libre analytics platform
 *
 * Overlay screenshot tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("Overlay", function () {
    this.timeout(0);

    async function removeOptOutIframe(page) {
        const frame = page.frames().find(f => f.name() === 'overlayIframe');
        if (frame) {
            await frame.evaluate(function () {
                $('iframe#optOutIframe').remove();
            });
        }
    }

    function getUrl (useTokenAuth, withSegment) {
        var baseUrl = '?module=Overlay&period=year&date=today&idSite=3';
        var hash = '#?l=' + encodeURIComponent(testEnvironment.overlayUrl).replace(/[%]/g, "$");

        if (useTokenAuth === true) {
            baseUrl += '&token_auth=a4ca4238a0b923820dcc509a6f75849f';
            testEnvironment.testUseMockAuth = 0;
            testEnvironment.overrideConfig('General', 'enable_framed_pages', 1);
            testEnvironment.save();
        }

        if (withSegment) {
            return baseUrl + '&segment=' + encodeURIComponent('visitIp==50.112.3.5') + hash;
        }

        return baseUrl + hash;
    }

    before(async function () {
        await testEnvironment.callApi("SitesManager.addSiteAliasUrls", {idSite: 3, urls: [config.piwikUrl, '127.0.0.1']});
    });

    after(async function () {
        testEnvironment.testUseMockAuth = 1;
        if (testEnvironment.configOverride.General && testEnvironment.configOverride.General.enable_framed_pages) {
            delete testEnvironment.configOverride.General.enable_framed_pages;
        }
        testEnvironment.save();

        await testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []});
    });

    var testCases = [false, true];
    for (var index = 0; index < testCases.length; index++) {
        (function(useTokenAuth) {

            var descAppendix = useTokenAuth ? ' (with auth_token)' : '';

            it("should load correctly" + descAppendix, async function () {
                await page.goto(getUrl(useTokenAuth));
                // wait for sidebar to be finished loading
                await page.waitForSelector('#overlaySidebar', {visible: true});

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('loaded');
            });

            it("should show clicks when hover over link in iframe" + descAppendix, async function () {

                const frame = page.frames().find(f => f.name() === 'overlayIframe');
                await (await frame.$('.btn.btn-large')).hover();
                await page.waitForTimeout(250);

                await frame.evaluate(function () {
                    $('div#PIS_StatusBar').each(function () {
                        var html = $(this).html();
                        html = html.replace(/localhost\:[0-9]+/g, 'localhost');
                        $(this).html(html);
                    });
                });
                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('page_link_clicks');
            });

            it("should show stats for new links when dropdown opened" + descAppendix, async function () {
                await page.reload();
                // wait for sidebar to be finished loading
                await page.waitForSelector('#overlaySidebar', {visible: true});
                const frame = page.frames().find(f => f.name() === 'overlayIframe');
                await (await frame.$('.dropdown-toggle')).click();

                await page.waitForTimeout(2000);

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('page_new_links');
            });

            it("should change page when clicking on internal iframe link" + descAppendix, async function () {
                const frame = page.frames().find(f => f.name() === 'overlayIframe');
                await (await frame.$('ul.nav>li:nth-child(2)>a')).click();
                await page.waitForNetworkIdle();

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('page_change');
            });

            it("should change date range when period changed" + descAppendix, async function () {
                await page.waitForSelector('#overlayDateRangeSelect');
                await page.webpage.evaluate(function () {
                    $('#overlayDateRangeSelect').val('day;yesterday').trigger('change');
                });

                await page.waitForSelector('.overlayMainMetrics,.overlayNoData');
                await page.waitForNetworkIdle();

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('period_change');
            });

            it("should open row evolution popup when row evolution link clicked" + descAppendix, async function () {
                await page.evaluate(function () {
                    $('#overlayRowEvolution').click();
                });
                await page.waitForTimeout(500); // for modal to appear
                await page.waitForNetworkIdle();
                await page.evaluate(function () {
                    $('.jqplot-xaxis').hide(); // xaxis will change every day so hide it
                });

                await page.evaluate(function () {
                    $('.ui-dialog .ui-dialog-title,.ui-dialog h2').each(function () {
                        var html = $(this).html();
                        // ensure to use localhost as url for screenshots
                        html = html.split('127.0.0.1').join('localhost')
                        $(this).html(html);
                    });
                });
                await page.waitForTimeout(500);

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('row_evolution');
            });

            it("should open transitions popup when transitions link clicked" + descAppendix, async function () {
                await page.click('button.ui-dialog-titlebar-close');
                await page.waitForSelector('#overlayTransitions');
                await page.click('#overlayTransitions');
                await page.waitForNetworkIdle();
                await page.waitForTimeout(2000);

                await page.evaluate(function () {
                    $('.Transitions_Text').each(function () {
                        var html = $(this).html();
                        // ensure to use localhost as url for screenshots
                        html = html.split('127.<wbr>​0.<wbr>​0.<wbr>​1').join('localhost')
                        $(this).html(html);
                    });
                });

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('transitions');
            });

            it("should load an overlay with segment" + descAppendix, async function () {
                await page.goto(getUrl(useTokenAuth, true));
                // wait for sidebar to be finished loading
                await page.waitForSelector('#overlaySidebar', {visible: true});

                await page.waitForTimeout(2000);

                const frame = page.frames().find(f => f.name() === 'overlayIframe');
                await frame.waitForSelector('.PIS_LinkTag');

                await removeOptOutIframe(page);
                expect(await page.screenshot({fullPage: true})).to.matchImage('loaded_with_segment');
            });
        })(testCases[index]);
    }

    it("should load overlay correctly when coming from an widgetized action report", async function () {
        testEnvironment.testUseMockAuth = 0;
        testEnvironment.overrideConfig('General', 'enable_framed_pages', 1);
        testEnvironment.overrideConfig('General', 'enable_framed_allow_write_admin_token_auth', 1);
        testEnvironment.save();

        await page.goto('?module=Widgetize&action=iframe&disableLink=0&widget=1&moduleToWidgetize=Actions&actionToWidgetize=getPageUrls&idSite=3&period=year&date=today&disableLink=1&widget=1&flat=1&token_auth=a4ca4238a0b923820dcc509a6f75849f', {waitUntil: 'networkidle0'});
        await page.waitForNetworkIdle();

        const row = await page.jQuery('.dataTable tbody tr:first', { waitFor: true });
        await row.hover();

        const icon = await page.waitForSelector('.dataTable tbody tr a.actionOverlay');

        const [popup] = await Promise.all([
            new Promise(resolve => page.once('popup', resolve)),
            await icon.click()
        ]);

        await popup.waitForTimeout(2500);

        await removeOptOutIframe(popup);

        await popup.waitForSelector('#overlayLoading', {hidden: true});
        await popup.click('#overlayDateRangeSelection');

        // Select yesterday
        await popup.waitForSelector('#overlayDateRangeSelection .select-dropdown li:nth-child(2)', {visible: true});
        await popup.click('#overlayDateRangeSelection .select-dropdown li:nth-child(2)');
        await page.waitForNetworkIdle();
        await popup.waitForSelector('#overlayLoading', {hidden: true});

        expect(await popup.screenshot({fullPage: true})).to.matchImage('loaded_from_actions');
    });

});
