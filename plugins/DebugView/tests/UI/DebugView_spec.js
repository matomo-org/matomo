/*!
 * Matomo - free/libre analytics platform
 *
 * Screenshot integration tests for DebugView.
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

describe("DebugView", function () {
    this.fixture = "Piwik\\Plugins\\DebugView\\tests\\Fixtures\\FewDebugRequests";

    // rarely, a devtools-protocol call stalls under load until the test times
    // out although the page rendered correctly; retry instead of failing the
    // suite (each test's actions are idempotent on the already-loaded page,
    // so retrying is safe)
    this.retries(2);

    const generalParams = 'period=day&date=today&module=DebugView&action=index';

    // the fixture tracks relative to "now", so every clock-derived text must be
    // replaced with a fixed value before comparing screenshots
    async function normalizeVolatileContent() {
        // park the mouse so hover styles from earlier clicks cannot bleed into
        // a later screenshot
        await page.mouse.move(0, 0);

        await page.evaluate(function () {
            // freeze all animations so screenshots do not depend on the
            // animation frame they were taken at, and hide the pulsing live
            // dot completely: its few pixels carry no assertion value but
            // repeatedly caused tiny intermittent diffs
            if (!document.getElementById('debugViewTestFreeze')) {
                var style = document.createElement('style');
                style.id = 'debugViewTestFreeze';
                style.innerHTML = '* { animation: none !important;'
                    + ' transition: none !important; }'
                    + ' .debugViewLiveDot { visibility: hidden !important; }'
                    // gap rows depend on which fixture requests straddled a
                    // second boundary — pure timing noise; hidden via CSS so
                    // a Vue re-render cannot bring them back
                    + ' .debugViewHitGap { display: none !important; }'
                    // the macOS overlay scrollbar intermittently flashes into
                    // the capture area when a poll repaints the page
                    + ' ::-webkit-scrollbar { display: none !important; }'
                    // notifications (https hint, incompatible-plugin warnings)
                    // are environment noise, not part of what these tests pin
                    + ' #notificationContainer { display: none !important; }';
                document.head.appendChild(style);
            }

            document.querySelectorAll('.debugViewHitTime').forEach(function (element) {
                element.textContent = '12:00:00';
            });
            document.querySelectorAll('.debugViewMinuteLabel').forEach(function (element) {
                element.textContent = '12:00';
            });
            // minute buckets shift with the current second the test runs at, so
            // the rail content cannot be compared pixel-wise; hide the
            // dots/labels and pin the rail height, because the number of
            // buckets (and with it the page height) varies between runs
            document.querySelectorAll('.debugViewMinutesRail').forEach(function (element) {
                element.style.visibility = 'hidden';
                element.style.height = '1000px';
                element.style.minHeight = '1000px';
                element.style.maxHeight = '1000px';
            });

            var volatileKeys = ['cdt', 'r', '_idts', '_viewts', '_ects', 'h', 'm', 's', 'pv_id',
                'userAgent', 'serverTimeReceived', 'timestamp', 'fingerprint',
                'idpageview', 'visitServerHour'];
            var volatileValuePattern = /\d{4}-\d{2}-\d{2}|\d{1,2}:\d{2}(:\d{2})?|^\d{6,}$|20\d{2}/;

            document.querySelectorAll('.debugViewDetailRow').forEach(function (row) {
                var keyElement = row.querySelector('.debugViewDetailKey');
                var valueElement = row.querySelector('.debugViewDetailValue');
                if (!keyElement || !valueElement) {
                    return;
                }
                var key = keyElement.textContent.trim();
                var value = valueElement.textContent.trim();
                if (volatileKeys.indexOf(key) !== -1 || volatileValuePattern.test(value)) {
                    valueElement.textContent = 'removed-for-screenshot';
                    valueElement.removeAttribute('title');
                }
            });
        });
    }

    async function expectScreenshot(imageName) {
        const pageWrap = await page.$('.pageWrap');
        expect(await pageWrap.screenshot()).to.matchImage({
            imageName: imageName,
            // rounded corners and the notification link underline rasterize
            // with a few pixels of jitter between runs; tolerate that while
            // still failing on any real content change
            comparisonThreshold: 0.00003,
        });
    }

    async function waitForStream() {
        // the stream polls continuously, so a fully idle network window may
        // never occur; the selector wait below is the actual readiness gate
        await page.waitForNetworkIdle({ timeout: 20000 }).catch(function () {});
        await page.waitForSelector('.debugViewPage');
        await page.waitForTimeout(1000);
    }

    it('should show the stream of debug requests for site 1', async function () {
        await page.goto("?idSite=1&" + generalParams);
        await waitForStream();
        // the site selector lives in the page header (outside the captured
        // area); assert it rendered without a screenshot
        await page.waitForSelector('.top_bar_sites_selector');
        await page.waitForSelector('.debugViewHitRow');
        await normalizeVolatileContent();

        await expectScreenshot('site_1_stream');
    });

    it('should open the details pane with the parameters of the clicked hit', async function () {
        await page.evaluate(function () {
            var rows = Array.prototype.slice.call(document.querySelectorAll('.debugViewHitRow'));
            var row = rows.find(function (r) { return r.textContent.indexOf('First Debug Page') !== -1; });
            if (row) {
                row.click();
            }
        });
        await page.waitForSelector('.debugViewDetailsPane');
        // the sensitive token_auth value is shown translated, never the raw
        // storage sentinel
        await page.waitForFunction(function () {
            var body = document.querySelector('.debugViewDetailsBody');
            return body && body.textContent.indexOf('Redacted') !== -1
                && body.textContent.indexOf('__redacted__') === -1;
        });
        await page.waitForTimeout(1000);
        await normalizeVolatileContent();
        // position-deterministic even on retries: a failed earlier attempt
        // may have left the pane scrolled
        await page.evaluate(function () {
            document.querySelector('.debugViewDetailsBody').scrollTop = 0;
        });

        await expectScreenshot('site_1_pane_parameters');

        // the pane body scrolls; token_auth sits below the fold — pin the
        // translated "Redacted" value (and the default/other groups) with an
        // own screenshot of the pane scrolled to its end
        await page.evaluate(function () {
            var body = document.querySelector('.debugViewDetailsBody');
            body.scrollTop = body.scrollHeight;
        });
        await page.waitForTimeout(250);
        const pane = await page.$('.debugViewDetailsPane');
        expect(await pane.screenshot()).to.matchImage({
            imageName: 'site_1_pane_parameters_bottom',
            comparisonThreshold: 0.00003,
        });
    });

    it('should show the processed details and visit context in the processed tab', async function () {
        await page.click('#debugViewTabProcessed');
        // the processed action and visit context load lazily through the Live API
        await page.waitForFunction(function () {
            var body = document.querySelector('.debugViewDetailsBody');
            return body && /processed hit details/i.test(body.textContent);
        });
        await page.waitForTimeout(500);
        await normalizeVolatileContent();
        // the pane body element persists across tab switches and the previous
        // test scrolls it — capture from the top, deterministically
        await page.evaluate(function () {
            document.querySelector('.debugViewDetailsBody').scrollTop = 0;
        });

        await expectScreenshot('site_1_pane_processed');
    });

    it('should explain that a bot request has no processed details', async function () {
        await page.goto("?idSite=1&" + generalParams);
        await waitForStream();
        await page.waitForSelector('.debugViewHitRow');
        await page.evaluate(function () {
            var rows = Array.prototype.slice.call(document.querySelectorAll('.debugViewHitRow'));
            var row = rows.find(function (r) { return r.textContent.indexOf('Bot Crawl') !== -1; });
            if (row) {
                row.click();
            }
        });
        await page.waitForSelector('.debugViewDetailsPane');
        await page.click('#debugViewTabProcessed');
        await page.waitForFunction(function () {
            var body = document.querySelector('.debugViewDetailsBody');
            return body && /bot request/i.test(body.textContent);
        });
        await page.waitForTimeout(500);
        await normalizeVolatileContent();

        await expectScreenshot('site_1_pane_bot');
    });

    it('should show only the hits of site 2', async function () {
        await page.goto("?idSite=2&" + generalParams);
        await waitForStream();
        await page.waitForSelector('.debugViewHitRow');
        await normalizeVolatileContent();

        await expectScreenshot('site_2_stream');
    });

    it('should show the friendly disabled page when the visits log is disabled', async function () {
        await page.goto("?idSite=4&" + generalParams);
        await page.waitForNetworkIdle({ timeout: 20000 }).catch(function () {});
        // disabled.twig: no stream, but the site selector and the notice show
        await page.waitForSelector('.top_bar_sites_selector');
        await page.waitForFunction(function () {
            return document.body && /Visits Log/.test(document.body.textContent);
        });
        await normalizeVolatileContent();

        await expectScreenshot('site_4_visits_log_disabled');
    });

    it('should show the empty state for the site without debug requests', async function () {
        await page.goto("?idSite=3&" + generalParams);
        await waitForStream();
        await page.waitForSelector('.debugViewEmptyState');
        await normalizeVolatileContent();

        await expectScreenshot('site_3_no_data');
    });
});
