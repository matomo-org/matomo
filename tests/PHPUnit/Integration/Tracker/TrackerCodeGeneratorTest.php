<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db\Schema\Mysql;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Tests\Framework\Mock\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TrackingCodeTrait;
use Piwik\Tracker\TrackerCodeGenerator;

/**
 * @group Core
 * @group TrackerCodeGenerator
 */
class TrackerCodeGeneratorTest extends IntegrationTestCase
{
    use TrackingCodeTrait;


    public function testJavascriptTrackingCodeWithAllOptions()
    {
        $generator = new TrackerCodeGenerator();

        $urls = array(
            'http://localhost/piwik',
            'http://another-domain/piwik',
            'https://another-domain/piwik',
        );
        $idSite = \Piwik\Plugins\SitesManager\API::getInstance()->addSite('Site name here <-->', $urls);
        $jsTag = $generator->generate(
            $idSite,
            'http://piwik-server/piwik',
            $mergeSubdomains = true,
            $groupPageTitlesByDomain = true,
            $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey",
            $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true,
            $disableCookies = false,
            $trackNoScript = true,
            $crossDomain = true,
            $excludedQueryParams = array("uid", "aid"),
            $excludedReferrers = array(),
            $disableCampaignParameters = true
        );

        $expected = "&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([&quot;setDocumentTitle&quot;, document.domain + &quot;/&quot; + document.title]);
  _paq.push([&quot;setCookieDomain&quot;, &quot;*.localhost&quot;]);
  _paq.push([&quot;setDomains&quot;, [&quot;*.localhost/piwik&quot;,&quot;*.another-domain/piwik&quot;,&quot;*.another-domain/piwik&quot;]]);
  _paq.push([&quot;enableCrossDomainLinking&quot;]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;name&quot;, &quot;value&quot;, &quot;visit&quot;]);
  _paq.push([&quot;setCustomVariable&quot;, 2, &quot;name 2&quot;, &quot;value 2&quot;, &quot;visit&quot;]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;page cvar&quot;, &quot;page cvar value&quot;, &quot;page&quot;]);" : "") . "
  _paq.push([&quot;disableCampaignParameters&quot;]);
  _paq.push([&quot;setCampaignNameKey&quot;, &quot;campaignKey&quot;]);
  _paq.push([&quot;setCampaignKeywordKey&quot;, &quot;keywordKey&quot;]);
  _paq.push([&quot;setDoNotTrack&quot;, true]);
  _paq.push([&quot;setExcludedQueryParams&quot;, [&quot;uid&quot;,&quot;aid&quot;]]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//piwik-server/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;noscript&gt;&lt;p&gt;&lt;img referrerpolicy=&quot;no-referrer-when-downgrade&quot; src=&quot;//piwik-server/piwik/matomo.php?idsite=1&amp;amp;rec=1&quot; style=&quot;border:0;&quot; alt=&quot;&quot; /&gt;&lt;/p&gt;&lt;/noscript&gt;
&lt;!-- End Matomo Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    public function testJavascriptTrackingCodeNoScriptTrackingDisabledDefaultTrackingCode()
    {
        $generator = new TrackerCodeGenerator();

        $jsTag = $generator->generate($idSite = 1, $piwikUrl = 'http://localhost/piwik');

        $expected = "&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//localhost/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    /**
     * Tests the generated JS code with protocol override
     */
    public function testJavascriptTrackingCodeWithAllOptionsAndProtocolOverwrite()
    {
        $generator = new TrackerCodeGenerator();

        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['protocol'] = 'https://';
        });

        $jsTag = $generator->generate(
            $idSite = 1,
            $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true,
            $groupPageTitlesByDomain = true,
            $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey",
            $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true,
            $disableCookies = false,
            $trackNoScript = false,
            $crossDomain = false,
            $excludedQueryParams = array("uid", "aid"),
            $excludedReferrers = array(),
            $disableCampaignParameters = true
        );

        $expected = "&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([&quot;setDocumentTitle&quot;, document.domain + &quot;/&quot; + document.title]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;name&quot;, &quot;value&quot;, &quot;visit&quot;]);
  _paq.push([&quot;setCustomVariable&quot;, 2, &quot;name 2&quot;, &quot;value 2&quot;, &quot;visit&quot;]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;page cvar&quot;, &quot;page cvar value&quot;, &quot;page&quot;]);" : "") . "
  _paq.push([&quot;disableCampaignParameters&quot;]);
  _paq.push([&quot;setCampaignNameKey&quot;, &quot;campaignKey&quot;]);
  _paq.push([&quot;setCampaignKeywordKey&quot;, &quot;keywordKey&quot;]);
  _paq.push([&quot;setDoNotTrack&quot;, true]);
  _paq.push([&quot;setExcludedQueryParams&quot;, [&quot;uid&quot;,&quot;aid&quot;]]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;https://localhost/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    /**
     * Tests the generated JS code with options before tracker url
     */
    public function testJavascriptTrackingCodeWithAllOptionsAndOptionsBeforeTrackerUrl()
    {
        $generator = new TrackerCodeGenerator();

        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['optionsBeforeTrackerUrl'] .= "_paq.push(['setAPIUrl', 'http://localhost/statistics']);\n    ";
        });

        $jsTag = $generator->generate(
            $idSite = 1,
            $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true,
            $groupPageTitlesByDomain = true,
            $mergeAliasUrls = true,
            $visitorCustomVariables = array(array("name", "value"), array("name 2", "value 2")),
            $pageCustomVariables = array(array("page cvar", "page cvar value")),
            $customCampaignNameQueryParam = "campaignKey",
            $customCampaignKeywordParam = "keywordKey",
            $doNotTrack = true,
            $disableCookies = false,
            $trackNoScript = false,
            $crossDomain = false,
            $excludedQueryParams = array("uid", "aid"),
            $excludedReferrers = array(),
            $disableCampaignParameters = true
        );

        $expected = "&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([&quot;setDocumentTitle&quot;, document.domain + &quot;/&quot; + document.title]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;name&quot;, &quot;value&quot;, &quot;visit&quot;]);
  _paq.push([&quot;setCustomVariable&quot;, 2, &quot;name 2&quot;, &quot;value 2&quot;, &quot;visit&quot;]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;page cvar&quot;, &quot;page cvar value&quot;, &quot;page&quot;]);" : "") . "
  _paq.push([&quot;disableCampaignParameters&quot;]);
  _paq.push([&quot;setCampaignNameKey&quot;, &quot;campaignKey&quot;]);
  _paq.push([&quot;setCampaignKeywordKey&quot;, &quot;keywordKey&quot;]);
  _paq.push([&quot;setDoNotTrack&quot;, true]);
  _paq.push([&quot;setExcludedQueryParams&quot;, [&quot;uid&quot;,&quot;aid&quot;]]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//localhost/piwik/&quot;;
    _paq.push(['setAPIUrl', 'http://localhost/statistics']);
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    /**
     * Tests the generated JS code with options before tracker url
     */
    public function testJavascriptTrackingCodeLoadSync()
    {
        $generator = new TrackerCodeGenerator();

        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['loadAsync'] = false;
        });

        $jsTag = $generator->generate(
            $idSite = 1,
            $piwikUrl = 'http://localhost/piwik',
            $mergeSubdomains = true,
            $groupPageTitlesByDomain = true,
            $mergeAliasUrls = true
        );

        $expected = "&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([&quot;setDocumentTitle&quot;, document.domain + &quot;/&quot; + document.title]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=&quot;//localhost/piwik/&quot;;
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '1']);
    
  })();
