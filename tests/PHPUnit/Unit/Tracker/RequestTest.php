<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Tracker;

use Piwik\Cookie;
use Piwik\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tests\Framework\TestCase\UnitTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;

/**
 * @group RequestSetTest
 * @group RequestSet
 * @group Tracker
 */
class RequestTest extends UnitTestCase
{
    /**
     * @var TestRequest
     */
    private $request;
    private $time;

    public function setUp()
    {
        parent::setUp();

        $this->time = 1416795617;
        $this->request = $this->buildRequest(array('idsite' => '1'));
    }

    public function test_getCurrentTimestamp_ShouldReturnTheSetTimestamp_IfNoCustomValueGiven()
    {
        $this->assertSame($this->time, $this->request->getCurrentTimestamp());
    }

    public function test_getCurrentTimestamp_ShouldReturnTheCurrentTimestamp_IfTimestampIsInvalid()
    {
        $request = $this->buildRequest(array('cdt' => '' . 5));
        $request->setIsAuthenticated();
        $this->assertSame($this->time, $request->getCurrentTimestamp());
    }

    public function test_cdt_ShouldReturnTheCurrentTimestamp_IfNotAuthenticatedAndTimestampIsNotRecent()
    {
        $request = $this->buildRequest(array('cdt' => '' . $this->time - 28800));
        $this->assertSame($this->time, $request->getCurrentTimestamp());
    }

