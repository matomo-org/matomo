<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\ExceptionHandler;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionMethod;

/**
 * An exception thrown before the platform is initialized still produces an API response, which must
 * not be treated as application UI either. The response builder recognises the API endpoint by its
 * module and action, so the requests it leaves out are covered by the exception handler instead.
 *
 * @group Core
 */
class ExceptionHandlerApiResponseTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Common::$headersSentInTests = [];
        Request::setIsRootRequestApiRequest(null);
    }

    public function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        Common::$headersSentInTests = [];
        Request::setIsRootRequestApiRequest(null);

        parent::tearDown();
    }

    public function testErrorResponseForAnApiRequestSendsDataResponseHeaders()
    {
        $_GET = ['module' => 'API', 'method' => 'VisitsSummary.get', 'format' => 'json'];

        $this->getErrorResponse(new \Exception('Failure before the platform was initialized'));

        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options'] ?? ''));
        $this->assertSame('deny', trim(Common::$headersSentInTests['X-Frame-Options'] ?? ''));
        $this->assertSame('no-referrer', trim(Common::$headersSentInTests['Referrer-Policy'] ?? ''));
        $this->assertStringStartsWith(
            "default-src 'none';",
            trim(Common::$headersSentInTests['Content-Security-Policy'] ?? '')
        );
    }

    /**
     * Whatever content type earlier code left on the response, the renderer of the requested format
     * has to replace it, or the error body would be served under a type it does not have.
     */
    public function testErrorResponseForAnApiRequestIsServedAsTheRequestedFormat()
    {
        $_GET = ['module' => 'API', 'method' => 'VisitsSummary.get', 'format' => 'json'];
        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        $this->getErrorResponse(new \Exception('Failure before the platform was initialized'));

        $this->assertSame('application/json; charset=utf-8', trim(Common::$headersSentInTests['Content-Type']));
    }

    /**
     * An action of its own keeps the module and action gate of the response builder from matching,
     * so only the call in the exception handler sends the header set for such a response.
     */
    public function testErrorResponseForAnApiRequestTheResponseBuilderDoesNotRecogniseGetsThemToo()
    {
        $_GET = [
            'module' => 'API',
            'action' => 'listAllAPI',
            'method' => 'VisitsSummary.get',
            'format' => 'json',
        ];

        $this->getErrorResponse(new \Exception('Failure before the platform was initialized'));

        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options'] ?? ''));
        $this->assertSame('deny', trim(Common::$headersSentInTests['X-Frame-Options'] ?? ''));
        $this->assertStringStartsWith(
            "default-src 'none';",
            trim(Common::$headersSentInTests['Content-Security-Policy'] ?? '')
        );
    }

    public function testErrorResponseForAPageRequestSendsNoDataResponseHeaders()
    {
        $_GET = ['module' => 'CoreHome', 'action' => 'index'];

        $this->getErrorResponse(new \Exception('Failure before the platform was initialized'));

        $this->assertArrayNotHasKey('X-Content-Type-Options', Common::$headersSentInTests);
        $this->assertArrayNotHasKey('Content-Security-Policy', Common::$headersSentInTests);
    }

    /**
     * The module and method may be sent in the request body, as the bulk requests of the UI do, so
     * the response of such a request has to be formatted as API output like any other.
     */
    public function testErrorResponseForAnApiRequestSentAsPostIsFormattedAsApiOutput()
    {
        $_POST = ['module' => 'API', 'method' => 'VisitsSummary.get', 'format' => 'json'];

        $response = $this->getErrorResponse(new \Exception('Failure before the platform was initialized'));

        $this->assertJson($response);
        $this->assertStringContainsString('Failure before the platform was initialized', $response);
        $this->assertSame('nosniff', trim(Common::$headersSentInTests['X-Content-Type-Options'] ?? ''));
    }

    private function getErrorResponse(\Exception $exception): string
    {
        $method = new ReflectionMethod(ExceptionHandler::class, 'getErrorResponse');
        $method->setAccessible(true);

        return (string) $method->invokeArgs(null, [$exception]);
    }
}
