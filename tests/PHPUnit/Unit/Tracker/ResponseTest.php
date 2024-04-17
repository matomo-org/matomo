<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Response;
use Piwik\Tests\Framework\Mock\Tracker;
use Exception;

class TestResponse extends Response
{
    protected function logExceptionToErrorLog($e)
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
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestResponse
     */
    private $response;

    public function setUp(): void
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

        self::assertStringContainsString('An exception occurred', $content);
        self::assertStringContainsString('My Custom Message', $content);
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

        $this->assertEquals(
            "This resource is part of Matomo. Keep full control of your data with the leading free and open source <a href='https://matomo.org' target='_blank' rel='noopener noreferrer nofollow'>web analytics & conversion optimisation platform</a>.<br>\nThis file is the endpoint for the Matomo tracking API. If you want to access the Matomo UI or use the Reporting API, please use <a href='index.php'>index.php</a> instead.\n",
            $this->response->getOutput()
        );
    }

    public function test_getMessageFromException_ShouldNotOutputAnyDetails_IfErrorContainsDbCredentials()
    {
        $message = $this->response->getMessageFromException(new Exception('Test Message', 1044));
        $this->assertStringStartsWith("Error while connecting to the Matomo database", $message);

        $message = $this->response->getMessageFromException(new Exception('Test Message', 42000));
        $this->assertStringStartsWith("Error while connecting to the Matomo database", $message);
    }

    public function test_getMessageFromException_ShouldReturnMessageAndTrace_InCaseIsCli()
    {
        $message = $this->response->getMessageFromException(new Exception('Test Message', 8150));
        $this->assertStringStartsWith("Test Message\n#0 ", $message);
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

    public function test_outputResponse_shouldOuputCustomImage_IfCustomBase64ImageSet()
    {
        // Base64 sample image string (4x4px red PNG made in GIMP)
        $base64Image = 'iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAIAAAACDbGyAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAB3RJTUUH5QgLFiABlwQnpwAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAAUSURBVAjXY/wjLMyABJgYUAGpfABbJQEsALGyNgAAAABJRU5ErkJggg==';

        // Initialise the custom_image setting
        $config = Config::getInstance();
        $trackerSettings = $config->Tracker;
        $trackerSettings['custom_image'] = $base64Image;
        $config->Tracker = $trackerSettings;

        // Get the response
        $tracker = $this->getTracker();
        $this->response->init($tracker);
        $this->response->outputResponse($tracker);
        $response = $this->response->getOutput();

        // Encode the response back into base64 and compare with the original
        $this->assertSame($base64Image, base64_encode($response));
    }

    public function test_outputResponse_shouldOuputCustomImage_IfCustomImageFileSet()
    {

        // Using the Matomo logo file from the Morpheus theme plugin
        $testImagePath = PIWIK_INCLUDE_PATH . '/plugins/Morpheus/images/logo.png';
        $this->assertFileExists($testImagePath, "Unable to find the test image for custom image file test");
        $md5File = md5_file($testImagePath);

        // Initialise the custom_image setting
        $config = Config::getInstance();
        $trackerSettings = $config->Tracker;
        $trackerSettings['custom_image'] = $testImagePath;
        $config->Tracker = $trackerSettings;

        // Get the response
        $tracker = $this->getTracker();
        $this->response->init($tracker);
        $this->response->outputResponse($tracker);
        $response = $this->response->getOutput();
        $md5Response = md5($response);

        // Compare the hash of the response with the file hash
        $this->assertSame($md5Response, $md5File);
    }

    private function getTracker()
    {
        $tracker = new Tracker();
        $tracker->setCountOfLoggedRequests(5);
        return $tracker;
    }
}
