<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Plugins\BulkTracking\tests\Mock\TrackerResponse;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Tracker\ScheduledTasksRunner;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;
use Piwik\Plugins\BulkTracking\Tracker\Handler;
use Piwik\Tests\Framework\Mock\Tracker\RequestSet;
use Exception;

/**
 * @group HandlerTest
 * @group Handler
 * @group Tracker
 */
class HandlerTest extends IntegrationTestCase
{
    /**
     * @var Handler
     */
    private $handler;

    /**
     * @var TrackerResponse
     */
    private $response;

    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @var RequestSet
     */
    private $requestSet;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Tracker\Cache::deleteTrackerCache();

        $this->response = new TrackerResponse();
        $this->handler  = new Handler();
        $this->handler->setResponse($this->response);
        $this->tracker  = new Tracker();
        $this->requestSet = new RequestSet();
    }

    public function testInitShouldInitiateResponseInstance()
    {
        $this->handler->init($this->tracker, $this->requestSet);

        $this->assertTrue($this->response->isInit);
        $this->assertFalse($this->response->isResponseOutput);
        $this->assertFalse($this->response->isSend);
    }

    public function testFinishShouldOutputAndSendResponse()
    {
        $response = $this->handler->finish($this->tracker, $this->requestSet);

        $this->assertEquals('My Dummy Content', $response);

        $this->assertFalse($this->response->isInit);
        $this->assertFalse($this->response->isExceptionOutput);
        $this->assertTrue($this->response->isResponseOutput);
        $this->assertTrue($this->response->isSend);
    }

    public function testOnExceptionShouldOutputAndSendResponse()
    {
        $this->executeOnException($this->buildException());

        $this->assertFalse($this->response->isInit);
        $this->assertFalse($this->response->isResponseOutput);
        $this->assertTrue($this->response->isExceptionOutput);
        $this->assertFalse($this->response->isSend);
    }

    public function testOnExceptionShouldPassExceptionToResponse()
    {
        $exception = $this->buildException();

        $this->executeOnException($exception);

        $this->assertSame($exception, $this->response->exception);
        $this->assertSame(500, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendStatusCode400IfUnexpectedWebsite()
    {
        $this->executeOnException(new UnexpectedWebsiteFoundException('test'));
        $this->assertSame(400, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendStatusCode400IfInvalidRequestParameterException()
    {
        $this->executeOnException(new InvalidRequestParameterException('test'));
        $this->assertSame(400, $this->response->statusCode);
    }

    public function testOnExceptionShouldNotRethrowAnException()
    {
        self::expectNotToPerformAssertions();

        $exception = $this->buildException();

        $this->handler->onException($this->tracker, $this->requestSet, $exception);
    }

    public function testOnAllRequestsTrackedShouldNeverTriggerScheduledTasksEvenIfEnabled()
    {
        $runner = new ScheduledTasksRunner();
        $runner->shouldRun = true;

        $this->handler->setScheduledTasksRunner($runner);
        $this->handler->onAllRequestsTracked($this->tracker, $this->requestSet);

        $this->assertFalse($runner->ranScheduledTasks);
    }

    public function testProcessShouldTrackAllSetRequests()
    {
        $this->assertSame(0, $this->tracker->getCountOfLoggedRequests());

        $this->requestSet->setRequests(array(
            array('idsite' => 1, 'url' => 'http://localhost/foo?bar'),
            array('idsite' => 1, 'url' => 'http://localhost'),
        ));

        $this->handler->process($this->tracker, $this->requestSet);

        $this->assertSame(2, $this->tracker->getCountOfLoggedRequests());
    }

    public function testProcessAuthenticatesABulkRequestMadeWithAnUnscopedSuperUserToken()
    {
        $token = $this->createSuperUserToken(null);

        $this->requestSet->setTokenAuth($token);
        $this->requestSet->setRequests(array());

        $this->handler->process($this->tracker, $this->requestSet);

        $this->assertTrue($this->response->isAuthenticated());
    }

    public function testProcessDoesNotAuthenticateABulkRequestMadeWithASuperUserTokenScopedBelowSuperUser()
    {
        // The token belongs to a super user, so the authentication itself succeeds as a super-user one.
        // What the bulk request may do is decided by the access the token carries after its scope is
        // applied, which is no longer super-user access.
        $token = $this->createSuperUserToken('view');

        $this->requestSet->setTokenAuth($token);
        $this->requestSet->setRequests(array());

        $this->handler->process($this->tracker, $this->requestSet);

        $this->assertFalse($this->response->isAuthenticated());
    }

    private function createSuperUserToken(?string $accessLevel): string
    {
        Fixture::createSuperUser(false);

        $model = new UsersModel();
        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth(
            Fixture::ADMIN_USER_LOGIN,
            $token,
            'bulk tracking token ' . (string) $accessLevel,
            Date::now()->getDatetime(),
            null,
            false,
            false,
            $accessLevel
        );

        return $token;
    }

    private function buildException()
    {
        return new \Exception('MyMessage', 292);
    }

    private function executeOnException(Exception $exception)
    {
        try {
            $this->handler->onException($this->tracker, $this->requestSet, $exception);
        } catch (Exception $e) {
        }
    }
}
