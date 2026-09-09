<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\API\Request;
use Piwik\Db\Schema\Mysql;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TrackingCodeTrait;

/**
 * Covers the tracking code returned by the SitesManager API, including the values that reach the
 * generator through the request. `SitesManager.getJavascriptTag` is annotated `@unsanitized`, so its
 * parameters are passed on unchanged and the generator is responsible for escaping them.
 *
 * @group Plugins
 * @group SitesManager
 * @group SitesManager_Integration
 * @group TrackerCodeGenerator
 */
class TrackingCodeApiTest extends IntegrationTestCase
{
    use TrackingCodeTrait;


    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess($superUser = true);

        Fixture::createWebsite('2020-01-01 00:00:00', 0, 'Site name here', 'http://example.org');
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }


    /**
     * The tracking code is written into the page unescaped, as element content and as an
     * attribute value, so it has to be escaped already and must not contain a character that is
     * active in either context.
     */


    private function getJavascriptTag(array $parameters = []): string
    {
        return Request::processRequest('SitesManager.getJavascriptTag', array_merge([
            'idSite'   => 1,
            'piwikUrl' => 'http://localhost/matomo',
        ], $parameters), []);
    }

    private function getImageTrackingCode(array $parameters = []): string
    {
        return Request::processRequest('SitesManager.getImageTrackingCode', array_merge([
            'idSite'   => 1,
            'piwikUrl' => 'localhost/matomo',
        ], $parameters), []);
    }

    public function testGetJavascriptTagReturnsTheDefaultTrackingCode()
    {
        $trackingCode = $this->getRenderedTrackingCode($this->getJavascriptTag());

        self::assertStringContainsString('var u="//localhost/matomo/";', $trackingCode);
        self::assertStringContainsString("_paq.push(['setSiteId', '1']);", $trackingCode);
        self::assertStringContainsString("_paq.push(['setTrackerUrl', u+'matomo.php']);", $trackingCode);
        $this->assertTrackingCodeIsSafeToEmbed($this->getJavascriptTag());
    }

    /**
     * @dataProvider getJavascriptTagOptions
     */
    public function testGetJavascriptTagContainsExpectedCodeForOption(
        array $parameters,
        array $expectedLines,
        bool $requiresCustomVariables = false
    ) {
        if ($requiresCustomVariables && !Manager::getInstance()->isPluginActivated('CustomVariables')) {
            self::markTestSkipped('CustomVariables plugin is not activated');
        }

        $trackingCode = $this->getRenderedTrackingCode($this->getJavascriptTag($parameters));

        foreach ($expectedLines as $expectedLine) {
            self::assertStringContainsString($expectedLine, $trackingCode);
        }
    }

    public function getJavascriptTagOptions(): iterable
    {
        yield 'mergeSubdomains' => [
            ['mergeSubdomains' => 1],
            ['_paq.push(["setCookieDomain", "*.example.org"]);'],
        ];
        yield 'mergeAliasUrls' => [
            ['mergeAliasUrls' => 1],
            ['_paq.push(["setDomains", ["*.example.org"]]);'],
        ];
        yield 'groupPageTitlesByDomain' => [
            ['groupPageTitlesByDomain' => 1],
            ['_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);'],
        ];
        yield 'crossDomain' => [
            ['crossDomain' => 1],
            ['_paq.push(["enableCrossDomainLinking"]);'],
        ];
        yield 'doNotTrack' => [
            ['doNotTrack' => 1],
            ['_paq.push(["setDoNotTrack", true]);'],
        ];
        yield 'disableCookies' => [
            ['disableCookies' => 1],
            ['_paq.push(["disableCookies"]);'],
        ];
        yield 'disableCampaignParameters' => [
            ['disableCampaignParameters' => 1],
            ['_paq.push(["disableCampaignParameters"]);'],
        ];
        yield 'customCampaignNameQueryParam' => [
            ['customCampaignNameQueryParam' => 'cmpn'],
            ['_paq.push(["setCampaignNameKey", "cmpn"]);'],
        ];
        yield 'customCampaignKeywordParam' => [
            ['customCampaignKeywordParam' => 'cmpkw'],
            ['_paq.push(["setCampaignKeywordKey", "cmpkw"]);'],
        ];
        yield 'excludedQueryParams as string' => [
            ['excludedQueryParams' => 'uid,aid'],
            ['_paq.push(["setExcludedQueryParams", ["uid","aid"]]);'],
        ];
        yield 'excludedQueryParams as array' => [
            ['excludedQueryParams' => ['uid', 'aid']],
            ['_paq.push(["setExcludedQueryParams", ["uid","aid"]]);'],
        ];
        yield 'excludedReferrers as string' => [
            ['excludedReferrers' => 'example.com,example.net'],
            ['_paq.push(["setExcludedReferrers", ["example.com","example.net"]]);'],
        ];
        yield 'excludedReferrers as array' => [
            ['excludedReferrers' => ['example.com', 'example.net']],
            ['_paq.push(["setExcludedReferrers", ["example.com","example.net"]]);'],
        ];
        yield 'trackNoScript' => [
            ['trackNoScript' => 1],
            ['<noscript><p><img referrerpolicy="no-referrer-when-downgrade" src="//localhost/matomo/matomo.php?idsite=1&amp;rec=1" style="border:0;" alt="" /></p></noscript>'],
        ];
        yield 'visitorCustomVariables' => [
            ['visitorCustomVariables' => [['name', 'value']]],
            ['_paq.push(["setCustomVariable", 1, "name", "value", "visit"]);'],
            true,
        ];
        yield 'pageCustomVariables' => [
            ['pageCustomVariables' => [['page cvar', 'page cvar value']]],
            ['_paq.push(["setCustomVariable", 1, "page cvar", "page cvar value", "page"]);'],
            true,
        ];
    }

    /**
     * Markup a plugin adds through `Tracker.getJavascriptCode` is escaped along with everything else,
     * rather than being stripped from the generated code.
     */
    public function testGetJavascriptTagEscapesMarkupAddedByAPlugin()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['options'] .= '  _paq.push(["setCustomUrl", "x"]);<br />' . "\n";
        });

        $trackingCode = $this->getJavascriptTag();

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        self::assertStringContainsString('&lt;br /&gt;', $trackingCode);
    }

    /**
     * @dataProvider getUnsafeJavascriptTagParameters
     */
    public function testGetJavascriptTagEscapesUnsafeParameterValues(array $parameters)
    {
        $this->assertTrackingCodeIsSafeToEmbed($this->getJavascriptTag($parameters));
    }

    public function getUnsafeJavascriptTagParameters(): iterable
    {
        foreach ($this->getUnsafeValueList() as $label => $value) {
            yield "piwikUrl / $label" => [['piwikUrl' => 'http://localhost/matomo/' . $value, 'trackNoScript' => 1]];
            yield "customCampaignNameQueryParam / $label" => [['customCampaignNameQueryParam' => $value]];
            yield "customCampaignKeywordParam / $label" => [['customCampaignKeywordParam' => $value]];
            yield "excludedQueryParams / $label" => [['excludedQueryParams' => $value]];
            yield "excludedReferrers / $label" => [['excludedReferrers' => $value]];
            yield "visitorCustomVariables name / $label" => [['visitorCustomVariables' => [[$value, 'cvar value']]]];
            yield "visitorCustomVariables value / $label" => [['visitorCustomVariables' => [['cvar name', $value]]]];
            yield "pageCustomVariables name / $label" => [['pageCustomVariables' => [[$value, 'cvar value']]]];
            yield "pageCustomVariables value / $label" => [['pageCustomVariables' => [['cvar name', $value]]]];
        }
    }

    /**
     * Parameters of the current request are merged into the API call, so they have to be escaped too.
     *
     * @dataProvider getUnsafeValues
     */
    public function testGetJavascriptTagEscapesUnsafeValuesComingFromTheCurrentRequest(string $value)
    {
        $_GET['customCampaignNameQueryParam'] = $value;

        try {
            $trackingCode = Request::processRequest('SitesManager.getJavascriptTag', [
                'idSite'   => 1,
                'piwikUrl' => 'localhost/matomo',
            ]);

            $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        } finally {
            unset($_GET['customCampaignNameQueryParam']);
        }
    }

    /**
     * Site URLs are stored sanitized, so they reach the generator HTML encoded already and must not end
     * up in the generated code unescaped.
     *
     * @dataProvider getUnsafeValues
     */
    public function testGetJavascriptTagEscapesUnsafeSiteUrls(string $value)
    {
        API::getInstance()->setSiteAliasUrls(1, ['http://example.com/' . $value]);

        $this->assertTrackingCodeIsSafeToEmbed($this->getJavascriptTag([
            'mergeSubdomains' => 1,
            'mergeAliasUrls'  => 1,
        ]));
    }

    public function testGetImageTrackingCodeRequiresViewAccess()
    {
        FakeAccess::clearAccess($superUser = false, $idSitesAdmin = [], $idSitesView = [2], $identity = 'someUser');

        try {
            $this->expectExceptionMessage('checkUserHasViewAccess Fake exception');

            $this->getImageTrackingCode();
        } finally {
            FakeAccess::clearAccess($superUser = true);
        }
    }

    public function testGetImageTrackingCodeIsReturnedWithViewAccess()
    {
        FakeAccess::clearAccess($superUser = false, $idSitesAdmin = [], $idSitesView = [1], $identity = 'someUser');

        try {
            self::assertStringContainsString('matomo.php', $this->getImageTrackingCode());
        } finally {
            FakeAccess::clearAccess($superUser = true);
        }
    }

    public function testGetImageTrackingCodeReturnsTheDefaultTrackingCode()
    {
        $trackingCode = $this->getRenderedTrackingCode($this->getImageTrackingCode());

        self::assertStringContainsString('<img referrerpolicy="no-referrer-when-downgrade" src="http://localhost/matomo/matomo.php?idsite=1&amp;rec=1" style="border:0" alt="" />', $trackingCode);
    }

    /**
     * @dataProvider getImageTrackingCodeOptions
     */
    public function testGetImageTrackingCodeContainsExpectedUrl(array $parameters, string $expectedQueryString)
    {
        $trackingCode = $this->getRenderedTrackingCode($this->getImageTrackingCode($parameters));

        self::assertStringContainsString('src="http://localhost/matomo/matomo.php?' . $expectedQueryString . '"', $trackingCode);
    }

    public function getImageTrackingCodeOptions(): iterable
    {
        yield 'default' => [[], 'idsite=1&amp;rec=1'];
        yield 'actionName' => [['actionName' => 'My Action'], 'idsite=1&amp;rec=1&amp;action_name=My+Action'];
        yield 'idGoal' => [['idGoal' => 2], 'idsite=1&amp;rec=1&amp;idgoal=2'];
        yield 'idGoal with revenue' => [['idGoal' => 2, 'revenue' => 15], 'idsite=1&amp;rec=1&amp;idgoal=2&amp;revenue=15'];
        yield 'revenue without goal is ignored' => [['revenue' => 15], 'idsite=1&amp;rec=1'];
        yield 'piwikUrl with trailing slash' => [['piwikUrl' => 'localhost/matomo/'], 'idsite=1&amp;rec=1'];
    }

    /**
     * `SettingsPiwik::getPiwikUrl()`, which is used when no `piwikUrl` is given, returns a URL including
     * the protocol, so the protocol must not be prepended a second time.
     *
     * @dataProvider getImageTrackingCodePiwikUrls
     */
    public function testGetImageTrackingCodeDoesNotPrependTheProtocolTwice(string $piwikUrl)
    {
        $trackingCode = $this->getRenderedTrackingCode($this->getImageTrackingCode(['piwikUrl' => $piwikUrl]));

        self::assertStringContainsString('src="http://localhost/matomo/matomo.php?idsite=1&amp;rec=1"', $trackingCode);
    }

    public function getImageTrackingCodePiwikUrls(): iterable
    {
        yield 'without protocol' => ['localhost/matomo'];
        yield 'with http protocol' => ['http://localhost/matomo'];
        yield 'with trailing slash' => ['localhost/matomo/'];
    }

    /**
     * @dataProvider getForceMatomoEndpointValues
     */
    public function testGetImageTrackingCodeReadsForceMatomoEndpointAsABoolean($value, string $expectedEndpoint)
    {
        Option::set(Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION, '3.6.0');

        $trackingCode = $this->getRenderedTrackingCode($this->getImageTrackingCode(['forceMatomoEndpoint' => $value]));

        self::assertStringContainsString('/' . $expectedEndpoint . '?', $trackingCode);
    }

    public function getForceMatomoEndpointValues(): iterable
    {
        yield 'one' => [1, 'matomo.php'];
        yield 'true' => ['true', 'matomo.php'];
        yield 'zero' => [0, 'piwik.php'];
        yield 'false' => ['false', 'piwik.php'];
        // anything else falls back to the default rather than being rejected
        yield 'unsupported value' => ['yes', 'piwik.php'];
    }

    /**
     * @dataProvider getUnsafeImageTrackingCodeParameters
     */
    public function testGetImageTrackingCodeEscapesUnsafeParameterValues(array $parameters)
    {
        $this->assertTrackingCodeIsSafeToEmbed($this->getImageTrackingCode($parameters));
    }

    public function getUnsafeImageTrackingCodeParameters(): iterable
    {
        foreach ($this->getUnsafeValueList() as $label => $value) {
            yield "piwikUrl / $label" => [['piwikUrl' => 'http://localhost/matomo/' . $value]];
            yield "actionName / $label" => [['actionName' => $value]];
            yield "idGoal / $label" => [['idGoal' => $value]];
            yield "revenue / $label" => [['idGoal' => 1, 'revenue' => $value]];
        }
    }

    /**
     * An action name that is not a string used to reach a decode that raises a TypeError, which is not
     * an exception the API turns into an error response.
     *
     * @dataProvider getNonStringActionNames
     */
    public function testGetImageTrackingCodeIgnoresANonStringActionName($actionName)
    {
        $trackingCode = $this->getImageTrackingCode(['actionName' => $actionName]);

        $this->assertImageTrackingUrlIsWellFormed($trackingCode);
        self::assertStringNotContainsString('action_name', $this->getRenderedTrackingCode($trackingCode));
    }

    public function getNonStringActionNames(): iterable
    {
        yield 'array' => [['x']];
        yield 'nested array' => [[['x']]];
        yield 'empty string' => [''];
    }

    public function testGetImageTrackingCodeKeepsAZeroActionName()
    {
        $trackingCode = $this->getImageTrackingCode(['actionName' => '0']);

        self::assertStringContainsString('action_name=0', $this->getRenderedTrackingCode($trackingCode));
    }

    /**
     * @dataProvider getTrackingCodeMethodsWithHostlessUrls
     */
    public function testTrackingCodeIsRefusedWhenNoMatomoUrlCanBeResolved(string $method, string $piwikUrl)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SitesManager_ExceptionMatomoUrlUnknown');

        Request::processRequest($method, ['idSite' => 1, 'piwikUrl' => $piwikUrl], []);
    }

    public function getTrackingCodeMethodsWithHostlessUrls(): iterable
    {
        foreach (['SitesManager.getJavascriptTag', 'SitesManager.getImageTrackingCode'] as $method) {
            yield "$method / protocol only" => [$method, 'http://'];
            yield "$method / slashes only" => [$method, '///'];
        }
    }

    /**
     * The API sanitizes its parameters, so a raw quote only reaches the query string builder when the
     * method is called directly or when a plugin adds a parameter through the event.
     *
     * @dataProvider getRawImageTrackingCodeParameters
     */
    public function testGetImageTrackingCodeEncodesParametersPassedDirectly($actionName, $idGoal, $revenue)
    {
        $trackingCode = API::getInstance()->getImageTrackingCode(1, 'localhost/matomo', $actionName, $idGoal, $revenue);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        $this->assertImageTrackingUrlIsWellFormed($trackingCode);
    }

    public function getRawImageTrackingCodeParameters(): iterable
    {
        yield 'quote in action name' => ['a"b', false, false];
        yield 'attribute breakout in action name' => ['a" onerror="alert(1)', false, false];
        yield 'quote in goal' => [false, 'a"b', false];
        yield 'quote in revenue' => [false, 1, 'a"b'];
        yield 'tag in action name' => ['a"><script>alert(1)</script>', false, false];
        yield 'array goal' => [false, [1, 2], false];
        yield 'space and ampersand' => ['a b&c=d', false, false];
    }

    /**
     * A plugin can add any parameter to the tracking URL through the event.
     */
    public function testGetImageTrackingCodeEncodesParametersAddedByAPlugin()
    {
        Piwik::addAction('SitesManager.getImageTrackingCode', function (&$piwikUrl, &$urlParams) {
            $urlParams['uid'] = 'a" onerror="alert(1)';
            $urlParams['dimension'] = ['a"b', 'c d'];
            $urlParams['skipped'] = null;
            $urlParams['alsoSkipped'] = false;
        });

        $trackingCode = $this->getImageTrackingCode();
        $renderedCode = $this->getRenderedTrackingCode($trackingCode);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        $this->assertImageTrackingUrlIsWellFormed($trackingCode);

        self::assertStringContainsString('uid=a%22+onerror%3D%22alert%281%29', $renderedCode);
        self::assertStringContainsString('dimension[]=a%22b&amp;dimension[]=c+d', $renderedCode);
        self::assertStringNotContainsString('skipped', $renderedCode);
    }

    private function assertImageTrackingUrlIsWellFormed(string $trackingCode): void
    {
        $renderedCode = $this->getRenderedTrackingCode($trackingCode);

        self::assertSame(
            1,
            preg_match('~^<img ([^>]*)/>$~m', $renderedCode, $matches),
            sprintf('the image tag is malformed: %s', $renderedCode)
        );

        self::assertSame(
            1,
            preg_match('~ src="([^"]*)" ~', $matches[1], $source),
            sprintf('the source attribute is malformed: %s', $matches[1])
        );

        $url = html_entity_decode($source[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        self::assertSame(
            1,
            preg_match('~^https?://[^\s"\'<>]+$~', $url),
            sprintf('"%s" is not a well formed URL', $url)
        );
    }

    /**
     * @dataProvider getUnsafeImageTrackingCodeParameters
     */
    public function testGetImageTrackingCodeKeepsTheUrlInsideTheSourceAttribute(array $parameters)
    {
        $this->assertImageTrackingUrlIsWellFormed($this->getImageTrackingCode($parameters));
    }

    /**
     * @dataProvider getImageTrackingCodeParametersWithSmuggledParameters
     */
    public function testGetImageTrackingCodeDoesNotAllowSmugglingAdditionalUrlParameters(array $parameters)
    {
        $trackingCode = $this->getRenderedTrackingCode($this->getImageTrackingCode($parameters));

        self::assertStringNotContainsString('&amp;idsite=999', $trackingCode);
    }

    public function getImageTrackingCodeParametersWithSmuggledParameters(): iterable
    {
        yield 'actionName' => [['actionName' => 'name&idsite=999']];
        yield 'idGoal' => [['idGoal' => '1&idsite=999']];
        yield 'revenue' => [['idGoal' => 1, 'revenue' => '1&idsite=999']];
    }

    /**
     * @dataProvider getInvalidSiteIds
     */
    public function testGetImageTrackingCodeRejectsANonIntegerSiteId(string $idSite)
    {
        $this->expectExceptionMessage('General_PleaseSpecifyValue');

        $this->getImageTrackingCode(['idSite' => $idSite]);
    }

    public function getInvalidSiteIds(): iterable
    {
        yield 'smuggled url parameter' => ['1&idsite=999'];
        yield 'not a number' => ['notASiteId'];
    }
}
