<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_TranslationWriter extends UnitTestCase
{
	function test_clean()
	{
		$tests = array(
			// empty string
			"" => '',
			// newline
			"\n" => '',
			// leading and trailing whitespace
			" a \n" => 'a',
			// single / double quotes
			" &quot;it&#039;s&quot; " => '"it\'s"',
			// html special characters
			"&lt;tag&gt;" => '<tag>',
			// other html entities
			"&hellip;" => '…',
		);

		foreach($tests as $data => $expected)
		{
			$this->assertEqual(Piwik_TranslationWriter::clean($data), $expected, "not $expected");
		}
	}

	function test_quote()
	{
		$tests = array(
			// alphanumeric
			'abc 123' => "'abc 123'",
			// newline
			"\n" => '
',
			'
' => "'
'",
			// tab
			'	' => "'	'",
			// single quote
			"it's" => "'it\'s'",
		);

		foreach($tests as $data => $expected)
		{
			if(Piwik_Common::isWindows() && $data == "\n")
			{
				continue;
			} 
			$this->assertEqual(Piwik_TranslationWriter::quote($data), $expected, "$data => not '$expected'");
		}
	}

	function test_getTranslationPath()
	{
		// invalid lang
		try {
			$path = Piwik_TranslationWriter::getTranslationPath('../index');
			$this->fail('invalid lang');
		} catch(Exception $e) {
			$this->pass();
		}

		// invalid base path
		try {
			$path = Piwik_TranslationWriter::getTranslationPath('en', 'core');
			$this->fail('invalid base path');
		} catch(Exception $e) {
			$this->pass();
		}

		// implicit base path
		$this->assertEqual(Piwik_TranslationWriter::getTranslationPath('en'), PIWIK_INCLUDE_PATH . '/lang/en.php');

		// explicit base path
		$this->assertEqual(Piwik_TranslationWriter::getTranslationPath('en', 'lang'), PIWIK_INCLUDE_PATH . '/lang/en.php');
		$this->assertEqual(Piwik_TranslationWriter::getTranslationPath('en', 'tmp'), PIWIK_INCLUDE_PATH . '/tmp/en.php');
	}

	function test_loadTranslation()
	{
		// invalid lang
		try {
			$translations = Piwik_TranslationWriter::loadTranslation('a');
			$this->fail('invalid lang');
		} catch(Exception $e) {
			$this->pass();
		}

		require PIWIK_INCLUDE_PATH . '/lang/en.php';
		$this->assertTrue(is_array($translations));

		$englishTranslations = Piwik_TranslationWriter::loadTranslation('en');

		$this->assertTrue(count($translations) == count($englishTranslations));
		$this->assertTrue(count(array_diff($translations, $englishTranslations)) == 0);
		$this->assertTrue(count(array_diff_assoc($translations, $englishTranslations)) == 0);
	}

	function test_saveTranslation()
	{
		$path = Piwik_TranslationWriter::getTranslationPath('en', 'tmp');

		$translations = array(
			'General_Locale' => 'en_CA.UTF-8',
			'General_Id' => 'Id',
			'Goals_Goals' => 'Goals',
			'Plugin_Body' => "Message\nBody",
		);

		@unlink($path);

		$rc = Piwik_TranslationWriter::saveTranslation($translations, $path);
		$this->assertTrue($rc !== false);

		$contents = file_get_contents($path);
		$expected = "<?php
\$translations = array(
\t'General_Locale' => 'en_CA.UTF-8',
\t'General_Id' => 'Id',
\t'Goals_Goals' => 'Goals',

\t// FOR REVIEW
\t'Plugin_Body' => 'Message
Body',
);
";
		if(Piwik_Common::isWindows()) $expected = str_replace("\r\n", "\n", $expected);
		$this->assertEqual($contents, $expected);
	}
}
