<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Plugins\BulkTracking\tests\Framework\TestCase\BulkTrackingTestCase;
use Piwik\Plugins\BulkTracking\Tracker\Handler;
use Piwik\Tracker\Handler as DefaultHandler;
use Piwik\Tracker\RequestSet;

/**
 * @group BulkTracking
 * @group BulkTrackingTest
 * @group Plugins
 * @group Tracker
 */
class BulkTrackingTest extends BulkTrackingTestCase
{
    public function test_initRequestSet_shouldNotSetAnything_IfItIsActuallyNotUsingBulkRequest()
    {
        $requestSet = new RequestSet();
        $this->bulk->initRequestSet($requestSet);

        $this->assertEquals(array(), $requestSet->getRequests());
        $this->assertEquals(false, $requestSet->getTokenAuth());
    }

    public function test_initRequestSet_shouldNotSetAnything_IfNotBulkRequestRawDataIsGiven()
    {
        $requestSet = $this->initRequestSet('invalid:requests');

        $this->assertEquals(array(), $requestSet->getRequests());
        $this->assertEquals(false, $requestSet->getTokenAuth());
    }

    public function test_initRequestSet_shouldInitialize_AsItIsABulkRequest()
    {
        $token   = $this->getSuperUserToken();
        $request = $this->getDummyRequest($token);

        $requestSet = $this->initRequestSet($request);

        $requests = $requestSet->getRequests();
        $this->assertCount(2, $requests);
        $this->assertEquals(array('idsite' => '1', 'rec' => '1'), $requests[0]->getParams());
        $this->assertEquals(array('idsite' => '2', 'rec' => '1'), $requests[1]->getParams());
        $this->assertEquals($token, $requestSet->getTokenAuth());
    }

    public function test_initRequestSet_shouldNotOverwriteAToken_IfOneIsAlreadySet()
    {
        $token   = $this->getSuperUserToken();
        $request = $this->getDummyRequest($token);

        $requestSet = $this->initRequestSet($request, false, 'initialtoken');

        $this->assertEquals('initialtoken', $requestSet->getTokenAuth());
        $this->assertCount(2, $requestSet->getRequests());
    }

    public function test_initRequestSet_shouldNotFail_IfNoTokenProvided_AsAuthenticationIsDisabledByDefault()
    {
        $request = $this->getDummyRequest();

        $requestSet = $this->initRequestSet($request);

        $requests = $requestSet->getRequests();
        $this->assertCount(2, $requests);
    }

    public function test_initRequestSet_shouldTriggerException_InCaseNoValidTokenProvidedAndAuthenticationIsRequired()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('token_auth must be specified when using Bulk Tracking Import');

        $request = $this->getDummyRequest(false);

        $this->initRequestSet($request, true);
    }

    public function test_setHandlerIfBulkRequest_shouldSetBulkHandler_InCaseNoHandlerIsSetAndItIsABulkRequest()
    {
        $this->injectRawDataToBulk($this->getDummyRequest());

        $handler = null;
        $this->bulk->setHandlerIfBulkRequest($handler);

        $this->assertTrue($handler instanceof Handler);
    }

    public function test_setHandlerIfBulkRequest_shouldNotSetAHandler_IfOneIsAlreadySetEvenIfItIsABulkRequest()
    {
        $this->injectRawDataToBulk($this->getDummyRequest());

        $default = new DefaultHandler();
        $handler = $default;

        $this->bulk->setHandlerIfBulkRequest($default);

        $this->assertSame($default, $handler);
    }

    public function test_setHandlerIfBulkRequest_shouldNotSetAHandler_IfItIsNotABulkRequest()
    {
        $this->injectRawDataToBulk('{"test":"not a bulk request"}');

        $handler = null;

        $this->bulk->setHandlerIfBulkRequest($handler);

        $this->assertNull($handler);
    }

    public function test_registerEvents_shouldListenToNewTrackerEventAndCreateBulkHandler_IfBulkRequest()
    {
        $this->injectRawDataToBulk($this->getDummyRequest());

        $handler = DefaultHandler\Factory::make();

        $this->assertTrue($handler instanceof Handler);
    }

    public function test_registerEvents_shouldListenToNewTrackerEventAndNotCreateBulkHandler_IfNotBulkRequest()
    {
        $handler = DefaultHandler\Factory::make();

        $this->assertTrue($handler instanceof DefaultHandler);
    }

    public function test_registerEvents_shouldListenToInitRequestSetEventAndInit_IfBulkRequest()
    {
        $this->injectRawDataToBulk($this->getDummyRequest());

        $requestSet = new RequestSet();
        $requestSet->initRequestsAndTokenAuth();

        $this->assertCount(2, $requestSet->getRequests());
    }
}
