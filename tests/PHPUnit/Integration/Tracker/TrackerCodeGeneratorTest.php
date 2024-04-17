<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Tests\Framework\Mock\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\TrackerCodeGenerator;

/**
 * @group Core
 * @group TrackerCodeGenerator
 */
class TrackerCodeGeneratorTest extends IntegrationTestCase
{
    public function testJavascriptTrackingCode_withAllOptions()
    {
        $generator = new TrackerCodeGenerator();

        $urls = array(
            'http://localhost/piwik',
            'http://another-domain/piwik',
            'https://another-domain/piwik'
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
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);
  _paq.push([\"setCookieDomain\", \"*.localhost\"]);
  _paq.push([\"setDomains\", [\"*.localhost/piwik\",\"*.another-domain/piwik\",\"*.another-domain/piwik\"]]);
  _paq.push([\"enableCrossDomainLinking\"]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);" : "") . "
  _paq.push([\"disableCampaignParameters\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push([\"setExcludedQueryParams\", [\"uid\",\"aid\"]]);
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

    public function testJavascriptTrackingCode_noScriptTrackingDisabled_defaultTrackingCode()
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
    public function testJavascriptTrackingCode_withAllOptionsAndProtocolOverwrite()
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
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);" : "") . "
  _paq.push([\"disableCampaignParameters\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push([\"setExcludedQueryParams\", [\"uid\",\"aid\"]]);
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
    public function testJavascriptTrackingCode_withAllOptionsAndOptionsBeforeTrackerUrl()
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
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);" . ($this->hasCustomVariables() ? "
  // you can set up to 5 custom variables for each visitor
  _paq.push([\"setCustomVariable\", 1, \"name\", \"value\", \"visit\"]);
  _paq.push([\"setCustomVariable\", 2, \"name 2\", \"value 2\", \"visit\"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push([\"setCustomVariable\", 1, \"page cvar\", \"page cvar value\", \"page\"]);" : "") . "
  _paq.push([\"disableCampaignParameters\"]);
  _paq.push([\"setCampaignNameKey\", \"campaignKey\"]);
  _paq.push([\"setCampaignKeywordKey\", \"keywordKey\"]);
  _paq.push([\"setDoNotTrack\", true]);
  _paq.push([\"setExcludedQueryParams\", [\"uid\",\"aid\"]]);
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
    public function testJavascriptTrackingCode_loadSync()
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
  _paq.push([\"setDocumentTitle\", document.domain + \"/\" + document.title]);
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
  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);' . ($this->hasCustomVariables() ? '
  // you can set up to 5 custom variables for each visitor
  _paq.push(["setCustomVariable", 1, "abc\"def", "abc\"def", "visit"]);
  // you can set up to 5 custom variables for each action (page view, download, click, site search)
  _paq.push(["setCustomVariable", 1, "abc\"def", "abc\"def", "page"]);' : '') . '
  _paq.push(["setCampaignNameKey", "abc\"def"]);
  _paq.push(["setCampaignKeywordKey", "abc\"def"]);
  _paq.push(["setExcludedQueryParams", ["u\"id","a\"id"]]);
  _paq.push([\'trackPageView\']);
  _paq.push([\'enableLinkTracking\']);
  (function() {
    var u=&quot;//abc&quot;def/&quot;;
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

    public function testJavascriptTrackingCode_withForceSsl()
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

    private function hasCustomVariables()
    {
        return Manager::getInstance()->isPluginActivated('CustomVariables');
    }
}
