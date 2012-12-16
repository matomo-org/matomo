<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

class MobileMessagingTest extends DatabaseTestCase
{
	protected $idSiteAccess;

	public function setUp()
	{
		parent::setUp();

		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		//finally we set the user as a super user by default
		Zend_Registry::set('access', $pseudoMockAccess);

		$this->idSiteAccess = Piwik_SitesManager_API::getInstance()->addSite("test","http://test");

		Piwik_PluginsManager::getInstance()->loadPlugins( array('PDFReports', 'MobileMessaging', 'MultiSites') );
		Piwik_PluginsManager::getInstance()->installLoadedPlugins();
	}


	/**
	 * When the MultiSites plugin is not activated, the SMS content should invite the user to activate it back
	 *
	 * @group Plugins
	 * @group MobileMessaging
	 */
	public function testWarnUserViaSMSMultiSitesDeactivated()
	{
		// safety net
		Piwik_PluginsManager::getInstance()->loadPlugins(array('PDFReports', 'MobileMessaging'));
		$this->assertFalse(Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites'));

		$PdfReportsAPIInstance = Piwik_PDFReports_API::getInstance();
		$reportId = $PdfReportsAPIInstance->addReport(
			$this->idSiteAccess,
			'description',
			'month',
			'mobile',
			'sms',
			array(),
			array("phoneNumbers" => array('33698896656'))
		);

		list($outputFilename, $prettyDate, $websiteName, $additionalFiles) =
			$PdfReportsAPIInstance->generateReport(
				$reportId,
				'01-01-2010',
				'en',
				2
			);

		$handle = fopen($outputFilename, "r");
		$contents = fread($handle, filesize($outputFilename));
		fclose($handle);

		$this->assertEquals(
			Piwik_Translate('MobileMessaging_MultiSites_Must_Be_Activated'),
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

		$GSMWithRegExpSpecialChars = $stdGSMx459 . '[\^$.|?*+()';

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
			array(str_repeat('a€', 149)  . $extGSMAppendedString, $alternatedGSMx153 . '€', 3, $extGSMAppendedString),

			// alternated GSM, one '€' too many, appended with UCS2 characters
			array(str_repeat('a€', 98) . 'a' . $UCS2AppendedString, $alternatedGSMx153 . '€', 3, $UCS2AppendedString),

			// GSM with RegExp reserved special chars
			array(str_repeat('a', 451) . $stdGSMAppendedString, $GSMWithRegExpSpecialChars, 3, $stdGSMAppendedString),

			// maximum number of UCS-2 characters
			array($UCS2x201, $UCS2x201, 3, 'N/A'),

			// UCS-2, one '控' too many, appended with UCS2 characters
			array(str_repeat('控', 197) . $UCS2AppendedString, $UCS2x201 . '控', 3, $UCS2AppendedString),

			// UCS-2, one '控' too many, appended with standard GSM characters
			array(str_repeat('控', 193) . $stdGSMAppendedString, $UCS2x201 . '控', 3, $stdGSMAppendedString),
		);
	}

	/**
	 * @group Plugins
	 * @group MobileMessaging
	 * @dataProvider getTruncateTestCases
	 */
	public function testTruncate($expected, $stringToTruncate, $maximumNumberOfConcatenatedSMS, $appendedString)
	{
		$this->assertEquals(
			$expected,
			Piwik_MobileMessaging_SMSProvider::truncate($stringToTruncate, $maximumNumberOfConcatenatedSMS, $appendedString)
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
	 * @group Plugins
	 * @group MobileMessaging
	 * @dataProvider getContainsUCS2CharactersTestCases
	 */
	public function testContainsUCS2Characters($expected, $stringToTest)
	{
		$this->assertEquals(
			$expected,
			Piwik_MobileMessaging_SMSProvider::containsUCS2Characters($stringToTest)
		);
	}

	/**
	 * @group Plugins
	 * @group MobileMessaging
	 */
	public function testSanitizePhoneNumber()
	{
		$this->assertEquals('676932647', Piwik_MobileMessaging_API::sanitizePhoneNumber('  6  76 93 26 47'));
	}

	/**
	 * @group Plugins
	 * @group MobileMessaging
	 */
	public function testPhoneNumberIsSanitized()
	{
		$mobileMessagingAPI = new Piwik_MobileMessaging_API();
		$mobileMessagingAPI->setSMSAPICredential('StubbedProvider', '');
		$mobileMessagingAPI->addPhoneNumber('  6  76 93 26 47');
		$this->assertEquals('676932647', key($mobileMessagingAPI->getPhoneNumbers()));
	}
}
