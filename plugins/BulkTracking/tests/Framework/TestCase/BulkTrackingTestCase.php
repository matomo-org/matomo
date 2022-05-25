<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Framework\TestCase;

use Piwik\Plugin;
use Piwik\Plugins\BulkTracking\BulkTracking;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\BulkTracking\tests\Framework\Mock\Tracker\Requests;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\RequestSet;

/**
 * @group BulkTracking
 * @group BulkTrackingTest
 * @group Plugins
 * @group Tracker
 */
class BulkTrackingTestCase extends IntegrationTestCase
{
    /**
     * @var BulkTracking
     */
    protected $bulk;

    private $pluginBackup;

    public function setUp(): void
    {
        parent::setUp();

        $this->bulk = new BulkTracking();

        $this->pluginBackup = Plugin\Manager::getInstance()->getLoadedPlugin('BulkTracking');
        Plugin\Manager::getInstance()->addLoadedPlugin('BulkTracking', $this->bulk);
    }

    public function tearDown(): void
    {
        Plugin\Manager::getInstance()->addLoadedPlugin('BulkTracking', $this->pluginBackup);
        parent::tearDown();
    }

    protected function getSuperUserToken()
    {
        Fixture::createSuperUser(false);
        return Fixture::getTokenAuth();
    }

    protected function injectRawDataToBulk($rawData, $requiresAuth = false)
    {
        $requests = new Requests();
        $requests->setRawData($rawData);

        if ($requiresAuth) {
            $requests->enableRequiresAuth();
        }

        $this->bulk->setRequests($requests);
    }

    protected function initRequestSet($rawData, $requiresAuth = false, $initToken = null)
    {
        $requestSet = new RequestSet();

        if (!is_null($initToken)) {
            $requestSet->setTokenAuth($initToken);
        }

        $this->injectRawDataToBulk($rawData, $requiresAuth);

        $this->bulk->initRequestSet($requestSet);

        return $requestSet;
    }

    protected function getDummyRequest($token = null, $idSites = array(1, 2))
    {
        $params = array(array('idsite' => $idSites[0], 'rec' => '1'), array('idsite' => $idSites[1], 'rec' => '1'));
        $params = array('requests' => $params);

        if (!is_null($token)) {
            $params['token_auth'] = $token;
        }

        $request = json_encode($params);

        return $request;
    }
}