    public function test_cdt_ShouldReturnTheCustomTimestamp_IfNotAuthenticatedButTimestampIsRecent()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 5)));

        $this->assertSame('' . ($this->time - 5), $request->getCurrentTimestamp());
    }

    public function test_cdt_ShouldReturnTheCustomTimestamp_IfAuthenticatedAndValid()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time - 28800)));
        $request->setIsAuthenticated();
        $this->assertSame('' . ($this->time - 28800), $request->getCurrentTimestamp());
    }

    public function test_cdt_ShouldReturnTheCustomTimestamp_IfTimestampIsInFuture()
    {
        $request = $this->buildRequest(array('cdt' => '' . ($this->time + 30800)));
        $this->assertSame($this->time, $request->getCurrentTimestamp());
    }

    public function test_cdt_ShouldReturnTheCustomTimestamp_ShouldUseStrToTime_IfItIsNotATime()
    {
        $request = $this->buildRequest(array('cdt' => '5 years ago'));
        $request->setIsAuthenticated();
        $this->assertNotSame($this->time, $request->getCurrentTimestamp());
        $this->assertNotEmpty($request->getCurrentTimestamp());
    }

    public function test_isEmptyRequest_ShouldReturnTrue_InCaseNoParamsSet()
    {
        $request = $this->buildRequest(array());
        $this->assertTrue($request->isEmptyRequest());
    }

    public function test_isEmptyRequest_ShouldReturnTrue_InCaseNullIsSet()
    {
        $request = $this->buildRequest(null);
        $this->assertTrue($request->isEmptyRequest());
    }

    public function test_isEmptyRequest_ShouldRecognizeEmptyRequest_EvenIfConstructorAddsAParam()
    {
        $_SERVER['HTTP_REFERER'] = 'http://www.example.com';

        $request = $this->buildRequest(array());
        $this->assertCount(1, $request->getParams());

        $this->assertTrue($request->isEmptyRequest());

        unset($_SERVER['HTTP_REFERER']);
    }

    public function test_isEmptyRequest_ShouldReturnFalse_InCaseAtLEastOneParamIssSet()
    {
        $request = $this->buildRequest(array('idsite' => 1));
        $this->assertFalse($request->isEmptyRequest());
    }

    public function test_getTokenAuth_shouldReturnDefaultValue_IfNoneSet()
    {
        $request = $this->buildRequest(array('idsite' => 1));
        $this->assertFalse($request->getTokenAuth());
    }

    public function test_getTokenAuth_shouldReturnSetTokenAuth()
    {
        $request = $this->buildRequestWithToken(array('idsite' => 1), 'myToken');
        $this->assertEquals('myToken', $request->getTokenAuth());
    }

    public function test_getForcedUserId_shouldReturnFalseByDefault()
    {
        $this->assertFalse($this->request->getForcedUserId());
    }

    public function test_getForcedUserId_shouldReturnCustomUserId_IfSet()
    {
        $request = $this->buildRequest(array('uid' => 'mytest'));
        $this->assertEquals('mytest', $request->getForcedUserId());
    }

    public function test_getForcedUserId_shouldReturnFalse_IfCustomUserIdIsEmpty()
    {
        $request = $this->buildRequest(array('uid' => ''));
        $this->assertFalse($request->getForcedUserId());
    }

    public function test_getDaysSinceFirstVisit_shouldReturnZeroIfNow()
    {
        $this->assertEquals(0.0, $this->request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceFirstVisit_ShouldNotReturnMinusValue()
    {
        $request = $this->buildRequest(array('_idts' => '' . ($this->time + 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(0.0, $request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceFirstVisit_TodayMinusHalfDay()
    {
        $request = $this->buildRequest(array('_idts' => '' . ($this->time - 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceFirstVisit_Yesterday()
    {
        $request = $this->buildRequest(array('_idts' => '' .($this->time - 86400)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceFirstVisit_12Days()
    {
        $request = $this->buildRequest(array('_idts' => '' . ($this->time - (86400 * 12))));
        $request->setIsAuthenticated();
        $this->assertEquals(12.0, $request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceFirstVisit_IfTimestampIsNotValidShouldIgnoreParam()
    {
        $request = $this->buildRequest(array('_idts' => '' . ($this->time - (86400 * 15 * 365))));
        $this->assertEquals(0.0, $request->getDaysSinceFirstVisit());
    }

    public function test_getDaysSinceLastOrder_shouldReturnZeroIfNow()
    {
        $this->assertEquals(0.0, $this->request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastOrder_ShouldNotReturnMinusValue()
    {
        $request = $this->buildRequest(array('_ects' => '' . ($this->time + 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(0.0, $request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastOrder_TodayMinusHalfDay()
    {
        $request = $this->buildRequest(array('_ects' => '' . ($this->time - 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastOrder_Yesterday()
    {
        $request = $this->buildRequest(array('_ects' => '' . ($this->time - 86400)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastOrder_12Days()
    {
        $request = $this->buildRequest(array('_ects' => '' . ($this->time - (86400 * 12))));
        $request->setIsAuthenticated();
        $this->assertEquals(12.0, $request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastOrder_ShouldIgnoreParamIfInvalid()
    {
        $request = $this->buildRequest(array('_ects' => 5));
        $this->assertFalse($request->getDaysSinceLastOrder());
    }

    public function test_getDaysSinceLastVisit_shouldReturnZeroIfNow()
    {
        $this->assertEquals(0.0, $this->request->getDaysSinceLastVisit());
    }

    public function test_getDaysSinceLastVisit_ShouldNotReturnMinusValue()
    {
        $request = $this->buildRequest(array('_viewts' => '' . ($this->time + 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(0.0, $request->getDaysSinceLastVisit());
    }

    public function test_getDaysSinceLastVisit_TodayMinusHalfDay()
    {
        $request = $this->buildRequest(array('_viewts' => '' . ($this->time - 43200)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceLastVisit());
    }

    public function test_getDaysSinceLastVisit_Yesterday()
    {
        $request = $this->buildRequest(array('_viewts' => '' . ($this->time - 86400)));
        $request->setIsAuthenticated();
        $this->assertEquals(1.0, $request->getDaysSinceLastVisit());
    }

    public function test_getDaysSinceLastVisit_12Days()
    {
        $request = $this->buildRequest(array('_viewts' => '' . ($this->time - (86400 * 12))));
        $request->setIsAuthenticated();
        $this->assertEquals(12.0, $request->getDaysSinceLastVisit());
    }

    public function test_getDaysSinceLastVisit_ShouldIgnoreParamIfInvalid()
    {
        $request = $this->buildRequest(array('_viewts' => '' . 5));
        $this->assertSame(0, $request->getDaysSinceLastVisit());
    }

    public function test_getGoalRevenue_ShouldReturnDefaultValue_IfNothingSet()
    {
        $this->assertFalse($this->request->getGoalRevenue(false));
    }

    public function test_getGoalRevenue_ShouldReturnParam_IfSet()
    {
        $request = $this->buildRequest(array('revenue' => '5.51'));
        $this->assertSame(5.51, $request->getGoalRevenue(false));
    }

    public function test_getUserIdHashed_shouldReturnSetTokenAuth()
    {
        $hash = $this->request->getUserIdHashed(1);

        $this->assertEquals('356a192b7913b04c', $hash);
        $this->assertSame(16, strlen($hash));
        $this->assertTrue(ctype_alnum($hash));

        $this->assertEquals('da4b9237bacccdf1', $this->request->getUserIdHashed(2));
    }

    public function test_getVisitCount_shouldReturnOne_IfNotSet()
    {
        $this->assertEquals(1, $this->request->getVisitCount());
    }

    public function test_getVisitCount_shouldReturnTheSetValue_IfHigherThanOne()
    {
        $request = $this->buildRequest(array('_idvc' => 13));
        $this->assertEquals(13, $request->getVisitCount());
    }

    public function test_getVisitCount_shouldReturnAtLEastOneEvenIfLowerValueIsSet()
    {
        $request = $this->buildRequest(array('_idvc' => 0));
        $this->assertEquals(1, $request->getVisitCount());

        $request = $this->buildRequest(array('_idvc' => -1));
        $this->assertEquals(1, $request->getVisitCount());
    }

    public function test_getLocalTime_shouldFallbackToCurrentDate_IfNoParamIsSet()
    {
        $this->assertEquals('02:20:17', $this->request->getLocalTime());
    }

    public function test_getLocalTime_shouldReturnAtLEastOneEvenIfLowerValueIsSet()
    {
        $request = $this->buildRequest(array('h' => 15, 'm' => 3, 's' => 4));
        $this->assertEquals('15:03:04', $request->getLocalTime());
    }

    public function test_getLocalTime_shouldFallbackToPartsOfCurrentDate()
    {
        $request = $this->buildRequest(array('h' => 5));
        $this->assertEquals('05:20:17', $request->getLocalTime());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested parameter myCustomFaKeParaM is not a known Tracking API Parameter
     */
    public function test_getParam_shouldThrowException_IfTryingToAccessInvalidParam()
    {
        $this->request->getParam('myCustomFaKeParaM');
    }

    public function test_getParam_aString()
    {
        $request = $this->buildRequest(array('url' => 'test'));
        $this->assertEquals('test', $request->getParam('url'));
    }

    public function test_getParam_aInt()
    {
        $request = $this->buildRequest(array('new_visit' => '12'));
        $this->assertSame(12, $request->getParam('new_visit'));
    }

    public function test_getPlugins_shouldReturnZeroForAllIfNothingGiven()
    {
        $expected = array_fill(0, 10, 0);

        $this->assertEquals($expected, $this->request->getPlugins());
    }

    public function test_getPlugins_shouldReturnAllOneIfAllGiven()
    {
        $plugins = array('fla', 'java', 'dir', 'qt', 'realp', 'pdf', 'wma', 'gears', 'ag', 'cookie');
        $request = $this->buildRequest(array_fill_keys($plugins, '1'));

        $this->assertEquals(array_fill(0, 10, 1), $request->getPlugins());
    }

    public function test_getPlugins_shouldDetectSome()
    {
        $plugins = array('fla' => 1, 'java', 'dir' => '1', 'qt' => '0', 'realp' => 0, 'gears', 'ag' => 1, 'cookie');
        $request = $this->buildRequest($plugins);

        $expected = array(1, 0, 1, 0, 0, 0, 0, 0, 1, 0);
        $this->assertEquals($expected, $request->getPlugins());
    }

    public function test_getPageGenerationTime_shouldDefaultToFalse_IfNotGiven()
    {
        $this->assertFalse($this->request->getPageGenerationTime());
    }

    public function test_getPageGenerationTime_shouldIgnoreAnyValueLowerThan0()
    {
        $request = $this->buildRequest(array('gt_ms' => '0'));
        $this->assertFalse($request->getPageGenerationTime());

        $request = $this->buildRequest(array('gt_ms' => '-5'));
        $this->assertFalse($request->getPageGenerationTime());
    }

    public function test_getPageGenerationTime_shouldIgnoreAnyValueThatIsTooHigh()
    {
        $request = $this->buildRequest(array('gt_ms' => '3600002'));
        $this->assertFalse($request->getPageGenerationTime());
    }

    public function test_getPageGenerationTime_shouldReturnAValidValue()
    {
        $request = $this->buildRequest(array('gt_ms' => '1942'));
        $this->assertSame(1942, $request->getPageGenerationTime());
    }

    public function test_truncateCustomVariable_shouldNotTruncateAnything_IfValueIsShortEnough()
    {
        $len = CustomVariables::getMaxLengthCustomVariables();
        $input = str_pad('test', $len - 2, 't');

        $result = Request::truncateCustomVariable($input);

        $this->assertSame($result, $input);
    }

    public function test_truncateCustomVariable_shouldActuallyTruncateTheValue()
    {
        $len = CustomVariables::getMaxLengthCustomVariables();
        $input = str_pad('test', $len + 2, 't');

        $this->assertGreaterThan(100, $len);

        $truncated = Request::truncateCustomVariable($input);

        $this->assertEquals(str_pad('test', $len, 't'), $truncated);
    }

    public function test_getUserAgent_ShouldReturnEmptyString_IfNoneIsSet()
    {
        $this->assertEquals('', $this->request->getUserAgent());
    }

    public function test_getUserAgent_ShouldDefaultToServerUa_IfPossibleAndNoneIsSet()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'MyUserAgent';
        $this->assertSame('MyUserAgent', $this->request->getUserAgent());
        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function test_getUserAgent_ShouldReturnTheUaFromParams_IfOneIsSet()
    {
        $request = $this->buildRequest(array('idsite' => '14', 'ua' => 'My Custom UA'));
        $this->assertSame('My Custom UA', $request->getUserAgent());
    }

    public function test_getBrowserLanguage_ShouldReturnACustomSetLangParam_IfOneIsSet()
    {
        $request = $this->buildRequest(array('lang' => 'CusToMLang'));
        $this->assertSame('CusToMLang', $request->getBrowserLanguage());
    }

    public function test_getBrowserLanguage_ShouldReturnADefaultLanguageInCaseNoneIsSet()
    {
        $envLanguage = getenv('LANG');
        putenv('LANG=en');

        $lang = $this->request->getBrowserLanguage();
        $this->assertNotEmpty($lang);
        $this->assertTrue(2 <= strlen($lang) && strlen($lang) <= 10);

        if ($envLanguage !== false) {
            putenv('LANG=' . $envLanguage);
        }
    }

    public function test_makeThirdPartyCookie_ShouldReturnAnInstanceOfCookie()
    {
        $cookie = $this->request->makeThirdPartyCookie();

        $this->assertTrue($cookie instanceof Cookie);
    }

    public function test_makeThirdPartyCookie_ShouldPreconfigureTheCookieInstance()
    {
        $cookie = $this->request->makeThirdPartyCookie();

        $this->assertCookieContains('COOKIE _pk_uid', $cookie);
        $this->assertCookieContains('expire: 1450750817', $cookie);
        $this->assertCookieContains('path: ,', $cookie);
    }

    private function assertCookieContains($needle, Cookie $cookie)
    {
        $this->assertContains($needle, $cookie . '');
    }

    public function test_getLocalTime()
    {
        $request = $this->buildRequest(array('h' => '12', 'm' => '34', 's' => '3'));
        $this->assertSame('12:34:03', $request->getLocalTime());


        $request = $this->buildRequest(array('h' => '23', 'm' => '59', 's' => '59'));
        $this->assertSame('23:59:59', $request->getLocalTime());
    }

    public function test_getLocalTime_shouldReturnValidTime_whenTimeWasInvalid()
    {
        $request = $this->buildRequest(array('h' => '26', 'm' => '60', 's' => '333'));
        $this->assertSame('00:00:00', $request->getLocalTime());

        $request = $this->buildRequest(array('h' => '-26', 'm' => '-60', 's' => '-333'));
        $this->assertSame('00:00:00', $request->getLocalTime());
    }

    public function test_getIdSite()
    {
        $request = $this->buildRequest(array('idsite' => '14'));
        $this->assertSame(14, $request->getIdSite());
    }

    /**
     * @expectedException \Piwik\Exception\UnexpectedWebsiteFoundException
     * @expectedExceptionMessage Invalid idSite: '0'
     */
    public function test_getIdSite_shouldThrowException_IfValueIsZero()
    {
        $request = $this->buildRequest(array('idsite' => '0'));
        $request->getIdSite();
    }

    /**
     * @expectedException \Piwik\Exception\UnexpectedWebsiteFoundException
     * @expectedExceptionMessage Invalid idSite: '-1'
     */
    public function test_getIdSite_shouldThrowException_IfValueIsLowerThanZero()
    {
        $request = $this->buildRequest(array('idsite' => '-1'));
        $request->getIdSite();
    }

    public function test_getIpString_ShouldDefaultToServerAddress()
    {
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $this->request->getIpString());
    }

    public function test_getIpString_ShouldDefaultToServerAddress_IfCustomIpIsSetButNotAuthenticated()
    {
        $request = $this->buildRequest(array('cip' => '192.192.192.192'));
        $this->assertEquals($_SERVER['REMOTE_ADDR'], $request->getIpString());
    }

    public function test_getIpString_ShouldReturnCustomIp_IfAuthenticated()
    {
        $request = $this->buildRequest(array('cip' => '192.192.192.192'));
        $request->setIsAuthenticated();
        $this->assertEquals('192.192.192.192', $request->getIpString());
    }

    public function test_getIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->assertEquals(IPUtils::stringToBinaryIP($ip), $this->request->getIp());
    }

    public function test_getCookieName_ShouldReturnConfigValue()
    {
        $this->assertEquals('_pk_uid', $this->request->getCookieName());
    }

    public function test_getCookieExpire_ShouldReturnConfigValue()
    {
        $this->assertEquals($this->time + (60 * 60 * 24 * 393), $this->request->getCookieExpire());
    }

    public function test_getCookiePath_ShouldBeEmptyByDefault()
    {
        $this->assertEquals('', $this->request->getCookiePath());
    }

    public function test_getCookiePath_ShouldReturnConfigValue()
    {
        $oldPath = TrackerConfig::getConfigValue('cookie_path');
        TrackerConfig::setConfigValue('cookie_path', 'test');

        $this->assertEquals('test', $this->request->getCookiePath());

        TrackerConfig::setConfigValue('cookie_path', $oldPath);
    }

    private function buildRequest($params)
    {
        $request = new TestRequest($params);
        $request->setCurrentTimestamp($this->time);

        return $request;
    }

    private function buildRequestWithToken($params, $token)
    {
        return new TestRequest($params, $token);
    }
}

class TestRequest extends Request
{
    public function getCookieName()
    {
        return parent::getCookieName();
    }

    public function getCookieExpire()
    {
        return parent::getCookieExpire();
    }

    public function getCookiePath()
    {
        return parent::getCookiePath();
    }

    public function makeThirdPartyCookie()
    {
        return parent::makeThirdPartyCookie();
    }

    public function setIsAuthenticated()
    {
        $this->isAuthenticated = true;
    }
}
