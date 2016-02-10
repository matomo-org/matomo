    /*!
 * Piwik - free/libre analytics platform
 *
 * Overlay screenshot tests.
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

// TODO: should be stored in Overlay plugin
describe("Overlay", function () {
    this.timeout(0);

    var url = null;
    var urlWithSegment;

    function removeOptOutIframe(page) {
        page.evaluate(function () {
            $('iframe#optOutIframe', $('iframe').contents()).remove();
        });
    }

    before(function (done) {
        var baseUrl = '?module=Overlay&period=year&date=today&idSite=3';
        var hash = '#l=' + encodeURIComponent(testEnvironment.overlayUrl).replace(/[%]/g, "$");

        url = baseUrl + hash;
        urlWithSegment = baseUrl + '&segment=' + encodeURIComponent('visitIp==20.56.34.67') + hash;

        testEnvironment.callApi("SitesManager.addSiteAliasUrls", {idSite: 3, urls: [config.piwikUrl]}, done);
    });

    after(function (done) {
        testEnvironment.callApi("SitesManager.setSiteAliasUrls", {idSite: 3, urls: []}, done);
    });

    it("should load correctly", function (done) {
        expect.screenshot("loaded").to.be.capture(function (page) {
            page.load(url);

            removeOptOutIframe(page);
        }, done);
    });

    it("should show clicks when hover over link in iframe", function (done) {
        expect.screenshot("page_link_clicks").to.be.capture(function (page) {
            var pos = page.webpage.evaluate(function () {
                var iframe = $('iframe'),
                    innerOffset = $('.btn.btn-lg', iframe.contents()).offset();
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

    it("should show stats for new links when dropdown opened", function (done) {
        expect.screenshot("page_new_links").to.be.capture(function (page) {
            var pos = page.webpage.evaluate(function () {
                var iframe = $('iframe'),
                    innerOffset = $('.dropdown-toggle', iframe.contents()).offset();
                return {
                    x: iframe.offset().left + innerOffset.left + 32, // position is incorrect for some reason w/o adding pixels
                    y: iframe.offset().top + innerOffset.top
                };
            });
            page.sendMouseEvent('click', pos, 2000);

            removeOptOutIframe(page);
        }, done);
    });

    it("should change page when clicking on internal iframe link", function (done) {
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

    it("should change date range when period changed", function (done) {
        expect.screenshot("period_change").to.be.capture(function (page) {
            page.evaluate(function () {
                $('#overlayDateRangeSelect').val('day;yesterday').trigger('change');
            });

            removeOptOutIframe(page);
        }, done);
    });

    it("should open row evolution popup when row evolution link clicked", function (done) {
        expect.screenshot("row_evolution").to.be.capture(function (page) {
            page.click('#overlayRowEvolution');
            page.evaluate(function () {
                $('.jqplot-xaxis').hide(); // xaxis will change every day so hide it
            });

            removeOptOutIframe(page);
        }, done);
    });

    it("should open transitions popup when transitions link clicked", function (done) {
        expect.screenshot("transitions").to.be.capture(function (page) {
            page.click('button.ui-dialog-titlebar-close');
            page.click('#overlayTransitions');

            removeOptOutIframe(page);
        }, done);
    });

    it("should load an overlay with segment", function (done) {
        expect.screenshot("loaded_with_segment").to.be.capture(function (page) {
            page.load(urlWithSegment);

            removeOptOutIframe(page);
        }, done);
    });
});