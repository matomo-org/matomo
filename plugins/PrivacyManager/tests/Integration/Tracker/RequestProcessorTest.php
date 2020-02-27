<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Tracker;

use Piwik\Option;
use Piwik\Plugins\PrivacyManager\Config;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\PrivacyManager\Tracker\RequestProcessor;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;


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

        $this->requestProcessor = new RequestProcessor();
        $this->config = new Config();

        for ($i = 0; $i < 3; $i++) {
            Fixture::createWebsite('2014-01-01 02:03:04');
        }
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
