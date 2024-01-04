<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Unit;

use Piwik\Plugins\BulkTracking\Tracker\Response;
use Piwik\Tests\Framework\Mock\Tracker;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Exception;

class TestResponse extends Response
{
    protected function logExceptionToErrorLog($e)
    {
        // prevent console from outputting the error_log message
    }
}

/**
 * @group BulkTracking
 * @group ResponseTest
 * @group Plugins
 */
class ResponseTest extends UnitTestCase
{
    /**
     * @var TestResponse
     */
    private $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->response = new TestResponse();
        $this->response->init(new Tracker());
    }

    public function test_outputException_shouldOutputBulkResponse()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->outputException($tracker, new Exception('My Custom Message'), 400);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"error","tracked":5,"invalid":0}', $content);
    }

    public function test_outputException_shouldOutputDebugMessageIfEnabled()
    {
        $tracker = $this->getTrackerWithCountedRequests();
        $tracker->enableDebugMode();

        $this->response->outputException($tracker, new Exception('My Custom Message'), 400);
        $content = $this->response->getOutput();

        $this->assertStringStartsWith('{"status":"error","tracked":5,"invalid":0,"message":"My Custom Message\n', $content);
    }

    public function test_outputResponse_shouldOutputBulkResponse()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->outputResponse($tracker);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"success","tracked":5,"invalid":0}', $content);
    }

    public function test_outputResponse_shouldNotOutputAnything_IfExceptionResponseAlreadySent()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->outputException($tracker, new Exception('My Custom Message'), 400);
        $this->response->outputResponse($tracker);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"error","tracked":5,"invalid":0}', $content);
    }

    public function test_outputResponse_shouldIncludeInvalidIndices_IfExceptionSet_AndRequestAuthenticated()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->setInvalidRequests(array(10, 20));
        $this->response->setIsAuthenticated(true);
        $this->response->outputException($tracker, new Exception('My Custom Message'), 400);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"error","tracked":5,"invalid":2,"invalid_indices":[10,20]}', $content);
    }

    public function test_outputResponse_shouldOutputInvalidRequests_IfInvalidIndicesSet_AndRequestNotAuthenticated()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->setInvalidRequests(array(5, 63, 72));
        $this->response->outputResponse($tracker);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"success","tracked":5,"invalid":3}', $content);
    }

    public function test_outputResponse_shouldOutputInvalidRequests_IfInvalidIndicesSet_AndRequestAuthenticated()
    {
        $tracker = $this->getTrackerWithCountedRequests();

        $this->response->setInvalidRequests(array(5, 63, 72));
        $this->response->setIsAuthenticated(true);
        $this->response->outputResponse($tracker);
        $content = $this->response->getOutput();

        $this->assertEquals('{"status":"success","tracked":5,"invalid":3,"invalid_indices":[5,63,72]}', $content);
    }

    private function getTrackerWithCountedRequests()
    {
        $tracker = new Tracker();
        $tracker->setCountOfLoggedRequests(5);
        return $tracker;
    }
}
