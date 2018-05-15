<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\EventDispatcher;
use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\RequestSet;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestRequestSet extends RequestSet {

    private $redirectUrl = '';

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}
/**
 * @group RequestSetTest
 * @group RequestSet
 * @group Tracker
 */
class RequestSetTest extends IntegrationTestCase
{
    /**
     * @var TestRequestSet
     */
    private $requestSet;
    private $get;
    private $post;

    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00', 0, false, 'http://www.example.com');

        $this->requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();
        $this->requestSet->setRequests(array(array('idsite' => 1), array('idsite' => 2)));

        $this->get  = $_GET;
        $this->post = $_POST;

        $_GET  = array();
        $_POST = array();
    }

    public function tearDown()
    {
        $_GET  = $this->get;
        $_POST = $this->post;

        parent::tearDown();
    }

    public function test_shouldPerformRedirectToUrl_shouldNotRedirect_IfNoUrlIsSet()
    {
        $this->assertFalse($this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_shouldPerformRedirectToUrl_shouldNotRedirect_IfUrlIsSetButNoRequests()
    {
        $this->requestSet->setRedirectUrl('http://localhost');
        $this->assertEquals('http://localhost', $this->requestSet->getRedirectUrl());

        $this->requestSet->setRequests(array());

        $this->assertFalse($this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_shouldPerformRedirectToUrl_shouldNotRedirect_IfUrlHasNoHostOrIsNotUrl()
    {
        $this->requestSet->setRedirectUrl('abc');

        $this->assertFalse($this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_shouldPerformRedirectToUrl_shouldNotRedirect_IfUrlIsNotWhitelistedInAnySiteId()
    {
        $this->requestSet->setRedirectUrl('http://example.org');

        $this->assertFalse($this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_shouldPerformRedirectToUrl_shouldRedirect_IfUrlIsGivenAndWhitelistedInAnySiteId()
    {
        $this->requestSet->setRedirectUrl('http://www.example.com');

        $this->assertEquals('http://www.example.com', $this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_shouldPerformRedirectToUrl_shouldRedirect_IfBaseDomainIsGivenAndWhitelistedInAnySiteId()
    {
        $this->requestSet->setRedirectUrl('http://example.com');

        $this->assertEquals('http://example.com', $this->requestSet->shouldPerformRedirectToUrl());
    }

    public function test_initRequestsAndTokenAuth_shouldTriggerEventToInitRequestsButOnlyOnce()
    {
        $requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();

        $called = 0;
        $passedRequest = null;
        Piwik::addAction('Tracker.initRequestSet', function ($param) use (&$called, &$passedRequest) {
            $called++;
            $passedRequest = $param;
        });

        $requestSet->initRequestsAndTokenAuth();
        $this->assertSame(1, $called);
        $this->assertSame($requestSet, $passedRequest);

        $requestSet->initRequestsAndTokenAuth(); // should not be called again
        $this->assertSame(1, $called);
    }

    public function test_initRequestsAndTokednAuth_shouldInitializeRequestsWithEmptyArray()
    {
        $requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();
        $requestSet->initRequestsAndTokenAuth();
        $this->assertEquals(array(), $requestSet->getRequests());
    }

    public function test_initRequestsAndTokednAuth_shouldInitializeFromGetAndPostIfEventDoesNotHandleRequests()
    {
        $_GET  = array('idsite' => 1);
        $_POST = array('c_i' => 'click');

        Piwik::addAction('Tracker.initRequestSet', function (RequestSet $requestSet) {
            $requestSet->setRequests(array(array('idsite' => '2'), array('idsite' => '3')));
        });

        $requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();

        $requestSet->initRequestsAndTokenAuth();

        $requests = $requestSet->getRequests();
        $this->assertCount(2, $requests);
        $this->assertEquals(array('idsite' => '2'), $requests[0]->getParams());
        $this->assertEquals(array('idsite' => '3'), $requests[1]->getParams());
    }

    public function test_initRequestsAndTokednAuth_shouldIgnoreGetAndPostIfInitializedByEvent()
    {
        $_GET  = array('idsite' => '1');
        $_POST = array('c_i' => 'click');

        $requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();
        $requestSet->initRequestsAndTokenAuth();
        $requests = $requestSet->getRequests();
        $this->assertCount(1, $requests);
        $this->assertEquals(array('idsite' => 1, 'c_i' => 'click'), $requests[0]->getParams());
    }

    private function buildNewRequestSetThatIsNotInitializedYet()
    {
        return new TestRequestSet();
    }
}
