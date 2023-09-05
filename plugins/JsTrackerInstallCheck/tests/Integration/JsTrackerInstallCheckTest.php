<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Integration;

use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\JsTrackerInstallCheck\JsTrackerInstallCheck;
use Piwik\Plugins\JsTrackerInstallCheck\NonceOption\JsTrackerInstallCheckOption;
use Piwik\Plugins\JsTrackerInstallCheck\tests\Integration\JsTrackerInstallCheckIntegrationTestCase;
use Piwik\Site;

/**
 * @group JsTrackerInstallCheck
 * @group Plugins
 * @group JsTrackerInstallCheckTest
 */
class JsTrackerInstallCheckTest extends JsTrackerInstallCheckIntegrationTestCase
{
    const TEST_URL1 = 'https://some-test-site.local';
    const TEST_URL2 = 'https://another-test-site.local';
    const TEST_URL3 = 'https://nonexistent-test-site.local';
    const TEST_NONCE1 = '7fa8282ad93047a4d6fe6111c93b308a';
    const TEST_NONCE2 = '79d886010186eb60e3611cd4a5d0bcae';

    /**
     * @var JsTrackerInstallCheck
     */
    protected $jsTrackerInstallCheck;

    /**
     * @var array
     */
    protected $testOptions;

    /**
     * @var int
     */
    protected $idSite1;

    /**
     * @var int
     */
    protected $idSite2;

    public function provideContainerConfig()
    {
        $this->testOptions = [
            1 => [
                self::TEST_NONCE1 => [
                    JsTrackerInstallCheckOption::NONCE_DATA_TIME => Date::getNowTimestamp(),
                    JsTrackerInstallCheckOption::NONCE_DATA_URL => self::TEST_URL1,
                    JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS => true,
                ],
                self::TEST_NONCE2 => [
                    JsTrackerInstallCheckOption::NONCE_DATA_TIME => Date::getNowTimestamp(),
                    JsTrackerInstallCheckOption::NONCE_DATA_URL => self::TEST_URL2,
                    JsTrackerInstallCheckOption::NONCE_DATA_IS_SUCCESS => false,
                ],
            ]
        ];

        $mock = $this->getMockBuilder('stdClass')
            ->addMethods(['markNonceAsSuccessFul', 'getNonceMap', 'getCurrentNonceMap', 'createNewNonce'])
            ->getMock();
        $mock->expects($this->any())->method('markNonceAsSuccessFul')->willReturnCallback(function ($idSite, $nonce) {
            if (isset($this->testOptions[$idSite][$nonce])) {
                $this->testOptions[$idSite][$nonce] = true;
            }
        });
        $mock->expects($this->any())->method('getNonceMap')->willReturnCallback(function ($idSite) {
            return $this->testOptions[$idSite] ?? [];
        });
        $mock->expects($this->any())->method('getCurrentNonceMap')->willReturnCallback(function ($idSite) {
            $testArray = $this->testOptions;
            unset($testArray[self::TEST_NONCE1]);
            return $testArray[$idSite];
        });
        $mock->expects($this->any())->method('createNewNonce')->willReturnCallback(function ($idSite) {
            return self::TEST_NONCE1;
        });

        return [
            JsTrackerInstallCheckOption::class => $mock,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->jsTrackerInstallCheck = new JsTrackerInstallCheck();

        $mock = $this->getMockBuilder('stdClass')
            ->addMethods(['getValue', 'setValue'])
            ->getMock();
        $mock->expects($this->any())->method('getValue')->willReturnCallback(function ($key) {
            return $this->optionsArray[$key] ?? false;
        });
        $mock->expects($this->any())->method('setValue')->willReturnCallback(function ($key, $value) {
            $this->optionsArray[$key] = $value;
        });

        Option::setSingletonInstance($mock);
        Site::setSiteFromArray($this->idSite1, ['idSite' => $this->idSite1, 'main_url' => self::TEST_URL1]);
        Site::setSiteFromArray($this->idSite2, ['idSite' => $this->idSite1, 'main_url' => self::TEST_URL1]);
    }

    public function tearDown(): void
    {
        Option::setSingletonInstance(null);
    }

    public function testIsExcludedVisitNoParams()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(false);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisitNoParamsAlreadyExcluded()
    {
        $isExcluded = true;
        $testRequest = $this->createRequestMock(false);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
    }

    public function testIsExcludedVisitEmptyParam()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => '']);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertFalse($isExcluded);
    }

    public function testIsExcludedVisit()
    {
        $isExcluded = false;
        $testRequest = $this->createRequestMock(true, [JsTrackerInstallCheck::QUERY_PARAM_NAME => 'abc123'], $this->idSite1);
        $this->jsTrackerInstallCheck->isExcludedVisit($isExcluded, $testRequest);
        $this->assertTrue($isExcluded);
    }

    public function testCheckForJsTrackerInstallTestSuccessNonExistantNonce()
    {
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess(3));
    }

    public function testCheckForJsTrackerInstallTestSuccessFalse()
    {
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite2));
    }

    public function testCheckForJsTrackerInstallTestSuccess()
    {
        $this->assertTrue($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonceProvided()
    {
        $this->assertTrue($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1, self::TEST_NONCE1));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonceProvidedFalse()
    {
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1, self::TEST_NONCE2));
    }

    public function testCheckForJsTrackerInstallTestSuccessNonceProvidedNotFound()
    {
        $this->assertFalse($this->jsTrackerInstallCheck->checkForJsTrackerInstallTestSuccess($this->idSite1, 'abc123'));
    }

    public function testInitiateJsTrackerInstallTest()
    {
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame(self::TEST_NONCE1, $result['nonce']);
        $this->assertStringContainsString(self::TEST_NONCE1, $result['url']);
        $this->assertSame(self::TEST_URL1 . '?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . self::TEST_NONCE1, $result['url']);
    }

    public function testInitiateJsTrackerInstallTestProvideUrl()
    {
        $testUrl = 'https://some-test-site.com?test=1';
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1, $testUrl);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame(self::TEST_NONCE1, $result['nonce']);
        $this->assertStringContainsString(self::TEST_NONCE1, $result['url']);
        $this->assertSame($testUrl . '&' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . self::TEST_NONCE1, $result['url']);
    }

    public function testInitiateJsTrackerInstallTestProvideInvalidUrl()
    {
        $result = $this->jsTrackerInstallCheck->initiateJsTrackerInstallTest($this->idSite1, 'abc123');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result['url']);
        $this->assertNotEmpty($result['nonce']);
        $this->assertSame(self::TEST_NONCE1, $result['nonce']);
        $this->assertStringContainsString(self::TEST_NONCE1, $result['url']);
        $this->assertSame(self::TEST_URL1 . '?' . JsTrackerInstallCheck::QUERY_PARAM_NAME . '=' . self::TEST_NONCE1, $result['url']);
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
