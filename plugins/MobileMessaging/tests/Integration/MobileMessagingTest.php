<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging\tests\Integration;

use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\API as APIMobileMessaging;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\MobileMessaging\Model;
use Piwik\Plugins\MobileMessaging\SMSProvider;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Plugins_MobileMessagingTest
 *
 * @group Plugins
 */
class MobileMessagingTest extends IntegrationTestCase
{
    protected $idSiteAccess;

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;

        $this->idSiteAccess = APISitesManager::getInstance()->addSite("test", "http://test");

        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('ScheduledReports', 'MobileMessaging', 'MultiSites'));
        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
    }

    /**
     * When the MultiSites plugin is not activated, the SMS content should invite the user to activate it back
     */
    public function testWarnUserViaSMSMultiSitesDeactivated()
    {
        // safety net
        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('ScheduledReports', 'MobileMessaging'));
        $this->assertFalse(\Piwik\Plugin\Manager::getInstance()->isPluginActivated('MultiSites'));

        $APIScheduledReports = APIScheduledReports::getInstance();
        $reportId = $APIScheduledReports->addReport(
            $this->idSiteAccess,
            'description',
            'month',
            0,
            'mobile',
            'sms',
            array(),
            array("phoneNumbers" => array('33698896656'))
        );

        $contents = $APIScheduledReports->generateReport(
            $reportId,
            '01-01-2010',
            'en',
            APIScheduledReports::OUTPUT_RETURN
        );

        $this->assertEquals(
            \Piwik\Piwik::translate('MobileMessaging_MultiSites_Must_Be_Activated'),
            $contents
        );
    }

    /**
     * Dataprovider for testTruncate
     */
    public function getTruncateTestCases()
    {

        $stdGSMx459 = str_repeat('a', 459);

        $extGSMx229 = str_repeat('€', 229);

        $alternatedGSMx153 = str_repeat('a€', 153);

        $GSMWithRegExpSpecialChars = $stdGSMx459 . '[\^$.|?*/+()';

        $UCS2x201 = str_repeat('控', 201);

        // appended strings
        $stdGSMAppendedString = 'too long';
        $extGSMAppendedString = '[too long]';
        $UCS2AppendedString = '[控控]';

        return array(

            // maximum number of standard GSM characters
            array($stdGSMx459, $stdGSMx459, 3, 'N/A'),

            // maximum number of extended GSM characters
            array($extGSMx229, $extGSMx229, 3, 'N/A'),

            // maximum number of alternated GSM characters
            array($alternatedGSMx153, $alternatedGSMx153, 3, 'N/A'),

            // standard GSM, one 'a' too many, appended with standard GSM characters
            array(str_repeat('a', 451) . $stdGSMAppendedString, $stdGSMx459 . 'a', 3, $stdGSMAppendedString),

            // standard GSM, one 'a' too many, appended with extended GSM characters
            array(str_repeat('a', 447) . $extGSMAppendedString, $stdGSMx459 . 'a', 3, $extGSMAppendedString),

            // standard GSM, one 'a' too many, appended with UCS2 characters
            array(str_repeat('a', 197) . $UCS2AppendedString, $stdGSMx459 . 'a', 3, $UCS2AppendedString),

            // extended GSM, one '€' too many, appended with standard GSM characters
            array(str_repeat('€', 225) . $stdGSMAppendedString, $extGSMx229 . '€', 3, $stdGSMAppendedString),

            // extended GSM, one '€' too many, appended with extended GSM characters
            array(str_repeat('€', 223) . $extGSMAppendedString, $extGSMx229 . '€', 3, $extGSMAppendedString),

            // extended GSM, one '€' too many, appended with UCS2 characters
            array(str_repeat('€', 197) . $UCS2AppendedString, $extGSMx229 . '€', 3, $UCS2AppendedString),

            // alternated GSM, one 'a' too many, appended with standard GSM characters
            array(str_repeat('a€', 150) . 'a' . $stdGSMAppendedString, $alternatedGSMx153 . 'a', 3, $stdGSMAppendedString),

            // alternated GSM, one 'a' too many, appended with extended GSM characters
            array(str_repeat('a€', 149) . $extGSMAppendedString, $alternatedGSMx153 . 'a', 3, $extGSMAppendedString),

            // alternated GSM, one 'a' too many, appended with UCS2 characters
            array(str_repeat('a€', 98) . 'a' . $UCS2AppendedString, $alternatedGSMx153 . 'a', 3, $UCS2AppendedString),

            // alternated GSM, one '€' too many, appended with standard GSM characters
            array(str_repeat('a€', 150) . 'a' . $stdGSMAppendedString, $alternatedGSMx153 . '€', 3, $stdGSMAppendedString),

            // alternated GSM, one '€' too many, appended with extended GSM characters
            array(str_repeat('a€', 149) . $extGSMAppendedString, $alternatedGSMx153 . '€', 3, $extGSMAppendedString),

            // alternated GSM, one '€' too many, appended with UCS2 characters
            array(str_repeat('a€', 98) . 'a' . $UCS2AppendedString, $alternatedGSMx153 . '€', 3, $UCS2AppendedString),

            // GSM with RegExp reserved special chars
            array(str_repeat('a', 451) . $stdGSMAppendedString, $GSMWithRegExpSpecialChars, 3, $stdGSMAppendedString),

            // maximum number of UCS-2 characters
            array($UCS2x201, $UCS2x201, 3, 'N/A'),

            // UCS-2, one '控' too many, appended with UCS2 characters
            array(str_repeat('控', 197) . $UCS2AppendedString, $UCS2x201 . '控', 3, $UCS2AppendedString),

            // UCS-2, one '控' too many, appended with standard GSM characters
            array(str_repeat('控', 193) . $stdGSMAppendedString, $UCS2x201 . '控', 3, $stdGSMAppendedString)
        );
    }

    /**
     * @dataProvider getTruncateTestCases
     */
    public function testTruncate($expected, $stringToTruncate, $maximumNumberOfConcatenatedSMS, $appendedString)
    {
        $this->assertEquals(
            $expected,
            SMSProvider::truncate($stringToTruncate, $maximumNumberOfConcatenatedSMS, $appendedString)
        );
    }

    /**
     * Dataprovider for testContainsUCS2Characters
     */
    public function getContainsUCS2CharactersTestCases()
    {
        return array(
            array(false, 'too long'),
            array(false, '[too long]'),
            array(false, '€'),
            array(true, '[控控]'),
        );
    }

    /**
     * @dataProvider getContainsUCS2CharactersTestCases
     */
    public function testContainsUCS2Characters($expected, $stringToTest)
    {
        $this->assertEquals(
            $expected,
            SMSProvider::containsUCS2Characters($stringToTest)
        );
    }

    public function testPhoneNumberIsSanitized()
    {
        $mobileMessagingAPI = APIMobileMessaging::getInstance();
        $model = new Model();
        $mobileMessagingAPI->setSMSAPICredential('StubbedProvider', '');
        $mobileMessagingAPI->addPhoneNumber('  6  76 93 26 47');
        $this->assertEquals('676932647', key($model->getPhoneNumbers(Piwik::getCurrentUserLogin())));
    }

    /**
     * Dataprovider for testSendReport
     */
    public function getSendReportTestCases()
    {
        return array(
            array('reportContent', '0101010101', 'Piwik.org', 'reportContent', '0101010101', 'Piwik.org'),
            array('reportContent', '0101010101', 'General_Reports', 'reportContent', '0101010101', 'General_MultiSitesSummary'),
        );
    }

    /**
     * @dataProvider getSendReportTestCases
     */
    public function testSendReport($expectedReportContent, $expectedPhoneNumber, $expectedFrom, $reportContent, $phoneNumber, $reportSubject)
    {
        $report = array(
            'parameters' => array(MobileMessaging::PHONE_NUMBERS_PARAMETER => array($phoneNumber)),
        );

        $stubbedModel = $this->getMockBuilder(Model::class)
            ->onlyMethods(array('sendSMS'))
            ->getMock();
        $stubbedModel->expects($this->once())->method('sendSMS')->with(
            $this->equalTo($expectedReportContent, 0),
            $this->equalTo($expectedPhoneNumber, 1),
            $this->equalTo($expectedFrom, 2)
        );

        $stubbedMobileMessaging = $this->getMockBuilder(MobileMessaging::class)
            ->onlyMethods(['getModel'])
            ->getMock();

        $stubbedMobileMessaging->expects($this->once())->method('getModel')->will($this->returnValue($stubbedModel));
        $stubbedMobileMessaging->sendReport(MobileMessaging::MOBILE_TYPE, $report, $reportContent, null, null, $reportSubject, null, null, null, false);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
