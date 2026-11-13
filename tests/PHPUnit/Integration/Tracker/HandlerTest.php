<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Exception\PluginDeactivatedException;
use Piwik\Exception\PluginNotFoundException;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\BadRequestException;
use Piwik\Log\LoggerInterface;
use Piwik\NoAccessException;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Tracker\Response;
use Piwik\Tests\Framework\Mock\Tracker\ScheduledTasksRunner;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker;
use Piwik\Tracker\Handler;
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
     * @var Response
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

        $this->response = new Response();
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

    public function testFinishShoulAlsoReturnAPossibleRenderedException()
    {
        $this->executeOnException($this->buildException());
        $response = $this->handler->finish($this->tracker, $this->requestSet);

        $this->assertEquals('MyMessage', $response);
    }

    public function testOnExceptionShouldOutputResponse()
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

    public function testOnExceptionShouldSendStatusCode401IfNoAccessException()
    {
        $this->executeOnException(new NoAccessException('test'));
        $this->assertSame(401, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendStatusCode403IfNoAccessExceptionWithForbiddenCode()
    {
        $this->executeOnException(new NoAccessException('test', 403));
        $this->assertSame(403, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendStatusCode400IfNoAccessExceptionHasAnUnsupportedCode()
    {
        $this->executeOnException(new NoAccessException('test', 0));
        $this->assertSame(400, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendStatusCode500IfPluginIsDeactivated()
    {
        $this->executeOnException(new PluginDeactivatedException('Goals'));
        $this->assertSame(500, $this->response->statusCode);
    }

    public function testOnExceptionShouldSendTheCodeOfAnHttpCodeExceptionAndOnlyLogItAtDebugLevel()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $handler = new Handler();
        $handler->setResponse($this->response);
        $handler->onException($this->tracker, $this->requestSet, new ThingNotFoundException('Form not found'));

        $this->assertSame(404, $this->response->statusCode);
    }

    public function testOnExceptionShouldLogAServiceUnavailableHttpCodeExceptionAsError()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $handler = new Handler();
        $handler->setResponse($this->response);
        $handler->onException($this->tracker, $this->requestSet, new BadRequestException('Try again later', 503));

        $this->assertSame(503, $this->response->statusCode);
    }

    /**
     * @dataProvider getExceptionsWithStatusCode
     */
    public function testGetStatusCodeForException(\Throwable $exception, int $expectedStatusCode)
    {
        $this->assertSame($expectedStatusCode, Handler::getStatusCodeForException($exception));
    }

    public function getExceptionsWithStatusCode(): iterable
    {
        yield 'bad request' => [new BadRequestException('bad'), 400];
        yield 'not found' => [new ThingNotFoundException('not found'), 404];
        yield 'too many requests' => [new BadRequestException('slow down', 429), 429];
        yield 'unsupported client error code' => [new BadRequestException('conflict', 409), 400];
        yield 'service unavailable' => [new BadRequestException('later', 503), 503];
        yield 'other server error code' => [new BadRequestException('gateway', 502), 500];
        yield 'no access without code' => [new NoAccessException('no access', 0), 400];
        yield 'plugin deactivated' => [new PluginDeactivatedException('Goals'), 500];
        yield 'plugin not found' => [new PluginNotFoundException('Goals'), 500];
        yield 'invalid parameter' => [new InvalidRequestParameterException('invalid'), 400];
        yield 'generic exception' => [new Exception('failed', 404), 500];
        yield 'php error' => [new \TypeError('type'), 500];
    }

    public function testOnExceptionShouldStillOutputExceptionIfLoggerFails()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('error')->willThrowException(new \UnexpectedValueException('log file not writable'));
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);

        $handler = new Handler();
        $handler->setResponse($this->response);
        $handler->onException($this->tracker, $this->requestSet, new Exception('MyMessage'));

        $this->assertTrue($this->response->isExceptionOutput);
        $this->assertSame(500, $this->response->statusCode);
        $this->assertSame('MyMessage', $this->response->exception->getMessage());
    }

    public function testOnExceptionShouldNotRethrowExceptionToExitTrackerImmediately()
    {
        self::expectNotToPerformAssertions();

        $exception = $this->buildException();

        $this->handler->onException($this->tracker, $this->requestSet, $exception);
    }

    public function testOnAllRequestsTrackedShouldTriggerScheduledTasksIfEnabled()
    {
        $runner = new ScheduledTasksRunner();
        $runner->shouldRun = true;

        $this->handler->setScheduledTasksRunner($runner);
        $this->handler->onAllRequestsTracked($this->tracker, $this->requestSet);

        $this->assertTrue($runner->ranScheduledTasks);
    }

    public function testOnAllRequestsTrackedShouldNotTriggerScheduledTasksIfDisabled()
    {
        $runner = new ScheduledTasksRunner();
        $runner->shouldRun = false;

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
