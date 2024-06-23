<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Matomo\Network\IPUtils;
use Piwik\Config;
use Piwik\Piwik;
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

    private $time;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2014-01-01 00:00:00');
        Fixture::createWebsite('2014-01-01 00:00:00');
        foreach (range(3, 14) as $idSite) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function setUp(): void
    {
        parent::setUp();

        Cache::deleteTrackerCache();

        $this->request = $this->buildRequest(array('idsite' => '1'));
        $this->time = 1416795617;
    }

    public function testGetVisitorIdNoData()
    {
        $request = $this->buildRequest(array());
        $this->assertFalse($request->getVisitorId());
    }

    public function testGetVisitorIdIdParam()
    {
        $request = $this->buildRequest(array('_id' => '1234567890ABCDEF'));
        $this->assertSame('1234567890abcdef', bin2hex($request->getVisitorId()));
    }

    public function testGetVisitorIdUserIdOverwritesVisitorId()
    {
        $request = $this->buildRequest(array('_id' => '1234567890ABCDEF', 'uid' => 'foo'));
        $this->assertSame('0beec7b5ea3f0fdb', bin2hex($request->getVisitorId()));
    }

    public function testGetVisitorIdNotOverwritesWhenDisabled()
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['enable_userid_overwrites_visitorid'] = 0;
        $config->Tracker = $tracker;
        $request = $this->buildRequest(array('_id' => '1234567890ABCDEF', 'uid' => 'foo'));
        $this->assertSame('1234567890abcdef', bin2hex($request->getVisitorId()));
    }

    public function testCdtShouldNotTrackTheRequestIfNotAuthenticatedAndTimestampIsNotRecent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Custom timestamp is 86500 seconds old');

        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 86500)));
        $request->setCurrentTimestamp($this->time);
        $this->assertSame($this->time, $request->getCurrentTimestamp());
    }

    private function setTrackerExcludedConfig($exclude)
    {
        $config  = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['exclude_requests'] = $exclude;
        $config->Tracker = $tracker;
    }

    public function testIsRequestExcludedNothingConfigured()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 86500)));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedNotValidExpression()
    {
        $this->setTrackerExcludedConfig('foo=bar');
        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedEmptyRightValue()
    {
        $this->setTrackerExcludedConfig('foo==');

        $request = $this->buildRequest(array('foo' => ''));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array());
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'b'));
        $this->assertFalse($request->isRequestExcluded());

        $this->setTrackerExcludedConfig('foo!=');

        $request = $this->buildRequest(array('foo' => ''));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array());
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'b'));
        $this->assertTrue($request->isRequestExcluded());
    }

    public function testIsRequestExcludedEquals()
    {
        $this->setTrackerExcludedConfig('foo==bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedNotEquals()
    {
        $this->setTrackerExcludedConfig('foo!=bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());
    }

    public function testIsRequestExcludedContains()
    {
        $this->setTrackerExcludedConfig('foo=@bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'fffbar1'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedNotContains()
    {
        $this->setTrackerExcludedConfig('foo!@bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'fffbar1'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'hello'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'ba'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());
    }

    public function testIsRequestExcludedStartsWith()
    {
        $this->setTrackerExcludedConfig('foo=^bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'fffbar1'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedEndsWith()
    {
        $this->setTrackerExcludedConfig('foo=$bar');

        $request = $this->buildRequest(array('foo' => 'bar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'bar1'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo' => 'fffbar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('foo1' => 'bar'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testIsRequestExcludedMultipleComparisons()
    {
        $this->setTrackerExcludedConfig('foo==test,bar==foo%2Cbar');

        $request = $this->buildRequest(array('foo' => 'test'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('bar' => 'foo,bar'));
        $this->assertTrue($request->isRequestExcluded());

        $request = $this->buildRequest(array('bar' => 'foo%2Cbar'));
        $this->assertFalse($request->isRequestExcluded());

        $request = $this->buildRequest(array('bar' => 'foo'));
        $this->assertFalse($request->isRequestExcluded());
    }

    public function testCdtShouldReturnTheCustomTimestampIfNotAuthenticatedButTimestampIsRecent()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 5)));
        $request->setCurrentTimestamp($this->time);

        $this->assertSame(($this->time - 5), $request->getCurrentTimestamp());
    }

    public function testCdtShouldReturnTheCustomTimestampIfAuthenticatedAndValid()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 86500)));
        $request->setCurrentTimestamp($this->time);
        $request->setIsAuthenticated();
        $this->assertSame(($this->time - 86500), $request->getCurrentTimestamp());
    }

    public function testCdtShouldReturnTheCustomTimestampIfTimestampIsInFuture()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time + 30800)));
        $request->setCurrentTimestamp($this->time);
        $this->assertSame($this->time, $request->getCurrentTimestamp());
    }

    public function testCdtShouldReturnTheCustomTimestampShouldUseStrToTimeIfItIsNotATime()
    {
        $request = $this->buildRequest(array('cdt' => '10 years ago'));
        $request->setCurrentTimestamp($this->time);
        $request->setIsAuthenticated();
        $this->assertNotSame($this->time, $request->getCurrentTimestamp());
        $this->assertNotEmpty($request->getCurrentTimestamp());
    }

    public function testGetIdSite()
    {
        $request = $this->buildRequest(array('idsite' => '14'));
        $this->assertSame(14, $request->getIdSite());
    }

    public function testGetIdSiteShouldNotThrowExceptionIfValueIsZero()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);
        $this->expectExceptionMessage('Invalid idSite: \'0\'');

        $request = $this->buildRequest(array('idsite' => '0'));
        $request->getIdSite();
    }

    public function testGetIdSiteShouldThrowExceptionIfValueIsLowerThanZero()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);
        $this->expectExceptionMessage('Invalid idSite: \'-1\'');

        $request = $this->buildRequest(array('idsite' => '-1'));
        $request->getIdSite();
    }

    public function testGetIpStringShouldDefaultToServerAddress()
    {
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $this->request->getIpString());
    }

    public function testGetIpStringShouldReturnCustomIpIfAuthenticated()
    {
        $request = $this->buildRequest(array('cip' => '192.192.192.192'));
        $request->setIsAuthenticated();
        $this->assertEquals('192.192.192.192', $request->getIpString());
    }

    public function testGetIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->assertEquals(IPUtils::stringToBinaryIP($ip), $this->request->getIp());
    }


    public function testIsAuthenticatedShouldBeNotAuthenticatedInTestsByDefault()
    {
        $this->assertFalse($this->request->isAuthenticated());
    }

    public function testIsAuthenticatedShouldBeAuthenticatedIfCheckIsDisabledInConfig()
    {
        $oldConfig = TrackerConfig::getConfigValue('tracking_requests_require_authentication');
        TrackerConfig::setConfigValue('tracking_requests_require_authentication', 0);

        $this->assertTrue($this->request->isAuthenticated());

        TrackerConfig::setConfigValue('tracking_requests_require_authentication', $oldConfig);
    }

    public function testIsAuthenticatedShouldReadTheIsAuthenticatedPropertyAndIgnoreACheck()
    {
        $this->assertFalse($this->request->isAuthenticated());
        $this->request->setIsAuthenticated();
        $this->assertTrue($this->request->isAuthenticated());
    }

    public function testIsAuthenticatedShouldWorkIfTokenIsCorrect()
    {
        $token = $this->createAdminUserForSite(2);

        $request = $this->buildRequestWithToken(array('idsite' => '1'), $token);
        $this->assertFalse($request->isAuthenticated());

        $request = $this->buildRequestWithToken(array('idsite' => '2'), $token);
        $this->assertTrue($request->isAuthenticated());
    }

    public function testIsAuthenticatedShouldAlwaysWorkForSuperUser()
    {
        Fixture::createSuperUser(false);
        $token = Fixture::getTokenAuth();

        $request = $this->buildRequestWithToken(array('idsite' => '1'), $token);
        $this->assertTrue($request->isAuthenticated());

        $request = $this->buildRequestWithToken(array('idsite' => '2'), $token);
        $this->assertTrue($request->isAuthenticated());
    }

    public function testAuthenticateSuperUserOrAdminShouldFailIfTokenIsEmpty()
    {
        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite('', 2);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite(null, 2);
        $this->assertFalse($isAuthenticated);
    }

    public function testAuthenticateSuperUserOrAdminShouldPostAuthInitEventIfTokenIsGiven()
    {
        $called = 0;
        Piwik::addAction('Request.initAuthenticationObject', function () use (&$called) {
            $called++;
        });

        Request::authenticateSuperUserOrAdminOrWrite('', 2);
        $this->assertSame(0, $called);

        Request::authenticateSuperUserOrAdminOrWrite('atoken', 2);
        $this->assertSame(1, $called);

        Request::authenticateSuperUserOrAdminOrWrite('anothertoken', 2);
        $this->assertSame(2, $called);

        Request::authenticateSuperUserOrAdminOrWrite(null, 2);
        $this->assertSame(2, $called);
    }

    public function testAuthenticateSuperUserOrAdminShouldNotBeAllowedToAccessSitesHavingInvalidId()
    {
        $token = $this->createAdminUserForSite(2);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, -2);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, 0);
        $this->assertFalse($isAuthenticated);
    }

    public function testAuthenticateSuperUserOrAdminShouldWorkIfTokenIsCorrect()
    {
        $token = $this->createAdminUserForSite(2);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, 1);
        $this->assertFalse($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, 2);
        $this->assertTrue($isAuthenticated);
    }

    public function testAuthenticateSuperUserOrAdminShouldAlwaysWorkForSuperUser()
    {
        Fixture::createSuperUser(false);
        $token = Fixture::getTokenAuth();

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, 1);
        $this->assertTrue($isAuthenticated);

        $isAuthenticated = Request::authenticateSuperUserOrAdminOrWrite($token, 2);
        $this->assertTrue($isAuthenticated);
    }

    private function createAdminUserForSite($idSite)
    {
        $login = 'myadmin';
        $passwordHash = UsersManager::getPasswordHash('password');

        $user = new Model();
        $token = $user->generateRandomTokenAuth();

        $user->addUser($login, $passwordHash, 'admin@piwik', '2014-01-01 00:00:00');
        $user->addUserAccess($login, 'admin', array($idSite));
        $user->addTokenAuth($login, $token, 'createAdminUserForSite', '2014-01-01 00:00:00');

        return $token;
    }

    public function testGetIdSiteShouldTriggerEventAndReturnThatIdSite()
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
    public function testInvalidCharacterRemovalForUtf8($url, $expectedUrl)
    {
        Config::getInstance()->database['charset'] = 'utf8';
        $request = $this->buildRequest(array('url' => $url));
        $this->assertEquals($expectedUrl, $request->getParam('url'));
    }

    /**
     * @group invalidChars
     * @dataProvider getInvalidCharacterUrls
     */
    public function test4ByteCharacterRemainForUtf8mb4($url, $expectedUrl)
    {
        Config::getInstance()->database['charset'] = 'utf8mb4';
        $request = $this->buildRequest(array('url' => $url));
        $this->assertEquals($url, $request->getParam('url'));
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

    public function testGetIdSiteShouldTriggerExceptionWhenSiteNotExists()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);
        $this->expectExceptionMessage('An unexpected website was found in the request: website id was set to \'155\'');

        $self = $this;
        Piwik::addAction('Tracker.Request.getIdSite', function (&$idSite, $params) use ($self) {
            $self->assertSame(14, $idSite);
            $self->assertEquals(array('idsite' => '14'), $params);
            $idSite = 155;
        });

        $this->buildRequest(array('idsite' => '14'))->getIdSite();
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
