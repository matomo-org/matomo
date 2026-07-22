<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
?>
<script type="text/javascript">
module('Overlay/Piwik_Overlay');

var overlayContext = function () {
    return { idSite: 1, period: 'day', date: 'today', segment: null };
};

test("buildApiRequestParams builds the request from a fixed set of trusted parameters", function () {
    // idSite, format and filter_limit provided in the request must be ignored.
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?module=API&action=index&idSite=99&method=Overlay.getTranslations&format=xml&filter_limit=5',
        overlayContext()
    );

    ok(!result.error, 'a request for an allow-listed method is accepted');
    strictEqual(result.params.method, 'Overlay.getTranslations', 'method is the allow-listed value');
    strictEqual(result.params.idSite, 1, 'idSite comes from the trusted context');
    strictEqual(result.params.period, 'day', 'period comes from the trusted context');
    strictEqual(result.params.date, 'today', 'date comes from the trusted context');
    strictEqual(result.params.format, 'JSON', 'format is fixed to JSON');
    strictEqual(result.params.filter_limit, -1, 'filter_limit is fixed');
    strictEqual(result.params.module, 'API', 'module is fixed to API');
    strictEqual(result.params.action, 'index', 'action is fixed to index');
});

test("buildApiRequestParams rejects a method that is not allow-listed", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Live.getLastVisitsDetails',
        overlayContext()
    );

    ok(result.error, 'a method that is not allow-listed is rejected');
    strictEqual(typeof result.params, 'undefined', 'no params are produced for a rejected request');
});

test("buildApiRequestParams rejects parameter names with surrounding whitespace", function () {
    // Names with leading/trailing whitespace, or its percent-encoding, are ambiguous.
    var literalSpace = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations& method=Live.getLastVisitsDetails',
        overlayContext()
    );
    ok(literalSpace.error, 'a name with a leading space is rejected');

    var encodedSpace = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&%20method=Live.getLastVisitsDetails',
        overlayContext()
    );
    ok(encodedSpace.error, 'a name with a percent-encoded leading space is rejected');
});

test("buildApiRequestParams rejects duplicate parameter names", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&method=Overlay.getFollowingPages',
        overlayContext()
    );

    ok(result.error, 'a duplicate parameter name is rejected');
});

test("buildApiRequestParams always takes idSite from the trusted context", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getFollowingPages&idSite=2',
        overlayContext()
    );

    ok(!result.error, 'the request is accepted');
    strictEqual(result.params.idSite, 1, 'idSite comes from the trusted context, not the request');
});

test("buildApiRequestParams forwards the url parameter for getFollowingPages", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getFollowingPages&url=' + encodeURIComponent('https://example.org/page'),
        overlayContext()
    );

    ok(!result.error, 'getFollowingPages with a url is accepted');
    strictEqual(result.params.method, 'Overlay.getFollowingPages', 'method is preserved');
    strictEqual(result.params.url, 'https://example.org/page', 'url is decoded and forwarded');
});

test("buildApiRequestParams takes segment from the trusted context", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&segment=' + encodeURIComponent('countryCode==fr'),
        { idSite: 1, period: 'day', date: 'today', segment: 'browserCode==ff' }
    );

    ok(!result.error, 'the request is accepted');
    strictEqual(result.params.segment, 'browserCode==ff', 'segment comes from the trusted context');
});
</script>
