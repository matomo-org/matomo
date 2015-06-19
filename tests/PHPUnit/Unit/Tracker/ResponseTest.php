<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Common;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Response;
use Piwik\Tests\Framework\Mock\Tracker;
use Exception;

class TestResponse extends Response {

    protected function logExceptionToErrorLog(Exception $e)
    {
        // prevent console from outputting the error_log message
    }

    public function getMessageFromException($e)
    {
        return parent::getMessageFromException($e);
    }
}

/**
 * @group BulkTracking
 * @group ResponseTest
 * @group Plugins
 * @group Tracker
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestResponse
     */
    private $response;

    public function setUp()
    {
        parent::setUp();
        $this->response = new TestResponse();
    }

    public function test_outputException_shouldAlwaysOutputApiResponse_IfDebugModeIsDisabled()
    {
        $this->response->init($this->getTracker());
        $this->response->outputException($this->getTracker(), new Exception('My Custom Message'), 400);

        Fixture::checkResponse($this->response->getOutput());
    }

    public function test_outputException_shouldOutputDebugMessageIfEnabled()
    {
        $tracker = $this->getTracker();
        $this->response->init($tracker);

        $tracker->enableDebugMode();

        $this->response->outputException($tracker, new Exception('My Custom Message'), 400);

        $content = $this->response->getOutput();

        $this->assertContains('<title>Piwik &rsaquo; Error</title>', $content);
        $this->assertContains('<p>My Custom Message', $content);
    }

    public function test_outputResponse_shouldOutputStandardApiResponse()
    {
        $this->response->init($this->getTracker());
        $this->response->outputResponse($this->getTracker());

        Fixture::checkResponse($this->response->getOutput());
    }

    public function test_outputResponse_shouldNotOutputApiResponse_IfDebugModeIsEnabled_AsWePrintOtherStuff()
    {
        $this->response->init($this->getTracker());

        $tracker = $this->getTracker();
        $tracker->enableDebugMode();
        $this->response->outputResponse($tracker);

        $this->assertEquals('', $this->response->getOutput());
    }

    public function test_outputResponse_shouldNotOutputApiResponse_IfSomethingWasPrintedUpfront()
    {
        $this->response->init($this->getTracker());

        echo 5;
        $this->response->outputResponse($this->getTracker());

        $this->assertEquals('5', $this->response->getOutput());
    }

    public function test_outputResponse_shouldNotOutputResponseTwice_IfExceptionWasAlreadyOutput()
    {
        $this->response->init($this->getTracker());

        $this->response->outputException($this->getTracker(), new Exception('My Custom Message'), 400);
        $this->response->outputResponse($this->getTracker());

        Fixture::checkResponse($this->response->getOutput());
    }

    public function test_outputResponse_shouldOutputNoResponse_If204HeaderIsRequested()
    {
        $this->response->init($this->getTracker());

        $_GET['send_image'] = '0';
        $this->response->outputResponse($this->getTracker());
        unset($_GET['send_image']);

        $this->assertEquals('', $this->response->getOutput());
    }

    public function test_outputResponse_shouldOutputPiwikMessage_InCaseNothingWasTracked()
    {
        $this->response->init($this->getTracker());

        $tracker = $this->getTracker();
        $tracker->setCountOfLoggedRequests(0);
        $this->response->outputResponse($tracker);

        $this->assertEquals("<a href='/'>Piwik</a> is a free/libre web <a href='http://piwik.org'>analytics</a> that lets you keep control of your data.",
            $this->response->getOutput());
    }

    public function test_getMessageFromException_ShouldNotOutputAnyDetails_IfErrorContainsDbCredentials()
    {
        $message = $this->response->getMessageFromException(new Exception('Test Message', 1044));
        $this->assertStringStartsWith("Error while connecting to the Piwik database", $message);

        $message = $this->response->getMessageFromException(new Exception('Test Message', 42000));
        $this->assertStringStartsWith("Error while connecting to the Piwik database", $message);
    }

    public function test_getMessageFromException_ShouldReturnMessageAndTrace_InCaseIsCli()
    {
        $message = $this->response->getMessageFromException(new Exception('Test Message', 8150));
        $this->assertStringStartsWith("Test Message\n#0 [internal function]", $message);
    }

    public function test_getMessageFromException_ShouldOnlyReturnMessage_InCaseIsNotCli()
    {
        Common::$isCliMode = false;
        $message = $this->response->getMessageFromException(new Exception('Test Message', 8150));
        Common::$isCliMode = true;

        $this->assertStringStartsWith("Test Message", $message);
    }

    public function test_outputResponse_shouldOutputApiResponse_IfTrackerIsDisabled()
    {
        $this->response->init($this->getTracker());

        $tracker = $this->getTracker();
        $tracker->setCountOfLoggedRequests(0);
        $tracker->disableShouldRecordStatistics();
        $this->response->outputResponse($tracker);

        Fixture::checkResponse($this->response->getOutput());
    }

    private function getTracker()
    {
        $tracker = new Tracker();
        $tracker->setCountOfLoggedRequests(5);
        return $tracker;
    }

}
