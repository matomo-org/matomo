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

test("buildApiRequestParams forwards url only to getFollowingPages", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&url=' + encodeURIComponent('https://example.org/page'),
        overlayContext()
    );

    ok(!result.error, 'the request is accepted');
    strictEqual(typeof result.params.url, 'undefined', 'url is not forwarded to other allow-listed methods');
});

test("buildApiRequestParams takes segment from the trusted context", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&segment=' + encodeURIComponent('countryCode==fr'),
        { idSite: 1, period: 'day', date: 'today', segment: 'browserCode==ff' }
    );

    ok(!result.error, 'the request is accepted');
    strictEqual(result.params.segment, 'browserCode==ff', 'segment comes from the trusted context');
});

test("buildApiRequestParams rejects all whitespace variants in a parameter name", function () {
    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&\tmethod=Live.getLastVisitsDetails',
        overlayContext()
    ).error, 'a literal tab in a name is rejected');

    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&\nmethod=Live.getLastVisitsDetails',
        overlayContext()
    ).error, 'a literal newline in a name is rejected');

    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&%09method=Live.getLastVisitsDetails',
        overlayContext()
    ).error, 'a percent-encoded tab in a name is rejected');

    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?method =Overlay.getTranslations',
        overlayContext()
    ).error, 'trailing whitespace in a name is rejected');
});

test("buildApiRequestParams decodes parameter names before comparing them", function () {
    // "%6D%65%74%68%6F%64" is "method" fully percent-encoded.
    var encoded = Piwik_Overlay.buildApiRequestParams(
        'index.php?%6D%65%74%68%6F%64=Overlay.getTranslations',
        overlayContext()
    );
    ok(!encoded.error, 'a fully percent-encoded name is decoded and accepted');
    strictEqual(encoded.params.method, 'Overlay.getTranslations', 'the decoded name maps to method');

    var collision = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&%6D%65%74%68%6F%64=Live.getLastVisitsDetails',
        overlayContext()
    );
    ok(collision.error, 'a percent-encoded name that decodes to an existing name is a duplicate');
});

test("buildApiRequestParams reports malformed percent-encoding", function () {
    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getTranslations&extra=%ZZ',
        overlayContext()
    ).error, 'malformed encoding in a value is reported');

    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?%ZZmethod=Overlay.getTranslations',
        overlayContext()
    ).error, 'malformed encoding in a name is reported');
});

test("buildApiRequestParams requires a method", function () {
    var missing = Piwik_Overlay.buildApiRequestParams('index.php?idSite=1', overlayContext());
    ok(missing.error, 'a request with no method is rejected');
    strictEqual(typeof missing.params, 'undefined', 'no params are produced');

    ok(Piwik_Overlay.buildApiRequestParams('index.php?method=', overlayContext()).error,
        'an empty method is rejected');
});

test("buildApiRequestParams is not affected by object-property names", function () {
    var proto = Piwik_Overlay.buildApiRequestParams(
        'index.php?__proto__=Live.getLastVisitsDetails&method=Overlay.getTranslations',
        overlayContext()
    );
    ok(!proto.error, '__proto__ as a parameter name does not break parsing');
    strictEqual(proto.params.method, 'Overlay.getTranslations', 'method is still the allow-listed value');
    strictEqual(({}).polluted, undefined, 'the object prototype is not polluted');

    var ctor = Piwik_Overlay.buildApiRequestParams(
        'index.php?constructor=Live.getLastVisitsDetails&method=Overlay.getTranslations',
        overlayContext()
    );
    ok(!ctor.error, 'constructor as a parameter name does not break parsing');
    strictEqual(ctor.params.method, 'Overlay.getTranslations', 'method is still the allow-listed value');

    // A parameter named hasOwnProperty must not disturb the duplicate-name check.
    ok(Piwik_Overlay.buildApiRequestParams(
        'index.php?hasOwnProperty=x&method=Overlay.getTranslations&method=Overlay.getFollowingPages',
        overlayContext()
    ).error, 'the duplicate-name check still fires when hasOwnProperty is a parameter name');
});

test("buildApiRequestParams does not forward report-shaping parameters", function () {
    var result = Piwik_Overlay.buildApiRequestParams(
        'index.php?method=Overlay.getFollowingPages&url=' + encodeURIComponent('https://example.org/page')
            + '&filter_pattern=.*&filter_column=label&flat=1',
        overlayContext()
    );

    ok(!result.error, 'the request is accepted');
    strictEqual(typeof result.params.filter_pattern, 'undefined', 'filter_pattern is not forwarded');
    strictEqual(typeof result.params.filter_column, 'undefined', 'filter_column is not forwarded');
    strictEqual(typeof result.params.flat, 'undefined', 'flat is not forwarded');
});
</script>
