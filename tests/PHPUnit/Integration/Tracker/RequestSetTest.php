<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Piwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestSet;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestRequestSet extends RequestSet
{
    private $redirectUrl = '';

    public function getAllSiteIdsWithinRequest()
    {
        return parent::getAllSiteIdsWithinRequest();
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
    private $time;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00', 0, false, 'http://www.example.com');

        foreach (range(3, 10) as $idSite) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();
        $this->requestSet->setRequests(array(array('idsite' => 1), array('idsite' => 2)));

        $this->get  = $_GET;
        $this->post = $_POST;

        $this->time = time();

        $_GET  = array();
        $_POST = array();
    }

    public function tearDown(): void
    {
        $_GET  = $this->get;
        $_POST = $this->post;

        parent::tearDown();
    }

    public function test_getAllSiteIdsWithinRequest_ShouldReturnEmptyArray_IfNoRequestsSet()
    {
        $this->requestSet = $this->buildNewRequestSetThatIsNotInitializedYet();
        $this->assertEquals(array(), $this->requestSet->getAllSiteIdsWithinRequest());
    }

    public function test_getAllSiteIdsWithinRequest_ShouldReturnTheSiteIds_FromRequests()
    {
        $this->requestSet->setRequests($this->buildRequests(3));

        $this->assertEquals(array(1, 2, 3), $this->requestSet->getAllSiteIdsWithinRequest());
    }

    public function test_getAllSiteIdsWithinRequest_ShouldReturnUniqueSiteIds_Unordered()
    {
        $this->requestSet->setRequests(array(
            $this->buildRequest(1),
            $this->buildRequest(5),
            $this->buildRequest(1),
            $this->buildRequest(2),
            $this->buildRequest(2),
            $this->buildRequest(9),
        ));

        $this->assertEquals(array(1, 5, 2, 9), $this->requestSet->getAllSiteIdsWithinRequest());
    }

    /**
     * @param int $numRequests
     * @return Request[]
     */
    private function buildRequests($numRequests)
    {
        $requests = array();
        for ($index = 1; $index <= $numRequests; $index++) {
            $requests[] = $this->buildRequest($index);
        }
        return $requests;
    }

    private function buildRequest($idsite)
    {
        $request = new Request(array('idsite' => ('' . $idsite)));
        $request->setCurrentTimestamp($this->time);

        return $request;
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