&lt;/script&gt;
&lt;script src=&quot;//localhost/piwik/matomo.js&quot;&gt;&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
";

        $this->assertEquals($expected, $jsTag);
    }

    public function testStringsAreEscaped()
    {
        $generator = new TrackerCodeGenerator();

        $jsTag = $generator->generate(
            $idSite = 1,
            $piwikUrl = 'abc"def',
            $mergeSubdomains = true,
            $groupPageTitlesByDomain = true,
            $mergeAliasUrls = true,
            $visitorCustomVariables = array(array('abc"def', 'abc"def')),
            $pageCustomVariables = array(array('abc"def', 'abc"def')),
            $customCampaignNameQueryParam = 'abc"def',
            $customCampaignKeywordParam = 'abc"def',
            $doNotTrack = false,
            $disableCookies = false,
            $trackNoScript = false,
            $crossDomain = false,
            $excludedQueryParams = array('u"id', 'a"id')
        );

        $expected = '&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([&quot;setDocumentTitle&quot;, document.domain + &quot;/&quot; + document.title]);' . ($this->hasCustomVariables() ? '
  // you can set up to 5 custom variables for each visitor
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;abc\\&quot;def&quot;, &quot;abc\\&quot;def&quot;, &quot;visit&quot;]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([&quot;setCustomVariable&quot;, 1, &quot;abc\\&quot;def&quot;, &quot;abc\\&quot;def&quot;, &quot;page&quot;]);' : '') . '
  _paq.push([&quot;setCampaignNameKey&quot;, &quot;abc\\&quot;def&quot;]);
  _paq.push([&quot;setCampaignKeywordKey&quot;, &quot;abc\\&quot;def&quot;]);
  _paq.push([&quot;setExcludedQueryParams&quot;, [&quot;u\\&quot;id&quot;,&quot;a\\&quot;id&quot;]]);
  _paq.push([\'trackPageView\']);
  _paq.push([\'enableLinkTracking\']);
  (function() {
    var u=&quot;//abc%22def/&quot;;
    _paq.push([\'setTrackerUrl\', u+\'matomo.php\']);
    _paq.push([\'setSiteId\', \'1\']);
    var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
    g.async=true; g.src=u+\'matomo.js\'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
';

        $this->assertEquals($expected, $jsTag);
    }

    public function testJavascriptTrackingCodeWithForceSsl()
    {
        Config::getInstance()->General['force_ssl'] = 1;

        $generator = new TrackerCodeGenerator();
        $jsTag = $generator->generate($idSite = 1, $piwikUrl = 'http://localhost/piwik');

        $expected = '&lt;!-- Matomo --&gt;
&lt;script&gt;
  var _paq = window._paq = window._paq || [];
  /* tracker methods like &quot;setCustomDimension&quot; should be called before &quot;trackPageView&quot; */
  _paq.push([\'trackPageView\']);
  _paq.push([\'enableLinkTracking\']);
  (function() {
    var u=&quot;https://localhost/piwik/&quot;;
    _paq.push([\'setTrackerUrl\', u+\'matomo.php\']);
    _paq.push([\'setSiteId\', \'1\']);
    var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0];
    g.async=true; g.src=u+\'matomo.js\'; s.parentNode.insertBefore(g,s);
  })();
&lt;/script&gt;
&lt;!-- End Matomo Code --&gt;
';

        $this->assertEquals($expected, $jsTag);
    }


    private function generateTrackingCode(array $overrides = []): string
    {
        $parameters = array_merge([
            'idSite'                       => 1,
            'piwikUrl'                     => 'http://localhost/piwik',
            'mergeSubdomains'              => false,
            'groupPageTitlesByDomain'      => false,
            'mergeAliasUrls'               => false,
            'visitorCustomVariables'       => null,
            'pageCustomVariables'          => null,
            'customCampaignNameQueryParam' => null,
            'customCampaignKeywordParam'   => null,
            'doNotTrack'                   => false,
            'disableCookies'               => false,
            'trackNoScript'                => false,
            'crossDomain'                  => false,
            'excludedQueryParams'          => false,
            'excludedReferrers'            => [],
            'disableCampaignParameters'    => false,
        ], $overrides);

        $generator = new TrackerCodeGenerator();

        return $generator->generate(...array_values($parameters));
    }

    /**
     * Returns the JavaScript a user would copy out of the UI, ie. the generated code with the HTML
     * escaping removed again. Assertions about the generated JavaScript are made against this, so that
     * they stay independent of how the code is escaped for the HTML page it is embedded in.
     */

    /**
     * Asserts that every tracker command of the copied code is still a single command, and that the given
     * value survived as one string rather than being split up by the JavaScript it may contain.
     */
    private function assertTrackerCommandsContainValue(
        string $trackingCode,
        string $expectedValue,
        int $expectedOccurrences,
        string $context = ''
    ): void {
        $found = 0;

        foreach (explode("\n", $this->getRenderedTrackingCode($trackingCode)) as $line) {
            $line = trim($line);

            // the only command that is not a JSON literal
            if (false !== strpos($line, 'document.')) {
                continue;
            }

            if (!preg_match('~^_paq\.push\((\[".*\])\);$~', $line, $matches)) {
                continue;
            }

            $command = json_decode($matches[1], true);

            self::assertIsArray($command, sprintf('%s is not a single tracker command', $line));

            foreach ($this->getNestedStrings($command) as $string) {
                if ($string === $expectedValue) {
                    $found++;
                }
            }
        }

        self::assertSame(
            $expectedOccurrences,
            $found,
            sprintf('"%s" did not survive as a single string in %s', $expectedValue, $context ?: 'the code')
        );
    }

    private function getNestedStrings(array $values): iterable
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                yield from $this->getNestedStrings($value);
            } elseif (is_string($value)) {
                yield $value;
            }
        }
    }

    private function assertTrackerUrlIsASingleString(string $trackingCode): void
    {
        $assignments = 0;

        foreach (explode("\n", $this->getRenderedTrackingCode($trackingCode)) as $line) {
            if (!preg_match('~var u=(".*);~', trim($line), $matches)) {
                continue;
            }

            $assignments++;

            self::assertIsString(
                json_decode($matches[1]),
                sprintf('%s is not a single JavaScript string', $matches[1])
            );
        }

        self::assertSame(1, $assignments, 'the tracker URL assignment is missing');
    }

    /**
     * A value must not be able to end the script element the tracking code lives in, which the JSON
     * decoding of the tracker commands cannot detect on its own.
     */
    private function assertScriptElementIsIntact(string $trackingCode): void
    {
        $renderedCode = strtolower($this->getRenderedTrackingCode($trackingCode));

        self::assertSame(
            1,
            substr_count($renderedCode, '<script'),
            'the generated tracking code opens more than the one script element it should'
        );

        self::assertSame(
            1,
            substr_count($renderedCode, '</script'),
            'the generated tracking code closes more than the one script element it should'
        );
    }


    public function testJavascriptTrackingCodeUsesTheDefaultEndpointsAndSiteId()
    {
        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode(['idSite' => 42]));

        self::assertStringContainsString("_paq.push(['setSiteId', '42']);", $trackingCode);
        self::assertStringContainsString("_paq.push(['setTrackerUrl', u+'matomo.php']);", $trackingCode);
        self::assertStringContainsString("g.src=u+'matomo.js';", $trackingCode);
    }

    /**
     * @dataProvider getPiwikUrlsToNormalise
     */
    public function testJavascriptTrackingCodeNormalisesThePiwikUrl(string $piwikUrl, string $expectedTrackerUrl)
    {
        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode(['piwikUrl' => $piwikUrl]));

        self::assertStringContainsString('var u="' . $expectedTrackerUrl . '";', $trackingCode);
    }

    public function getPiwikUrlsToNormalise(): iterable
    {
        yield 'with http protocol' => ['http://localhost/piwik', '//localhost/piwik/'];
        yield 'with https protocol' => ['https://localhost/piwik', '//localhost/piwik/'];
        yield 'without protocol' => ['localhost/piwik', '//localhost/piwik/'];
        yield 'with trailing slash' => ['http://localhost/piwik/', '//localhost/piwik/'];
        yield 'with multiple trailing slashes' => ['http://localhost/piwik///', '//localhost/piwik/'];
        yield 'host only' => ['http://localhost', '//localhost/'];
        yield 'with port' => ['http://localhost:8080/piwik', '//localhost:8080/piwik/'];
        yield 'with subdirectory' => ['http://localhost/some/path/piwik', '//localhost/some/path/piwik/'];
    }

    /**
     * Covers every single option of the generator on its own, so that each of them is verified
     * independently of the combined "all options" test.
     *
     * @dataProvider getSingleOptions
     */
    public function testJavascriptTrackingCodeContainsExpectedCodeForSingleOption(
        array $overrides,
        array $expectedLines,
        bool $requiresCustomVariables = false
    ) {
        if ($requiresCustomVariables && !$this->hasCustomVariables()) {
            self::markTestSkipped('CustomVariables plugin is not activated');
        }

        if (!empty($overrides['createSiteWithUrls'])) {
            $overrides['idSite'] = \Piwik\Plugins\SitesManager\API::getInstance()->addSite(
                'Site name here',
                $overrides['createSiteWithUrls']
            );
            unset($overrides['createSiteWithUrls']);
        }

        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode($overrides));

        foreach ($expectedLines as $expectedLine) {
            self::assertStringContainsString($expectedLine, $trackingCode);
        }
    }

    public function getSingleOptions(): iterable
    {
        $urls = ['http://example.org', 'http://example.com/path', 'https://sub.example.net'];

        yield 'mergeSubdomains' => [
            ['createSiteWithUrls' => $urls, 'mergeSubdomains' => true],
            ['_paq.push(["setCookieDomain", "*.example.org"]);'],
        ];

        yield 'mergeAliasUrls' => [
            ['createSiteWithUrls' => $urls, 'mergeAliasUrls' => true],
            ['_paq.push(["setDomains", ["*.example.org","*.example.com/path","*.sub.example.net"]]);'],
        ];

        yield 'mergeSubdomains and mergeAliasUrls' => [
            ['createSiteWithUrls' => $urls, 'mergeSubdomains' => true, 'mergeAliasUrls' => true],
            [
                '_paq.push(["setCookieDomain", "*.example.org"]);',
                '_paq.push(["setDomains", ["*.example.org","*.example.com/path","*.sub.example.net"]]);',
            ],
        ];

        yield 'crossDomain implies setDomains' => [
            ['createSiteWithUrls' => $urls, 'crossDomain' => true],
            [
                '_paq.push(["setDomains", ["*.example.org","*.example.com/path","*.sub.example.net"]]);',
                '_paq.push(["enableCrossDomainLinking"]);',
            ],
        ];

        yield 'groupPageTitlesByDomain' => [
            ['groupPageTitlesByDomain' => true],
            ['_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);'],
        ];

        yield 'doNotTrack' => [
            ['doNotTrack' => true],
            ['_paq.push(["setDoNotTrack", true]);'],
        ];

        yield 'disableCookies' => [
            ['disableCookies' => true],
            ['_paq.push(["disableCookies"]);'],
        ];

        yield 'disableCampaignParameters' => [
            ['disableCampaignParameters' => true],
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

        yield 'excludedQueryParams as array' => [
            ['excludedQueryParams' => ['uid', 'aid']],
            ['_paq.push(["setExcludedQueryParams", ["uid","aid"]]);'],
        ];

        yield 'excludedQueryParams as comma separated string' => [
            ['excludedQueryParams' => 'uid,aid'],
            ['_paq.push(["setExcludedQueryParams", ["uid","aid"]]);'],
        ];

        yield 'excludedReferrers as array' => [
            ['excludedReferrers' => ['example.com', 'http://example.org/path']],
            ['_paq.push(["setExcludedReferrers", ["example.com","http:\/\/example.org\/path"]]);'],
        ];

        yield 'excludedReferrers as comma separated string' => [
            ['excludedReferrers' => 'example.com,example.org'],
            ['_paq.push(["setExcludedReferrers", ["example.com","example.org"]]);'],
        ];

        yield 'trackNoScript' => [
            ['trackNoScript' => true],
            ['<noscript><p><img referrerpolicy="no-referrer-when-downgrade" src="//localhost/piwik/matomo.php?idsite=1&amp;rec=1" style="border:0;" alt="" /></p></noscript>'],
        ];

        yield 'visitorCustomVariables' => [
            ['visitorCustomVariables' => [['name', 'value'], ['name 2', 'value 2']]],
            [
                '_paq.push(["setCustomVariable", 1, "name", "value", "visit"]);',
                '_paq.push(["setCustomVariable", 2, "name 2", "value 2", "visit"]);',
            ],
            true,
        ];

        yield 'pageCustomVariables' => [
            ['pageCustomVariables' => [['page cvar', 'page cvar value']]],
            ['_paq.push(["setCustomVariable", 1, "page cvar", "page cvar value", "page"]);'],
            true,
        ];

        yield 'empty custom variables are skipped' => [
            ['visitorCustomVariables' => [[], ['name', 'value'], []]],
            ['_paq.push(["setCustomVariable", 1, "name", "value", "visit"]);'],
            true,
        ];
    }

    /**
     * @dataProvider getDisabledSingleOptions
     */
    public function testJavascriptTrackingCodeOmitsCodeForDisabledOption(array $overrides, string $unexpectedLine)
    {
        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode($overrides));

        self::assertStringNotContainsString($unexpectedLine, $trackingCode);
    }

    public function getDisabledSingleOptions(): iterable
    {
        yield 'groupPageTitlesByDomain' => [['groupPageTitlesByDomain' => false], 'setDocumentTitle'];
        yield 'doNotTrack' => [['doNotTrack' => false], 'setDoNotTrack'];
        yield 'disableCookies' => [['disableCookies' => false], 'disableCookies'];
        yield 'disableCampaignParameters' => [['disableCampaignParameters' => false], 'disableCampaignParameters'];
        yield 'crossDomain' => [['crossDomain' => false], 'enableCrossDomainLinking'];
        yield 'trackNoScript' => [['trackNoScript' => false], 'noscript'];
        yield 'customCampaignNameQueryParam' => [['customCampaignNameQueryParam' => ''], 'setCampaignNameKey'];
        yield 'customCampaignKeywordParam' => [['customCampaignKeywordParam' => ''], 'setCampaignKeywordKey'];
        yield 'excludedQueryParams' => [['excludedQueryParams' => ''], 'setExcludedQueryParams'];
        yield 'excludedReferrers' => [['excludedReferrers' => []], 'setExcludedReferrers'];
        yield 'visitorCustomVariables' => [['visitorCustomVariables' => []], 'setCustomVariable'];
        yield 'pageCustomVariables' => [['pageCustomVariables' => []], 'setCustomVariable'];
    }

    public function testJavascriptTrackingCodeIsHtmlEscapedWithAllOptionsEnabled()
    {
        $idSite = \Piwik\Plugins\SitesManager\API::getInstance()->addSite('Site name here', ['http://example.org']);

        $trackingCode = $this->generateTrackingCode([
            'idSite'                       => $idSite,
            'mergeSubdomains'              => true,
            'groupPageTitlesByDomain'      => true,
            'mergeAliasUrls'               => true,
            'visitorCustomVariables'       => [['name', 'value']],
            'pageCustomVariables'          => [['page cvar', 'page cvar value']],
            'customCampaignNameQueryParam' => 'cmpn',
            'customCampaignKeywordParam'   => 'cmpkw',
            'doNotTrack'                   => true,
            'disableCookies'               => true,
            'trackNoScript'                => true,
            'crossDomain'                  => true,
            'excludedQueryParams'          => ['uid', 'aid'],
            'excludedReferrers'            => ['example.com'],
            'disableCampaignParameters'    => true,
        ]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
    }

    /**
     * @dataProvider getUnsafeOptionValues
     */
    public function testJavascriptTrackingCodeEscapesUnsafeOptionValues(
        array $overrides,
        string $expectedJsCode,
        bool $requiresCustomVariables = false
    ) {
        $trackingCode = $this->generateTrackingCode($overrides);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);

        if (!$requiresCustomVariables || $this->hasCustomVariables()) {
            self::assertStringContainsString($expectedJsCode, $this->getRenderedTrackingCode($trackingCode));
        }
    }

    public function getUnsafeOptionValues(): iterable
    {
        foreach ($this->getUnsafeValueList() as $label => $value) {
            // a value may already be HTML encoded, in which case it is decoded before being escaped again
            $json = json_encode(
                html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE
            );

            yield "visitor custom variable name / $label" => [
                ['visitorCustomVariables' => [[$value, 'cvar value']]],
                '_paq.push(["setCustomVariable", 1, ' . $json . ', "cvar value", "visit"]);',
                true,
            ];

            yield "visitor custom variable value / $label" => [
                ['visitorCustomVariables' => [['cvar name', $value]]],
                '_paq.push(["setCustomVariable", 1, "cvar name", ' . $json . ', "visit"]);',
                true,
            ];

            yield "page custom variable name / $label" => [
                ['pageCustomVariables' => [[$value, 'cvar value']]],
                '_paq.push(["setCustomVariable", 1, ' . $json . ', "cvar value", "page"]);',
                true,
            ];

            yield "page custom variable value / $label" => [
                ['pageCustomVariables' => [['cvar name', $value]]],
                '_paq.push(["setCustomVariable", 1, "cvar name", ' . $json . ', "page"]);',
                true,
            ];

            yield "customCampaignNameQueryParam / $label" => [
                ['customCampaignNameQueryParam' => $value],
                '_paq.push(["setCampaignNameKey", ' . $json . ']);',
            ];

            yield "customCampaignKeywordParam / $label" => [
                ['customCampaignKeywordParam' => $value],
                '_paq.push(["setCampaignKeywordKey", ' . $json . ']);',
            ];

            yield "excludedQueryParams as array / $label" => [
                ['excludedQueryParams' => [$value]],
                '_paq.push(["setExcludedQueryParams", [' . $json . ']]);',
            ];

            yield "excludedReferrers as array / $label" => [
                ['excludedReferrers' => [$value]],
                '_paq.push(["setExcludedReferrers", [' . $json . ']]);',
            ];

            if (strpos($value, ',') === false) {
                yield "excludedQueryParams as string / $label" => [
                    ['excludedQueryParams' => $value],
                    '_paq.push(["setExcludedQueryParams", [' . $json . ']]);',
                ];

                yield "excludedReferrers as string / $label" => [
                    ['excludedReferrers' => $value],
                    '_paq.push(["setExcludedReferrers", [' . $json . ']]);',
                ];
            }
        }
    }

    /**
     * Values that must not be able to break out of the JavaScript strings of the generated code. The code
     * is copied out of the UI, so the browser resolves the HTML entities before it is ever used, which
     * leaves the JSON escaping as the only thing keeping a value inside its string.
     */
    public function getJavascriptStringBreakoutValues(): iterable
    {
        yield 'double quote' => ['abc"def'];
        yield 'encoded double quote' => ['abc&quot;def'];
        yield 'double encoded double quote' => ['abc&amp;quot;def'];
        yield 'escaped double quote' => ['abc\\"def'];
        yield 'single quote' => ["abc'def"];
        yield 'encoded single quote' => ['abc&#039;def'];
        yield 'command injection' => ['abc"],["setUserId","1"],["x'];
        yield 'closing script tag' => ['abc</script><script>alert(1)</script>'];
        yield 'nested script tag' => ['abc<!--<script>'];
        yield 'encoded command injection' => ['abc&quot;],[&quot;setUserId&quot;,&quot;1&quot;],[&quot;x'];
    }

    /**
     * @dataProvider getJavascriptStringBreakoutValues
     */
    public function testJavascriptTrackingCodeKeepsOptionValuesInsideTheirJavascriptString(string $value)
    {
        $options = [
            'customCampaignNameQueryParam' => [$value, 1],
            'customCampaignKeywordParam'   => [$value, 1],
            'excludedQueryParams'          => [[$value], 1],
            'excludedReferrers'            => [[$value], 1],
        ];

        if ($this->hasCustomVariables()) {
            // the value is used as the name and as the value of the custom variable
            $options['visitorCustomVariables'] = [[[$value, $value]], 2];
            $options['pageCustomVariables'] = [[[$value, $value]], 2];
        }

        foreach ($options as $option => [$optionValue, $expectedOccurrences]) {
            $trackingCode = $this->generateTrackingCode([$option => $optionValue]);

            $this->assertTrackerCommandsContainValue(
                $trackingCode,
                Common::unsanitizeInputValue($value),
                $expectedOccurrences,
                $option
            );

            $this->assertScriptElementIsIntact($trackingCode);
        }
    }

    /**
     * @dataProvider getJavascriptStringBreakoutValues
     */
    public function testJavascriptTrackingCodeKeepsSiteUrlsInsideTheirJavascriptString(string $value)
    {
        $idSite = \Piwik\Plugins\SitesManager\API::getInstance()->addSite(
            'Site name here',
            ['http://example.org/' . $value]
        );

        $trackingCode = $this->generateTrackingCode([
            'idSite'          => $idSite,
            'mergeSubdomains' => true,
            'mergeAliasUrls'  => true,
        ]);

        $this->assertTrackerCommandsContainValue(
            $trackingCode,
            '*.example.org/' . Common::unsanitizeInputValue($value),
            1
        );

        $this->assertScriptElementIsIntact($trackingCode);
    }

    /**
     * @dataProvider getJavascriptStringBreakoutValues
     */
    public function testJavascriptTrackingCodeKeepsTheTrackerUrlInsideItsJavascriptString(string $value)
    {
        $trackingCode = $this->generateTrackingCode([
            'piwikUrl'      => 'localhost/piwik/' . $value,
            'trackNoScript' => true,
        ]);

        $this->assertTrackerUrlIsASingleString($trackingCode);
        $this->assertScriptElementIsIntact($trackingCode);
    }

    /**
     * The protocol is built into the code instead of being substituted, so it takes a different path
     * than the other elements and needs its own coverage.
     */
    public function testJavascriptTrackingCodeKeepsTheProtocolInsideItsJavascriptString()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['protocol'] = '";alert(document.domain);var z="';
        });

        $trackingCode = $this->generateTrackingCode(['trackNoScript' => true]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        $this->assertTrackerUrlIsASingleString($trackingCode);
        $this->assertScriptElementIsIntact($trackingCode);
    }

    /**
     * Normalising a URL decodes it, so applying it more than once decodes more than once and addresses
     * a path the installation does not serve.
     */
    public function testJavascriptTrackingCodeDecodesTheTrackerUrlOnlyOnce()
    {
        $renderedCode = $this->getRenderedTrackingCode(
            $this->generateTrackingCode(['piwikUrl' => 'localhost/a&amp;amp;b'])
        );

        self::assertStringContainsString('var u="//localhost/a&amp;b/";', $renderedCode);
    }

    /**
     * The elements are substituted as strings, so a handler replacing one with anything else has to be
     * told, rather than the code ending up with the word Array in it.
     */
    public function testJavascriptTrackingCodeRefusesANonScalarElementSetByAPlugin()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['piwikUrl'] = ['localhost/piwik'];
        });

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The piwikUrl element of the tracking code must be a scalar value, array given.'
        );

        $this->generateTrackingCode();
    }

    /**
     * Null is not one of the values the generator refuses, which the check has to keep allowing.
     */
    public function testJavascriptTrackingCodeAcceptsANullElementSetByAPlugin()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['optionsBeforeTrackerUrl'] = null;
        });

        self::assertStringContainsString(
            "_paq.push(['trackPageView']);",
            $this->getRenderedTrackingCode($this->generateTrackingCode())
        );
    }

    /**
     * A HTTPS URL that is left without a host would send the visitors of that protocol to a tracker URL
     * addressing none, while the others keep being tracked.
     *
     * @dataProvider getHostlessHttpsPiwikUrls
     */
    public function testJavascriptTrackingCodeIgnoresAHostlessHttpsPiwikUrl(string $httpsPiwikUrl)
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) use ($httpsPiwikUrl) {
            $codeImpl['httpsPiwikUrl'] = $httpsPiwikUrl;
        });

        $renderedCode = $this->getRenderedTrackingCode($this->generateTrackingCode());

        self::assertStringContainsString('var u="//localhost/piwik/";', $renderedCode);
        self::assertStringNotContainsString('https:///', $renderedCode);
    }

    public function getHostlessHttpsPiwikUrls(): iterable
    {
        yield 'protocol only' => ['https://'];
        yield 'slash' => ['/'];
        yield 'slashes' => ['///'];
    }

    /**
     * The protocol is substituted before the pass resolving the placeholders, so a value looking like
     * one must not be expanded by it into the code the options hold.
     */
    public function testJavascriptTrackingCodeDoesNotExpandAPlaceholderSetAsTheProtocol()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['protocol'] = '{$options}';
        });

        $trackingCode = $this->generateTrackingCode([
            'excludedQueryParams' => ['uid', 'gclid'],
            'trackNoScript'       => true,
        ]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        $this->assertTrackerUrlIsASingleString($trackingCode);
        $this->assertScriptElementIsIntact($trackingCode);
    }

    /**
     * Everything a plugin sets besides the two code elements is a URL, a filename or an id, and ends up in
     * a JavaScript string or an HTML attribute of the copied code without any escaping left.
     */
    public function testJavascriptTrackingCodeStripsCharactersBreakingElementsSetByAPlugin()
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) {
            $codeImpl['piwikUrl'] = 'localhost/matomo" onload="alert(1)';
            $codeImpl['idSite'] = '1" onerror="alert(1)';
            $codeImpl['matomoPhpFilename'] = "matomo.php'+alert(1)+'";
            $codeImpl['matomoJsFilename'] = 'matomo.js"><script>alert(1)</script>';
        });

        $trackingCode = $this->generateTrackingCode(['trackNoScript' => true]);
        $renderedCode = $this->getRenderedTrackingCode($trackingCode);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);

        self::assertStringContainsString('var u="//localhost/matomo%22%20onload=%22alert(1)/";', $renderedCode);
        self::assertStringContainsString("_paq.push(['setSiteId', '1onerror=alert(1)']);", $renderedCode);
        self::assertStringContainsString("_paq.push(['setTrackerUrl', u+'matomo.php+alert(1)+']);", $renderedCode);
        self::assertStringContainsString("g.src=u+'matomo.jsscriptalert(1)/script';", $renderedCode);
        self::assertStringContainsString(
            'src="//localhost/matomo%22%20onload=%22alert(1)/matomo.php+alert(1)+'
            . '?idsite=1onerror%3Dalert%281%29&amp;rec=1"',
            $renderedCode
        );
    }

    /**
     * @dataProvider getUnsafeValues
     */
    public function testJavascriptTrackingCodeEscapesUnsafePiwikUrls(string $value)
    {
        $trackingCode = $this->generateTrackingCode(['piwikUrl' => 'http://localhost/piwik/' . $value, 'trackNoScript' => true]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
    }

    /**
     * @dataProvider getUnsafeValues
     */
    public function testJavascriptTrackingCodeEscapesUnsafeSiteIds(string $value)
    {
        $trackingCode = $this->generateTrackingCode(['idSite' => $value, 'trackNoScript' => true]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
    }

    /**
     * Site URLs are stored sanitized, so they reach the generator HTML encoded already. They still must
     * not end up unescaped in the generated code.
     *
     * @dataProvider getUnsafeValues
     */
    public function testJavascriptTrackingCodeEscapesUnsafeSiteUrls(string $value)
    {
        $url = 'http://example.org/' . str_replace(["\n", "\0"], '', $value);

        $idSite = \Piwik\Plugins\SitesManager\API::getInstance()->addSite('Site name here', [$url]);

        $trackingCode = $this->generateTrackingCode([
            'idSite'          => $idSite,
            'mergeSubdomains' => true,
            'mergeAliasUrls'  => true,
        ]);

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
    }

    /**
     * The `httpsPiwikUrl` element can be set by plugins listening to `Tracker.getJavascriptCode` and is
     * documented as a URL, so the generator is responsible for escaping it.
     *
     * @dataProvider getUnsafeValues
     */
    public function testJavascriptTrackingCodeEscapesUnsafeHttpsPiwikUrlSetByEvent(string $value)
    {
        Piwik::addAction('Tracker.getJavascriptCode', function (&$codeImpl) use ($value) {
            $codeImpl['httpsPiwikUrl'] = 'localhost/piwik/' . $value;
        });

        $trackingCode = $this->generateTrackingCode();

        $this->assertTrackingCodeIsSafeToEmbed($trackingCode);
        self::assertStringContainsString(
            'var u=((document.location.protocol === "https:") ? "https://localhost/piwik/',
            $this->getRenderedTrackingCode($trackingCode)
        );
    }


    /**
     * Only the characters that are active in HTML are escaped, so that a plugin post processing the
     * generated code still finds a non ASCII tracker domain in it.
     */
    public function testJavascriptTrackingCodeKeepsNonAsciiCharactersAsIs()
    {
        $trackingCode = $this->generateTrackingCode(['piwikUrl' => 'http://münchen.example/matomo']);

        self::assertStringContainsString('münchen.example/matomo', $trackingCode);
    }

    /**
     * Invalid UTF-8 must not blank the whole tracking code.
     */
    public function testJavascriptTrackingCodeIsGeneratedForInvalidUtf8Values()
    {
        $trackingCode = $this->generateTrackingCode(['customCampaignNameQueryParam' => "abc\xC3\x28def"]);

        self::assertStringContainsString("_paq.push(['trackPageView']);", $this->getRenderedTrackingCode($trackingCode));

        // the invalid character is substituted, rather than the value turning into an empty argument
        $this->assertTrackerCommandsContainValue($trackingCode, "abc\u{FFFD}(def", 1);
    }

    /**
     * A parameter value must not be able to reference one of the placeholders the generator replaces
     * after rendering the template.
     */
    public function testJavascriptTrackingCodeDoesNotReplacePlaceholdersContainedInParameterValues()
    {
        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode([
            'customCampaignNameQueryParam' => '{$idSite}',
            'piwikUrl'                     => 'localhost/{$options}',
        ]));

        // the options are JavaScript and keep the value as it is, which is what the single pass covers
        self::assertStringContainsString('_paq.push(["setCampaignNameKey", "{$idSite}"]);', $trackingCode);
        // in the URL the braces are not valid, so they are encoded before a pass could see them
        self::assertStringContainsString('var u="//localhost/%7B%24options%7D/";', $trackingCode);
    }

    public function testJavascriptTrackingCodeUsesPiwikEndpointsForOldInstallations()
    {
        $this->setInstallVersion('3.6.0');

        $trackingCode = $this->getRenderedTrackingCode($this->generateTrackingCode(['trackNoScript' => true]));

        self::assertStringContainsString("_paq.push(['setTrackerUrl', u+'piwik.php']);", $trackingCode);
        self::assertStringContainsString("g.src=u+'piwik.js';", $trackingCode);
        self::assertStringContainsString('src="//localhost/piwik/piwik.php?idsite=1&amp;rec=1"', $trackingCode);
    }

    public function testJavascriptTrackingCodeUsesMatomoEndpointsForOldInstallationsWhenForced()
    {
        $this->setInstallVersion('3.6.0');

        $generator = new TrackerCodeGenerator();
        $generator->forceMatomoEndpoint();

        $trackingCode = $this->getRenderedTrackingCode($generator->generate(1, 'http://localhost/piwik'));

        self::assertStringContainsString("_paq.push(['setTrackerUrl', u+'matomo.php']);", $trackingCode);
        self::assertStringContainsString("g.src=u+'matomo.js';", $trackingCode);
    }

    /**
     * The stripped tracking code is used as the plain text body of the tracking code email, so it must
     * contain the plain JavaScript and no markup at all.
     *
     * @dataProvider getUnsafeValues
     */
    public function testStripTagsReturnsThePlainTrackingCode(string $value)
    {
        $trackingCode = $this->generateTrackingCode([
            'customCampaignNameQueryParam' => $value,
            'trackNoScript'                => true,
        ]);

        $stripped = TrackerCodeGenerator::stripTags($trackingCode);

        self::assertStringNotContainsString('<', $stripped);
        self::assertStringNotContainsString('>', $stripped);
        self::assertStringContainsString("_paq.push(['trackPageView']);", $stripped);
        self::assertStringContainsString('_paq.push(["setCampaignNameKey", ', $stripped);
    }

    /**
     * @dataProvider getTrackerUrlsToNormalize
     */
    public function testNormalizeTrackerUrlStripsTheProtocolAndTrailingSlashes($piwikUrl, string $expected)
    {
        self::assertSame($expected, TrackerCodeGenerator::normalizeTrackerUrl($piwikUrl));
    }

    public function getTrackerUrlsToNormalize(): iterable
    {
        yield 'null' => [null, ''];
        yield 'empty' => ['', ''];
        yield 'without protocol' => ['localhost/piwik', 'localhost/piwik'];
        yield 'http' => ['http://localhost/piwik', 'localhost/piwik'];
        yield 'https' => ['https://localhost/piwik', 'localhost/piwik'];
        yield 'scheme without slashes' => ['mailto:localhost/piwik', 'mailto:localhost/piwik'];
        yield 'uppercase protocol' => ['HTTP://localhost/piwik', 'localhost/piwik'];
        yield 'other protocol' => ['ftp://localhost/piwik', 'localhost/piwik'];
        yield 'protocol relative' => ['//localhost/piwik', '//localhost/piwik'];
        yield 'trailing slashes' => ['http://localhost/piwik///', 'localhost/piwik'];
        yield 'host starting with http' => ['httpfoo.example/piwik', 'httpfoo.example/piwik'];
        yield 'line break' => ["localhost/pi\nwik", 'localhost/pi%0Awik'];
        yield 'null byte' => ["localhost/pi\0wik", 'localhost/pi%00wik'];
        yield 'double quote' => ['localhost/pi"wik', 'localhost/pi%22wik'];
        yield 'encoded double quote' => ['localhost/pi&quot;wik', 'localhost/pi%22wik'];
        yield 'single quote' => ["localhost/pi'wik", 'localhost/pi%27wik'];
        yield 'angle brackets' => ['localhost/pi<b>wik', 'localhost/pi%3Cb%3Ewik'];
        yield 'backslash' => ['localhost/pi\\wik', 'localhost/pi%5Cwik'];
        yield 'space' => ['localhost/pi wik', 'localhost/pi%20wik'];
        yield 'surrounding whitespace' => ['  http://localhost/piwik  ', 'localhost/piwik'];
        // a path is still served after the characters that would break the code were encoded
        yield 'apostrophe in the path' => ["http://localhost/bob's matomo/", 'localhost/bob%27s%20matomo'];
        yield 'encoded ampersand' => ['localhost/a&amp;b', 'localhost/a&b'];
        yield 'non ascii is kept' => ['münchen.example/piwik', 'münchen.example/piwik'];
    }

    private function setInstallVersion(string $version): void
    {
        Option::set(Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION, $version);
    }

    private function hasCustomVariables()
    {
        return Manager::getInstance()->isPluginActivated('CustomVariables');
    }
}
