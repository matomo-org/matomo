<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\TrackerConfig;

/**
 * @group RequestTest
 * @group Request
 * @group Tracker
 */
class RequestTest extends IntegrationTestCase
{
    /**
     * @var TestRequest
     */
    private $request;

    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        Cache::deleteTrackerCache();

        $this->request = $this->buildRequest(array('idsite' => '1'));
    }

    public function test_getCustomVariablesInVisitScope_ShouldReturnNoCustomVars_IfNoWerePassedInParams()
    {
        $this->assertEquals(array(), $this->request->getCustomVariablesInVisitScope());
    }

    public function test_getCustomVariablesInVisitScope_ShouldReturnNoCustomVars_IfPassedParamIsNotAnArray()
    {
        $this->assertCustomVariablesInVisitScope(array(), '{"mykey":"myval"}');
    }

    public function test_getCustomVariablesInVisitScope_ShouldReturnCustomVars_IfTheyAreValid()
    {
        $customVars = $this->buildCustomVars(array('mykey' => 'myval', 'test' => 'value'));
        $expected   = $this->buildExpectedCustomVars(array('mykey' => 'myval', 'test' => 'value'));

        $this->assertCustomVariablesInVisitScope($expected, $customVars);
    }

    public function test_getCustomVariablesInVisitScope_ShouldIgnoreIndexesLowerThan1()
    {
        $customVars = array(
            array('mykey', 'myval'),
            array('test', 'value'),
        );
        $expected   = $this->buildExpectedCustomVars(array('test' => 'value'));

        $this->assertCustomVariablesInVisitScope($expected, json_encode($customVars));
    }

    public function test_getCustomVariablesInVisitScope_ShouldTruncateValuesIfTheyAreTooLong()
    {
        $maxLen = CustomVariables::getMaxLengthCustomVariables();

        $customVars = $this->buildCustomVars(array(
            'mykey' => 'myval',
            'test'  => str_pad('test', $maxLen + 5, 't'),
        ));
        $expected = $this->buildExpectedCustomVars(array(
            'mykey' => 'myval',
            'test'  => str_pad('test', $maxLen, 't'),
        ));

        $this->assertCustomVariablesInVisitScope($expected, $customVars);
    }

    public function test_getCustomVariablesInVisitScope_ShouldIgnoreVarsThatDoNotHaveKeyAndValue()
    {
        $customVars = array(
            1 => array('mykey', 'myval'),
            2 => array('test'),
        );
        $expected = $this->buildExpectedCustomVars(array('mykey' => 'myval'));

        $this->assertCustomVariablesInVisitScope($expected, json_encode($customVars));
    }

    public function test_getCustomVariablesInVisitScope_ShouldSetDefaultValueToEmptyStringAndHandleOtherTypes()
    {
        $input = array(
            'myfloat'  => 5.55,
            'myint'    => 53,
            'mystring' => '',
        );
        $customVars = $this->buildCustomVars($input);
        $expected   = $this->buildExpectedCustomVars($input);

        $this->assertCustomVariablesInVisitScope($expected, $customVars);
    }

    public function test_getCustomVariablesInPageScope_ShouldReturnNoCustomVars_IfNoWerePassedInParams()
    {
        $this->assertEquals(array(), $this->request->getCustomVariablesInPageScope());
    }

    public function test_getCustomVariablesInPageScope_ShouldReturnNoCustomVars_IfPassedParamIsNotAnArray()
    {
        $this->assertCustomVariablesInPageScope(array(), '{"mykey":"myval"}');
    }

    public function test_getCustomVariablesInPageScope_ShouldReturnCustomVars_IfTheyAreValid()
    {
        $customVars = $this->buildCustomVars(array('mykey' => 'myval', 'test' => 'value'));
        $expected   = $this->buildExpectedCustomVars(array('mykey' => 'myval', 'test' => 'value'));

        $this->assertCustomVariablesInPageScope($expected, $customVars);
    }

    public function test_getCustomVariablesInPageScope_ShouldIgnoreIndexesLowerThan1()
    {
        $customVars = array(
            array('mykey', 'myval'),
            array('test', 'value'),
        );
        $expected   = $this->buildExpectedCustomVars(array('test' => 'value'));

        $this->assertCustomVariablesInPageScope($expected, json_encode($customVars));
    }

    public function test_getCustomVariablesInPageScope_ShouldTruncateValuesIfTheyAreTooLong()
    {
        $maxLen = CustomVariables::getMaxLengthCustomVariables();

        $customVars = $this->buildCustomVars(array(
            'mykey' => 'myval',
            'test'  => str_pad('test', $maxLen + 5, 't'),
        ));
        $expected = $this->buildExpectedCustomVars(array(
            'mykey' => 'myval',
            'test'  => str_pad('test', $maxLen, 't'),
        ));

        $this->assertCustomVariablesInPageScope($expected, $customVars);
    }

    public function test_getCustomVariablesInPageScope_ShouldIgnoreVarsThatDoNotHaveKeyAndValue()
    {
        $customVars = array(
            1 => array('mykey', 'myval'),
            2 => array('test'),
        );
        $expected = $this->buildExpectedCustomVars(array('mykey' => 'myval'));

        $this->assertCustomVariablesInPageScope($expected, json_encode($customVars));
    }

    public function test_getCustomVariablesInPageScope_ShouldSetDefaultValueToEmptyStringAndHandleOtherTypes()
    {
        $input = array(
            'myfloat'  => 5.55,
            'myint'    => 53,
            'mystring' => '',
        );
        $customVars = $this->buildCustomVars($input);
        $expected   = $this->buildExpectedCustomVars($input);

        $this->assertCustomVariablesInPageScope($expected, $customVars);
    }

    public function test_isAuthenticated_ShouldBeNotAuthenticatedInTestsByDefault()
    {
        $this->assertFalse($this->request->isAuthenticated());
    }

    public function test_isAuthenticated_ShouldBeAuthenticatedIfCheckIsDisabledInConfig()
    {
        $oldConfig = TrackerConfig::getConfigValue('tracking_requests_require_authentication');
        TrackerConfig::setConfigValue('tracking_requests_require_authentication', 0);

        $this->assertTrue($this->request->isAuthenticated());

        TrackerConfig::setConfigValue('tracking_requests_require_authentication', $oldConfig);
    }

    public function test_isAuthenticated_ShouldReadTheIsAuthenticatedPropertyAndIgnoreACheck()
    {
        $this->assertFalse($this->request->isAuthenticated());
        $this->request->setIsAuthenticated();
        $this->assertTrue($this->request->isAuthenticated());
    }

    public function test_isAuthenticated_ShouldWorkIfTokenIsCorrect()
    {
        $token = $this->createAdminUserForSite(2);

        $request = $this->buildRequestWithToken(array('idsite' => '1'), $token);
        $this->assertFalse($request->isAuthenticated());

        $request = $this->buildRequestWithToken(array('idsite' => '2'), $token);
        $this->assertTrue($request->isAuthenticated());
    }

    public function test_isAuthenticated_ShouldAlwaysWorkForSuperUser()
    {
        Fixture::createSuperUser(false);
        $token = Fixture::getTokenAuth();

        $request = $this->buildRequestWithToken(array('idsite' => '1'), $token);
        $this->assertTrue($request->isAuthenticated());

        $request = $this->buildRequestWithToken(array('idsite' => '2'), $token);
        $this->assertTrue($request->isAuthenticated());
    }

    public function test_authenticateSuperUserOrAdmin_ShouldFailIfTokenIsEmpty()
    {
        $isAuthenticated = Request::authenticateSuperUserOrAdmin('', 2);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin(null, 2);
        $this->assertFalse($isAuthenticated);
    }

    public function test_authenticateSuperUserOrAdmin_ShouldPostAuthInitEvent_IfTokenIsGiven()
    {
        $called = 0;
        Piwik::addAction('Request.initAuthenticationObject', function () use (&$called) {
            $called++;
        });

        Request::authenticateSuperUserOrAdmin('', 2);
        $this->assertSame(0, $called);

        Request::authenticateSuperUserOrAdmin('atoken', 2);
        $this->assertSame(1, $called);

        Request::authenticateSuperUserOrAdmin('anothertoken', 2);
        $this->assertSame(2, $called);

        Request::authenticateSuperUserOrAdmin(null, 2);
        $this->assertSame(2, $called);
    }

    public function test_authenticateSuperUserOrAdmin_ShouldNotBeAllowedToAccessSitesHavingInvalidId()
    {
        $token = $this->createAdminUserForSite(2);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, -2);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, 0);
        $this->assertFalse($isAuthenticated);
    }

    public function test_authenticateSuperUserOrAdmin_ShouldWorkIfTokenIsCorrect()
    {
        $token = $this->createAdminUserForSite(2);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, 1);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, 2);
        $this->assertTrue($isAuthenticated);
    }

    public function test_authenticateSuperUserOrAdmin_ShouldAlwaysWorkForSuperUser()
    {
        Fixture::createSuperUser(false);
        $token = Fixture::getTokenAuth();

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, 1);
        $this->assertTrue($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdmin($token, 2);
        $this->assertTrue($isAuthenticated);
    }

    private function createAdminUserForSite($idSite)
    {
        $login = 'myadmin';
        $passwordHash = UsersManager::getPasswordHash('password');

        $token = API::getInstance()->getTokenAuth($login, $passwordHash);

        $user = new Model();
        $user->addUser($login, $passwordHash, 'admin@piwik', 'alias', $token, '2014-01-01 00:00:00');
        $user->addUserAccess($login, 'admin', array($idSite));

        return $token;
    }

    public function test_internalBuildExpectedCustomVars()
    {
        $this->assertEquals(array(), $this->buildExpectedCustomVars(array()));

        $this->assertEquals(array('custom_var_k1' => 'key', 'custom_var_v1' => 'val'),
                            $this->buildExpectedCustomVars(array('key' => 'val')));

        $this->assertEquals(array(
            'custom_var_k1' => 'key', 'custom_var_v1' => 'val',
            'custom_var_k2' => 'key2', 'custom_var_v2' => 'val2',
        ), $this->buildExpectedCustomVars(array('key' => 'val', 'key2' => 'val2')));
    }

    public function test_internalBuildCustomVars()
    {
        $this->assertEquals('[]', $this->buildCustomVars(array()));

        $this->assertEquals('{"1":["key","val"]}',
                            $this->buildCustomVars(array('key' => 'val')));

        $this->assertEquals('{"1":["key","val"],"2":["key2","val2"]}',
                            $this->buildCustomVars(array('key' => 'val', 'key2' => 'val2')));
    }

    public function test_getIdSite_shouldTriggerEventAndReturnThatIdSite()
    {
        $self = $this;
        Piwik::addAction('Tracker.Request.getIdSite', function (&$idSite, $params) use ($self) {
            $self->assertSame(14, $idSite);
            $self->assertEquals(array('idsite' => '14'), $params);
            $idSite = 12;
        });

        $request = $this->buildRequest(array('idsite' => '14'));
        $this->assertSame(12, $request->getIdSite());
    }

    /**
     * @group invalidChars
     * @dataProvider getInvalidCharacterUrls
     */
    public function testInvalidCharacterRemoval($url, $expectedUrl)
    {
        $request = $this->buildRequest(array('url' => $url));
        $this->assertEquals($expectedUrl, $request->getParam('url'));
    }

    public function getInvalidCharacterUrls()
    {
        return array(
            // urls with valid chars
            array("http://www.my.url", 'http://www.my.url'),
            array("http://www.my.url/ꟽ碌㒧䊶亄ﶆⅅขκもኸόσशμεޖृ", 'http://www.my.url/ꟽ碌㒧䊶亄ﶆⅅขκもኸόσशμεޖृ'), // various foreign chars
            array("http://www.my.url/‱©®↙⋗♤㎧￭", 'http://www.my.url/‱©®↙⋗♤㎧￭'), // various symbols
            array("http://www.my.url/\x39\xE2\x83\xA3", "http://www.my.url/\x39\xE2\x83\xA3"), // digit six + combining enclosing keycap

            // urls with 4byte chars
            array("http://www.my.url/test-article-\xF3\xA0\x81\xBEa", 'http://www.my.url/test-article-�a'), // tag tilde
            array("http://www.my.url/test-article-\xF0\x9F\x98\x81", 'http://www.my.url/test-article-�'), // emoji: grinning face with smiling eyes
            array("http://www.my.url/?param=val𠱸ue", 'http://www.my.url/?param=val�ue'),
            array("http://www.my.url/\xF0\x9F\x87\xB0\xF0\x9F\x87\xB7", 'http://www.my.url/��'), // regional indicator symbol letter k + regional indicator symbol letter r
        );
    }

    private function assertCustomVariablesInVisitScope($expectedCvars, $cvarsJsonEncoded)
    {
        $request = $this->buildRequest(array('_cvar' => $cvarsJsonEncoded));
        $this->assertEquals($expectedCvars, $request->getCustomVariablesInVisitScope());
    }

    private function assertCustomVariablesInPageScope($expectedCvars, $cvarsJsonEncoded)
    {
        $request = $this->buildRequest(array('cvar' => $cvarsJsonEncoded));
        $this->assertEquals($expectedCvars, $request->getCustomVariablesInPageScope());
    }

    private function buildExpectedCustomVars($customVars)
    {
        $vars  = array();
        $index = 1;

        foreach ($customVars as $key => $value) {
            $vars['custom_var_k' . $index] = $key;
            $vars['custom_var_v' . $index] = $value;
            $index++;
        }

        return $vars;
    }

    private function buildCustomVars($customVars)
    {
        $vars  = array();
        $index = 1;

        foreach ($customVars as $key => $value) {
            $vars[$index] = array($key, $value);
            $index++;
        }

        return json_encode($vars);
    }

    private function buildRequest($params)
    {
        return new TestRequest($params);
    }

    private function buildRequestWithToken($params, $token)
    {
        return new TestRequest($params, $token);
    }
}

class TestRequest extends Request
{
    public function setIsAuthenticated()
    {
        $this->isAuthenticated = true;
    }
}
