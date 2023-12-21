<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\ReferrerAnonymizer;
use Piwik\Plugins\PrivacyManager\Tracker\RequestProcessor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;


/**
 * @group PrivacyManager
 * @group RequestProcessorTest
 * @group RequestProcessor
 * @group Plugins
 */
class RequestProcessorTest extends IntegrationTestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestProcessor
     */
    private $requestProcessor;

    public function setUp(): void
    {
        parent::setUp();

        Option::set(PrivacyManager::OPTION_USERID_SALT, 'simpleuseridsalt1');
        Cache::clearCacheGeneral();

        $anonimiser = new ReferrerAnonymizer();

        $this->config = new Config();
        $this->requestProcessor = new RequestProcessor($this->config, $anonimiser);

        for ($i = 0; $i < 3; $i++) {
            Fixture::createWebsite('2014-01-01 02:03:04');
        }
    }

    public function test_onNewVisit_anonymiseReferrer_byDefaultNothingAnonymised()
    {
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $this->requestProcessor->onNewVisit($visit, $request);

        $this->assertVisitProperties($visit, Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
    }

    public function test_onNewVisit_anonymiseReferrer_byPath()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'barbaz', 'foo.com');
        $request = $this->makeRequest([]);
        $this->requestProcessor->onNewVisit($visit, $request);

        $this->assertVisitProperties($visit, Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/', '', 'foo.com');
    }

    public function test_onNewVisit_anonymiseReferrer_website_excludeAll()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $this->requestProcessor->onNewVisit($visit, $request);

        $this->assertVisitProperties($visit, Common::REFERRER_TYPE_WEBSITE, '', '', '');
    }

    public function test_onNewVisit_anonymiseReferrer_search_ExcludeAll()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_SEARCH_ENGINE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $this->requestProcessor->onNewVisit($visit, $request);

        $this->assertVisitProperties($visit, Common::REFERRER_TYPE_SEARCH_ENGINE, '', '', 'barbaz');
    }

    public function test_onExistingVisit_anonymiseReferrer_byDefaultNothingAnonymised()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_NONE;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $update = $visit->getProperties();
        $this->requestProcessor->onExistingVisit($update, $visit, $request);

        $this->assertVisitProperties($update, Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
    }

    public function test_onExistingVisit_anonymiseReferrer_byPath()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_PATH;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'barbaz', 'foo.com');
        $request = $this->makeRequest([]);
        $update = $visit->getProperties();
        $this->requestProcessor->onExistingVisit($update, $visit, $request);

        $this->assertVisitProperties($update, Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/', '', 'foo.com');
    }

    public function test_onExistingVisit_anonymiseReferrer_website_excludeAll()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_WEBSITE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $update = $visit->getProperties();
        $this->requestProcessor->onExistingVisit($update, $visit, $request);

        $this->assertVisitProperties($update, Common::REFERRER_TYPE_WEBSITE, '', '', '');
    }

    public function test_onExistingVisit_anonymiseReferrer_search_ExcludeAll()
    {
        $this->config->anonymizeReferrer = ReferrerAnonymizer::EXCLUDE_ALL;
        $visit = $this->makeReferrerVisitProperties(Common::REFERRER_TYPE_SEARCH_ENGINE, 'https://www.foo.com/path/?bar=baz', 'foo.com', 'barbaz');
        $request = $this->makeRequest([]);
        $update = $visit->getProperties();
        $this->requestProcessor->onExistingVisit($update, $visit, $request);

        $this->assertVisitProperties($update, Common::REFERRER_TYPE_SEARCH_ENGINE, '', '', 'barbaz');
    }

    private function assertVisitProperties($visit, $expectedType, $expectedUrl, $expectedKeyword, $exectedName)
    {
        if (is_array($visit)) {
            $visit = new VisitProperties($visit);
        }
        $this->assertEquals($expectedType, $visit->getProperty('referer_type'));
        $this->assertEquals($expectedUrl, $visit->getProperty('referer_url'));
        $this->assertEquals($expectedKeyword, $visit->getProperty('referer_keyword'));
        $this->assertEquals($exectedName, $visit->getProperty('referer_name'));
    }

    private function makeReferrerVisitProperties($type, $url, $keyword, $name)
    {
        $visit = new VisitProperties();
        $visit->setProperty('referer_type', $type);
        $visit->setProperty('referer_url', $url);
        $visit->setProperty('referer_keyword', $keyword);
        $visit->setProperty('referer_name', $name);
        return $visit;
    }

    public function test_manipulateRequest_enabledButNoUserIdNorOrderIdSet()
    {
        $this->config->anonymizeUserId = true;
        $this->config->anonymizeOrderId = true;

        $request = $this->makeRequest(array('idsite' => '3'));
        $this->requestProcessor->manipulateRequest($request);

        $this->assertSame(array('idsite' => '3'), $request->getParams());
    }

    public function test_manipulateRequest_enabledButEmptyValuesSet()
    {
        $this->config->anonymizeUserId = true;
        $this->config->anonymizeOrderId = true;

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => '', 'ec_id' => ''));
        $this->requestProcessor->manipulateRequest($request);

        $this->assertSame(array('idsite' => '3', 'uid' => '', 'ec_id' => ''), $request->getParams());
    }

    public function test_manipulateRequest_anonymizeUserIdOnly()
    {
        $this->config->anonymizeUserId = true;
        $this->config->anonymizeOrderId = false;

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'));
        $this->requestProcessor->manipulateRequest($request);

        $this->assertSame(array('idsite' => '3', 'uid' => '11d45007a54ea2dce76e57b9a1c2f0644b79687e', 'ec_id' => 'baz'), $request->getParams());
    }

    public function test_manipulateRequest_anonymizeOrderIdOnly()
    {
        $this->config->anonymizeUserId = false;
        $this->config->anonymizeOrderId = true;

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'));
        $this->requestProcessor->manipulateRequest($request);

        $params = $request->getParams();
        $this->assertSame(40, strlen($params['ec_id']));
        $this->assertTrue(ctype_xdigit($params['ec_id']));
        unset($params['ec_id']);

        $this->assertSame(array('idsite' => '3', 'uid' => 'foobar'), $params);
    }

    public function test_manipulateRequest_anonymizeOrderIdIsAlwaysDifferent()
    {
        $this->config->anonymizeUserId = false;
        $this->config->anonymizeOrderId = true;

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'));
        $this->requestProcessor->manipulateRequest($request);
        $params1 = $request->getParams();

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'));
        $this->requestProcessor->manipulateRequest($request);
        $params2 = $request->getParams();

        $this->assertNotSame($params1['ec_id'], $params2['ec_id']);
    }

    public function test_manipulateRequest_anonymizeDisabled()
    {
        $this->config->anonymizeUserId = false;
        $this->config->anonymizeOrderId = false;

        $request = $this->makeRequest(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'));
        $this->requestProcessor->manipulateRequest($request);

        $this->assertSame(array('idsite' => '3', 'uid' => 'foobar', 'ec_id' => 'baz'), $request->getParams());
    }

    private function makeRequest($request)
    {
        return new Request($request);
    }
}
