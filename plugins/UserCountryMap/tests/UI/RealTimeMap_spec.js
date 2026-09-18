/*!
 * Matomo - free/libre analytics platform
 *
 * Real time map auto refresh tests.
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("RealTimeMap", function () {
    var url = "?module=CoreHome&action=index&idSite=1&period=year&date=2012-08-09"
        + "#?idSite=2&period=year&date=2012-08-09&category=General_Visitors"
        + "&subcategory=UserCountryMap_RealTimeMap&showDateTime=0&realtimeWindow=last2"
        + "&changeVisitAlpha=0&enableAnimation=0&removeOldVisits=0";

    before(function () {
        // the loop has to retry within the lifetime of a test
        testEnvironment.overrideConfig('General', 'live_widget_refresh_after_seconds', 1);
        testEnvironment.save();
    });

    after(function () {
        if (testEnvironment.configOverride.General) {
            delete testEnvironment.configOverride.General.live_widget_refresh_after_seconds;
        }
        testEnvironment.save();
    });

    /**
     * Loads the map, waits for its first report, then fails every following report request.
     */
    async function failEveryReportRequest(status) {
        await page.goto('about:blank');
        await page.goto(url);
        await page.waitForSelector('.RealTimeMap_map svg');
        await page.waitForNetworkIdle();

        await page.evaluate(function (failStatus) {
            window.__mapRequestCount = 0;

            var originalAjax = window.$.ajax;

            window.$.ajax = function (options) {
                var requestUrl = (options && options.url) || '';
                if (requestUrl.indexOf('Live.getLastVisitsDetails') === -1) {
                    return originalAjax.apply(this, arguments);
                }

                window.__mapRequestCount += 1;

                var xhr = {
                    status: failStatus,
                    statusText: 'error',
                    getResponseHeader: function () { return null; },
                    abort: function () {},
                    done: function () { return this; },
                    fail: function (callback) {
                        // a real response never arrives synchronously
                        window.setTimeout(function () { callback(xhr); }, 0);
                        return this;
                    }
                };

                return xhr;
            };
        }, status);
    }

    it("should keep refreshing when a report request fails", async function () {
        await failEveryReportRequest(500);

        // one failure, then a retry after 1s and another after 2s
        await page.waitForFunction('window.__mapRequestCount >= 3', { timeout: 20000 });

        expect(await page.evaluate('window.__mapRequestCount')).to.be.at.least(3);
    });

    it("should stop refreshing when the server rejects the report request", async function () {
        await failEveryReportRequest(401);

        await page.waitForFunction('window.__mapRequestCount >= 1', { timeout: 20000 });
        await page.waitForTimeout(6000);

        expect(await page.evaluate('window.__mapRequestCount')).to.equal(1);
    });
});
