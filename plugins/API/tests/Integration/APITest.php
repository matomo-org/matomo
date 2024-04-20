<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\API\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group API
 * @group APITest
 * @group Plugins
 */
class APITest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    private $hasSuperUserAccess = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        Fixture::createSuperUser(true);

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $this->makeSureTestRunsInContextOfAnonymousUser();
    }

    public function tearDown(): void
    {
        Access::getInstance()->hasSuperUserAccess($this->hasSuperUserAccess);
        parent::tearDown();
    }

    public function test_getBulkRequest_IsAbleToHandleManyDifferentRequests()
    {
        $token = Fixture::getTokenAuth();
        $urls = array(
            "method%3dVisitsSummary.get%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26token_auth%3d$token%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26idSite%3d1%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26idSite%3d1%26token_auth%3danonymous%26date%3d2015-01-26%26period%3dday",
            "method%3dVisitsSummary.get%26token_auth%3d$token%26idSite%3d1%26date%3d2015-01-26%26period%3dday%26segment%3dvisitDuration%3d%3d30%3bactions%3e2",
        );
        $response = $this->api->getBulkRequest($urls);

        $this->assertResponseIsPermissionError($response[0]);
        $this->assertResponseIsSuccess($response[1]);
        $this->assertSame(0, $response[1]['nb_visits']);
        $this->assertResponseIsPermissionError($response[2]);
        $this->assertResponseIsPermissionError($response[3]);
        $this->assertResponseIsSuccess($response[4]);
    }

    private function assertResponseIsPermissionError($response)
    {
        $this->assertSame('error', $response['result']);
        $this->assertStringStartsWith('General_YouMustBeLoggedIn', $response['message']);
    }

    private function assertResponseIsSuccess($response)
    {
        $this->assertArrayNotHasKey('result', $response);
    }

    private function makeSureTestRunsInContextOfAnonymousUser()
    {
        Piwik::postEvent('Request.initAuthenticationObject');

        $access = Access::getInstance();
        $this->hasSuperUserAccess = $access->hasSuperUserAccess();
        $access->setSuperUserAccess(false);
        $access->reloadAccess(StaticContainer::get('Piwik\Auth'));
        Request::reloadAuthUsingTokenAuth(array('token_auth' => 'anonymous'));
    }
}
