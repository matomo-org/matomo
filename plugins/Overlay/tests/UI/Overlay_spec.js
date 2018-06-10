/*!
 * Matomo - free/libre analytics platform
 *
 * Overlay screenshot tests.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
describe("Overlay", function () {
    this.retries(3);

    this.timeout(0);

    var url = null;
    var urlWithSegment;

    function removeOptOutIframe(page) {
        page.evaluate(function () {
            $('iframe#optOutIframe', $('iframe').contents()).remove();
        });
    }

    before(async function() {
        var baseUrl = '?module=Overlay&period=year&date=today&idSite=3';
        var hash = '#?l=' + encodeURIComponent(testEnvironment.overlayUrl).replace(/[%]/g, "$");

        url = baseUrl + hash;
        urlWithSegment = baseUrl + '&segment=' + encodeURIComponent('visitIp==20.56.34.67') + hash;

        testEnvironment.callApi("SitesManager.addSiteAliasUrls", {idSite: 3, urls: [config.piwikUrl]}, done);
    });

    after(async function() {
        testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []}, done);
    });

    it("should load correctly", async function() {
        expect.screenshot("loaded").to.be.capture(function (page) {
            page.load(url);

            removeOptOutIframe(page);
        }, done);
    });

    it("should show clicks when hover over link in iframe", async function() {
        expect.screenshot("page_link_clicks").to.be.capture(function (page) {
            var pos = page.webpage.evaluate(function () {
                var iframe = $('iframe'),
                    innerOffset = $('.btn.btn-large', iframe.contents()).offset();
                return {
                    x: iframe.offset().left + innerOffset.left,
                    y: iframe.offset().top + innerOffset.top
                };
            });
            page.sendMouseEvent('mousemove', pos);

            page.evaluate(function () {
                $('div#PIS_StatusBar', $('iframe').contents()).each(function () {
                    var html = $(this).html();
                    html = html.replace(/localhost\:[0-9]+/g, 'localhost');
                    $(this).html(html);
                });
            });

            removeOptOutIframe(page);
        }, done);
    });

    it("should show stats for new links when dropdown opened", async function() {
        expect.screenshot("page_new_links").to.be.capture(function (page) {
            page.reload(2500);
            page.evaluate(function(){
                $('.dropdown-toggle', $('iframe').contents())[0].click();
            }, 500);
            removeOptOutIframe(page);
        }, done);
    });

    it("should change page when clicking on internal iframe link", async function() {
        expect.screenshot("page_change").to.be.capture(function (page) {
            var pos = page.webpage.evaluate(function () {
                var iframe = $('iframe'),
                    innerOffset = $('ul.nav>li:nth-child(2)>a', iframe.contents()).offset();
                return {
                    x: iframe.offset().left + innerOffset.left + 32, // position is incorrect for some reason w/o adding pixels
                    y: iframe.offset().top + innerOffset.top
                };
            });
            page.sendMouseEvent('click', pos);

            removeOptOutIframe(page);
        }, done);
    });

    it("should change date range when period changed", async function() {
        expect.screenshot("period_change").to.be.capture(function (page) {
            page.evaluate(function () {
                $('#overlayDateRangeSelect').val('day;yesterday').trigger('change');
            });

            removeOptOutIframe(page);
        }, done);
    });

    it("should open row evolution popup when row evolution link clicked", async function() {
        expect.screenshot("row_evolution").to.be.capture(function (page) {
            page.evaluate(function () {
                $('#overlayRowEvolution').click();
            }, 500);
            page.evaluate(function () {
                $('.jqplot-xaxis').hide(); // xaxis will change every day so hide it
            });

            removeOptOutIframe(page);
        }, done);
    });

    it("should open transitions popup when transitions link clicked", async function() {
        expect.screenshot("transitions").to.be.capture(function (page) {
            page.evaluate(function () {
                $('button.ui-dialog-titlebar-close').click();
            }, 500);
            page.evaluate(function () {
                $('#overlayTransitions').click();
            }, 500);

            removeOptOutIframe(page);
        }, done);
    });

    it("should load an overlay with segment", async function() {
        expect.screenshot("loaded_with_segment").to.be.capture(function (page) {
            page.load(urlWithSegment);

            removeOptOutIframe(page);
        }, done);
    });
});