<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestSet;

/**
 * @group RequestSetTest
 * @group Tracker
 */
class RequestSetTest extends UnitTestCase
{
    /**
     * @var TestRequestSet
     */
    private $requestSet;
    private $time;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestSet = $this->createRequestSet();
        $this->time = 1693386000;
    }

    private function createRequestSet()
    {
        return new TestRequestSet();
    }

    public function testInternalBuildRequestShoulBuildOneRequest()
    {
        $request = new Request(array('idsite' => '2'));
        $request->setCurrentTimestamp($this->time);

        $this->assertEquals($request, $this->buildRequest(2));
    }

    public function testInternalBuildRequestsShoulBuildASetOfRequests()
    {
        $this->assertEquals(array(), $this->buildRequests(0));

        $this->assertEquals(array($this->buildRequest(1)), $this->buildRequests(1));

        $this->assertEquals(array(
            $this->buildRequest(1),
            $this->buildRequest(2),
            $this->buildRequest(3)
        ), $this->buildRequests(3));
    }

    public function testGetRequestsShouldReturnEmptyArrayIfThereAreNoRequestsInitializedYet()
    {
        $this->assertEquals(array(), $this->requestSet->getRequests());
    }

    public function testSetRequestsShouldNotFailIfEmptyArrayGiven()
    {
        $this->requestSet->setRequests(array());
        $this->assertEquals(array(), $this->requestSet->getRequests());
    }

    public function testSetRequestsShouldSetAndOverwriteRequests()
    {
        $this->requestSet->setRequests($this->buildRequests(3));
        $this->assertEquals($this->buildRequests(3), $this->requestSet->getRequests());

        // overwrite
        $this->requestSet->setRequests($this->buildRequests(5));
        $this->assertEquals($this->buildRequests(5), $this->requestSet->getRequests());

        // overwrite
        $this->requestSet->setRequests($this->buildRequests(1));
        $this->assertEquals($this->buildRequests(1), $this->requestSet->getRequests());

        // clear
        $this->requestSet->setRequests(array());
        $this->assertEquals(array(), $this->requestSet->getRequests());
    }

    public function testSetRequestsShouldConvertNonRequestInstancesToARequestInstance()
    {
        $requests = array(
            $this->buildRequest(5),
            array('idsite' => 9),
            $this->buildRequest(2),
            array('idsite' => 3),
            $this->buildRequest(6)
        );

        $this->requestSet->setRequests($requests);

        $setRequests = $this->requestSet->getRequests();
        $this->assertEquals($this->buildRequest(5), $setRequests[0]);
        $this->assertEquals($this->buildRequest(2), $setRequests[2]);
        $this->assertEquals($this->buildRequest(6), $setRequests[4]);

        $this->assertTrue($setRequests[1] instanceof Request);
        $this->assertEquals(array('idsite' => 9), $setRequests[1]->getParams());

        $this->assertTrue($setRequests[3] instanceof Request);
        $this->assertEquals(array('idsite' => 3), $setRequests[3]->getParams());

        $this->assertCount(5, $setRequests);
    }

    public function testSetRequestsShouldIgnoreEmptyRequestsButNotArrays()
    {
        $requests = array(
            $this->buildRequest(5),
            null,
            $this->buildRequest(2),
            0,
            $this->buildRequest(6),
            array()
        );

        $this->requestSet->setRequests($requests);

        $expected = array($this->buildRequest(5), $this->buildRequest(2), $this->buildRequest(6), new Request(array()));
        $this->assertEquals($expected, $this->requestSet->getRequests());
    }

    public function testGetNumberOfRequestsShouldReturnZeroIfNothingSet()
    {
        $this->assertEquals(0, $this->requestSet->getNumberOfRequests());
    }

    public function testGetNumberOfRequestsShouldReturnNumberOfRequests()
    {
        $this->requestSet->setRequests($this->buildRequests(3));
        $this->assertSame(3, $this->requestSet->getNumberOfRequests());

        $this->requestSet->setRequests($this->buildRequests(5));
        $this->assertSame(5, $this->requestSet->getNumberOfRequests());

        $this->requestSet->setRequests($this->buildRequests(1));
        $this->assertSame(1, $this->requestSet->getNumberOfRequests());
    }

    public function testHasRequestsShouldReturnFalseIfNotInitializedYetOrNoDataSet()
    {
        $this->assertFalse($this->requestSet->hasRequests());

        $this->requestSet->setRequests(array());
        $this->assertFalse($this->requestSet->hasRequests());
    }

    public function testHasRequestsShouldReturnTrueIfAtLeastOneRequestIsSet()
    {
        $this->assertFalse($this->requestSet->hasRequests());

        $this->requestSet->setRequests($this->buildRequests(1));
        $this->assertTrue($this->requestSet->hasRequests());

        $this->requestSet->setRequests($this->buildRequests(5));
        $this->assertTrue($this->requestSet->hasRequests());

        $this->requestSet->setRequests(array(null, 0));
        $this->assertFalse($this->requestSet->hasRequests());
    }

    public function testGetTokenAuthShouldReturnFalseIfNoTokenIsSetAndNoRequestParam()
    {
        $this->assertFalse($this->requestSet->getTokenAuth());
    }

    public function testGetTokenAuthSetTokenAuthShouldOverwriteTheToken()
    {
        $this->requestSet->setTokenAuth('MKyKTokenTestIn');

        $this->assertEquals('MKyKTokenTestIn', $this->requestSet->getTokenAuth());
    }

    public function testGetTokenAuthSetTokenAuthShouldBePossibleToClearASetToken()
    {
        $this->requestSet->setTokenAuth('MKyKTokenTestIn');
        $this->assertNotEmpty($this->requestSet->getTokenAuth());

        $this->requestSet->setTokenAuth(null);
        $this->assertFalse($this->requestSet->getTokenAuth()); // does now fallback to get param
    }

    public function testGetTokenAuthShouldFallbackToRequestParamIfNoTokenSet()
    {
        $_GET['token_auth'] = 'MyTokenAuthTest';

        $this->assertSame('MyTokenAuthTest', $this->requestSet->getTokenAuth());

        unset($_GET['token_auth']);
    }

    public function testGetEnvironmentShouldReturnCurrentServerVar()
    {
        $this->assertEquals(array(
            'server' => $_SERVER,
            'cookie' => $_COOKIE
        ), $this->requestSet->getEnvironment());
    }

    public function testIntertnalFakeEnvironmentShouldActuallyReturnAValue()
    {
        $myEnv = $this->getFakeEnvironment();
        self::assertIsArray($myEnv);
        $this->assertNotEmpty($myEnv);
    }

    public function testSetEnvironmentShouldOverwriteAnEnvironment()
    {
        $this->requestSet->setEnvironment($this->getFakeEnvironment());

        $this->assertEquals($this->getFakeEnvironment(), $this->requestSet->getEnvironment());
    }

    public function testRestoreEnvironmentShouldRestoreAPreviouslySetEnvironment()
    {
        $serverBackup = $_SERVER;
        $cookieBackup = $_COOKIE;

        $this->requestSet->setEnvironment($this->getFakeEnvironment());
        $this->requestSet->restoreEnvironment();

        $this->assertEquals(array('mytest' => 'test'), $_SERVER);
        $this->assertEquals(array('mytest2' => 'test2'), $_COOKIE);

        $_SERVER = $serverBackup;
        $_COOKIE = $cookieBackup;
    }

    public function testRememberEnvironmentShouldSaveCurrentEnvironment()
    {
        $expected = array('server' => $_SERVER, 'cookie' => $_COOKIE);

        $this->requestSet->rememberEnvironment();

        $this->assertEquals($expected, $this->requestSet->getEnvironment());

        // should not change anything
        $this->requestSet->restoreEnvironment();
        $this->assertEquals($expected['server'], $_SERVER);
        $this->assertEquals($expected['cookie'], $_COOKIE);
    }

    public function testGetStateShouldReturnCurrentStateOfRequestSet()
    {
        $this->requestSet->setRequests($this->buildRequests(2));
        $this->requestSet->setTokenAuth('mytoken');

        $state = $this->requestSet->getState();

        $expectedKeys = array('requests', 'env', 'tokenAuth', 'time');
        $this->assertEquals($expectedKeys, array_keys($state));

        $expectedRequests = array(
            array('idsite' => 1),
            array('idsite' => 2)
        );

        $this->assertEquals($expectedRequests, $state['requests']);
        $this->assertEquals('mytoken', $state['tokenAuth']);
        $this->assertTrue(is_numeric($state['time']));
        $this->assertEquals(array('server' => $_SERVER, 'cookie' => $_COOKIE), $state['env']);
    }

    public function testGetStateShouldRememberAnyAddedParamsFromRequestConstructor()
    {
        $_SERVER['HTTP_REFERER'] = 'test';

        $requests = $this->buildRequests(1);

        $this->requestSet->setRequests($requests);
        $this->requestSet->setTokenAuth('mytoken');

        $state = $this->requestSet->getState();

        unset($_SERVER['HTTP_REFERER']);

        $expectedRequests = array(
            array('idsite' => 1)
        );

        $this->assertEquals($expectedRequests, $state['requests']);

        // the actual params include an added urlref param which should NOT be in the state. otherwise we cannot detect empty requests etc
        $this->assertEquals(array('idsite' => 1, 'url' => 'test'), $requests[0]->getParams());
    }

    public function testRestoreStateShouldRestoreRequestSet()
    {
        $serverBackup = $_SERVER;

        $state = array(
            'requests' => array(array('idsite' => 1), array('idsite' => 2), array('idsite' => 3)),
            'time' => $this->time,
            'tokenAuth' => 'tokenAuthRestored',
            'env' => $this->getFakeEnvironment()
        );

        $this->requestSet->restoreState($state);

        $this->assertEquals($this->getFakeEnvironment(), $this->requestSet->getEnvironment());
        $this->assertEquals('tokenAuthRestored', $this->requestSet->getTokenAuth());

        $expectedRequests = array(
            new Request(array('idsite' => 1), 'tokenAuthRestored'),
            new Request(array('idsite' => 2), 'tokenAuthRestored'),
            new Request(array('idsite' => 3), 'tokenAuthRestored'),
        );
        $expectedRequests[0]->setCurrentTimestamp($this->time);
        $expectedRequests[1]->setCurrentTimestamp($this->time);
        $expectedRequests[2]->setCurrentTimestamp($this->time);

        $requests = $this->requestSet->getRequests();
        $this->assertEquals($expectedRequests, $requests);

        // verify again just to be sure (only first one)
        $this->assertEquals('tokenAuthRestored', $requests[0]->getTokenAuth());
        $this->assertEquals($this->time, $requests[0]->getCurrentTimestamp());

        // should not restoreEnvironment, only set the environment
        $this->assertSame($serverBackup, $_SERVER);
    }

    public function testRestoreStateIfRequestWasEmptyShouldBeStillEmptyWhenRestored()
    {
        $_SERVER['HTTP_REFERER'] = 'test';

        $this->requestSet->setRequests(array(new Request(array())));
        $state = $this->requestSet->getState();

        $requestSet = $this->createRequestSet();
        $requestSet->restoreState($state);

        unset($_SERVER['HTTP_REFERER']);

        $requests = $requestSet->getRequests();
        $this->assertTrue($requests[0]->isEmptyRequest());
    }

    public function testRestoreStateShouldResetTheStoredEnvironmentBeforeRestoringRequests()
    {
        $this->requestSet->setRequests(array(new Request(array())));
        $state = $this->requestSet->getState();
        $state['env']['server']['HTTP_REFERER'] = 'mytesturl';

        $requestSet = $this->createRequestSet();
        $requestSet->restoreState($state);

        $requests = $requestSet->getRequests();
        $this->assertTrue($requests[0]->isEmptyRequest());
        $this->assertEquals(array('url' => 'mytesturl'), $requests[0]->getParams());
        $this->assertTrue(empty($_SERVER['HTTP_REFERER']));
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

    private function getFakeEnvironment()
    {
        return array('server' => array('mytest' => 'test'), 'cookie' => array('mytest2' => 'test2'));
    }
}

class TestRequestSet extends RequestSet
{
    public function getAllSiteIdsWithinRequest()
    {
        return parent::getAllSiteIdsWithinRequest();
    }

    public function getEnvironment()
    {
        return parent::getEnvironment();
    }
}
