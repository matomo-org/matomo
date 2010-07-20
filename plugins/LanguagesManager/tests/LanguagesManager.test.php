<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'LanguagesManager/API.php';

class Test_Languages_Manager extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	// test all languages
	function test_getTranslationsForLanguages()
	{
		$englishStrings = Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage('en');
		$englishStringsWithParameters = array();
		foreach($englishStrings as $englishString)
		{
			$stringLabel = $englishString['label'];
			$stringValue = $englishString['value'];
			$count = $this->getCountParametersToReplace($stringValue);
			if($count > 0)
			{
				$englishStringsWithParameters[$stringLabel] = $count;
			}
		}
		
		// we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
		$languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguages();
		foreach($languages as $language)
		{
			ob_start();
			$writeCleanedFile = false; 
			$strings = Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage($language);
			$content = ob_get_flush();
			$this->assertTrue(strpos(serialize($strings), "<script") === false, " language file containing javascript");
			$this->assertTrue(count($strings) > 100); // at least 100 translations in the language file
			$this->assertTrue(strlen($content) == 0, "buffer was ".strlen($content)." long but should be zero. Translation file for '$language' must be buggy.");
			
			// checking that translated strings have the same number of %s as the english source strings
			$cleanedStrings = array();
			foreach($strings as $string)
			{
				$stringLabel = $string['label'];
				$stringValue = $string['value'];
				
				if(!empty($stringValue)
					&& isset($englishStringsWithParameters[$stringLabel]))
				{
					$englishParametersCount = $englishStringsWithParameters[$stringLabel];
					$countTranslation = $this->getCountParametersToReplace($stringValue);
					if($englishParametersCount != $countTranslation)
					{
						// Write fixed file in given location
						// Will trigger a ->fail()
						$writeCleanedFile = true;
                		echo "The string $stringLabel has $englishParametersCount parameters in English, but $countTranslation in the language $language. <br/>\n";
					}
					else
					{
						$cleanedStrings[$stringLabel] = $stringValue;
					}
				}
				// No %s found
				else
				{
					$cleanedStrings[$stringLabel] = $stringValue;
				}
			}
			if($writeCleanedFile)
			{
				$this->writeCleanedTranslationFile($cleanedStrings, $language);
			}
		}
		$this->pass();
	}
	
	private function writeCleanedTranslationFile($translations, $language)
	{
		$pathFixedTranslations = PIWIK_INCLUDE_PATH . '/tmp/';
		$filename = $language . '.php';
		$tstr = '<?php '.PHP_EOL;
		$tstr .= '$translations = array('.PHP_EOL;
		foreach($translations as $key => $value)
		{
			$tstr .= "\t'".$key."' => '".addcslashes($value,"'")."',".PHP_EOL;
		}
		$tstr .= ');'.PHP_EOL;
		$path = $pathFixedTranslations . $filename;
		file_put_contents($path, $tstr);
		$this->fail('Translation file errors detected in '.$filename.'... 
					Wrote cleaned translation file in: '.$path .".
					You can copy the cleaned files to /langs/<br/>\n");
	}
	
	private function getCountParametersToReplace($string)
	{
		$sprintfParameters = array('%s', '%1$s', '%2$s', '%3$s', '%4$s', '%5$s', '%6$s');
		$count = 0;
		foreach($sprintfParameters as $parameter)
		{
			$count += substr_count($string, $parameter);
		}
		return $count;
	}
	//test language when it's not defined
	function test_getTranslationsForLanguages_not()
	{
		$this->assertFalse(Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage("../no-language"));
	}
}

