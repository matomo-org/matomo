<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\JsTrackerInstallCheck\tests\Integration;

use Piwik\Date;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;

/**
 * @group JsTrackerInstallCheck
 * @group Plugins
 * @group JsTrackerInstallCheckTest
 */
class JsTrackerInstallCheckTest extends JsTrackerInstallCheckIntegrationTestCase
{
    public function testIsExcludedVisitNoParams()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(false);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitEmptyParam()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => '']);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitNoOption()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => 'abc123'], $this->idSite1);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
    }

    public function testIsExcludedVisitWrongNonce()
    {
        $isExcluded = false;
        $nonce = $this->createNonceOption($this->idSite1);
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => 'abc123'], $this->idSite1);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
        $this->assertFalse($this->isNonceForSiteSuccessFul($this->idSite1));
    }

    public function testIsExcludedVisitExpiredNonce()
    {
        $isExcluded = false;
        $nonce = $this->createNonceOption($this->idSite1);
        // Update the nonce with an expired time
        $this->setNonceCheckTimestamp($this->idSite1, Date::getNowTimestamp() - (JsTrackerInstallCheck::MAX_NONCE_AGE_SECONDS + 5));
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => $nonce], $this->idSite1);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
        $this->assertFalse($this->isNonceForSiteSuccessFul($this->idSite1));
    }

    public function testIsExcludedVisit()
    {
        $isExcluded = false;
        $nonce = $this->createNonceOption($this->idSite1);
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => $nonce], $this->idSite1);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
        $this->assertTrue($this->isNonceForSiteSuccessFul($this->idSite1));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonExistantNonce()
    {
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1));
    }

    public function testCheckForJsTrackerInstallTestSuccessFalse()
    {
        $this->createNonceOption($this->idSite1);
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1));
    }

    public function testCheckForJsTrackerInstallTestSuccess()
    {
        $nonce = $this->createNonceOption($this->idSite1);
        $this->setNonceCheckAsSuccessful($this->idSite1);
        $this->assertTrue($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonceProvided()
    {
        $nonce = $this->createNonceOption($this->idSite1);
        $this->setNonceCheckAsSuccessful($this->idSite1);
        $this->assertTrue($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1, $nonce));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonceDifferent()
    {
        $this->createNonceOption($this->idSite1);
        $this->setNonceCheckAsSuccessful($this->idSite1);
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1, 'abc123'));
    }

    public function testInitiateJsTrackerInstallTest()
    {
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('nonce', $decodedOption);
        $this->assertSame($result['nonce'], $decodedOption['nonce']);
        $this->assertStringContainsString($decodedOption['nonce'], $result['url']);
        $this->assertSame('http://piwik.net?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $decodedOption['nonce'], $result['url']);
    }

    public function testInitiateJsTrackerInstallTestProvideUrl()
    {
        $testUrl = 'https://some-test-site.com?test=1';
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1, $testUrl);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('nonce', $decodedOption);
        $this->assertSame($result['nonce'], $decodedOption['nonce']);
        $this->assertSame($testUrl . '&' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $decodedOption['nonce'], $result['url']);
    }

    public function testInitiateJsTrackerInstallTestProvideInvalidUrl()
    {
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1, 'abc123');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('nonce', $decodedOption);
        $this->assertSame($result['nonce'], $decodedOption['nonce']);
        $this->assertStringContainsString($decodedOption['nonce'], $result['url']);
        $this->assertSame('http://piwik.net?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $decodedOption['nonce'], $result['url']);
    }

    public function testInitiateJsTrackerInstallTestReuseNonce()
    {
        $nonce = $this->createNonceOption($this->idSite1);
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame($nonce, $result['nonce']);
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('nonce', $decodedOption);
        $this->assertSame($nonce, $decodedOption['nonce']);
        $this->assertStringContainsString($nonce, $result['url']);
        $this->assertSame('http://piwik.net?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $nonce, $result['url']);
    }

    public function testInitiateJsTrackerInstallTestReplaceNonce()
    {
        $nonce = $this->createNonceOption($this->idSite1);
        // Make the new nonce expired so that we can test that it gets replaced
        $this->setNonceCheckTimestamp($this->idSite1, Date::getNowTimestamp() - JsTrackerInstallCheck::MAX_NONCE_AGE_SECONDS);
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $this->assertNotSame($nonce, $result['nonce']);
        $option = $this->getOptionForSite($this->idSite1);
        $this->assertNotEmpty($option);
        $decodedOption = json_decode($option, true);
        $this->assertIsArray($decodedOption);
        $this->assertArrayHasKey('nonce', $decodedOption);
        $this->assertSame($result['nonce'], $decodedOption['nonce']);
        $this->assertStringContainsString($decodedOption['nonce'], $result['url']);
        $this->assertSame('http://piwik.net?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . $decodedOption['nonce'], $result['url']);
    }

    private function createRequestMock(bool $hasParam, array $allParams = [], int $idSite = 0)
    {
        $mock = $this->getMockBuilder('\Piwik\Tracker\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->exactly(1))
            ->method('hasParam')
            ->will($this->returnValue($hasParam));

        // It should only proceed to get the params if it has the desired param
        if ($hasParam) {
            $mock
                ->expects($this->exactly(1))
                ->method('getParams')
                ->will($this->returnValue($allParams));
        } else {
            $mock
                ->expects($this->exactly(0))
                ->method('getParams');
        }

        if ($idSite) {
            $mock
                ->expects($this->exactly(1))
                ->method('getIdSite')
                ->will($this->returnValue($idSite));
        } else {
            $mock
                ->expects($this->exactly(0))
                ->method('getIdSite');
        }

        return $mock;
    }
}
