<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
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
			$serializedStrings = serialize($strings);
			$invalids = array("<script", 'document.', 'javascript:', 'src=', 'BACKGROUND=', 'onload=' );
			foreach($invalids as $invalid)
			{
				$this->assertTrue(stripos($serializedStrings, $invalid) === false, "$language: language file containing javascript");
			}
			$this->assertTrue(count($strings) > 100); // at least 100 translations in the language file
			$this->assertTrue(strlen($content) == 0, "$language: buffer was ".strlen($content)." long but should be zero. Translation file for '$language' must be buggy.");
			
			$cleanedStrings = array();
			foreach($strings as $string)
			{
				$stringLabel = $string['label'];
				$stringValue = $string['value'];
				
				// Testing that the translated string is not empty => '',
				if(empty($stringValue))
				{
					$writeCleanedFile = true;
            		echo "$language: The string $stringLabel is empty in the translation file, removing the line. <br/>\n";
            		$cleanedStrings[$stringLabel] = false;
				}
    			// checking that translated strings have the same number of %s as the english source strings
				else
				{
					if(isset($englishStringsWithParameters[$stringLabel]))
    				{
    					$englishParametersCount = $englishStringsWithParameters[$stringLabel];
    					$countTranslation = $this->getCountParametersToReplace($stringValue);
    					if($englishParametersCount != $countTranslation)
    					{
    						// Write fixed file in given location
    						// Will trigger a ->fail()
    						$writeCleanedFile = true;
                    		echo "$language: The string $stringLabel has $englishParametersCount parameters in English, but $countTranslation in this translation. <br/>\n";
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
				// remove excessive line breaks from translations
				if($stringLabel != 'Login_MailPasswordRecoveryBody'
					&& !empty($cleanedStrings[$stringLabel]))
				{
					$stringNoLineBreak = str_replace(array("\n", "\r"), " ", $cleanedStrings[$stringLabel]);
					if($cleanedStrings[$stringLabel] !== $stringNoLineBreak)
					{
						echo "$language: found line breaks in some strings in $stringLabel <br/>\n";
						$writeCleanedFile = true;
						$cleanedStrings[$stringLabel] = $stringNoLineBreak;
					}
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
		$tstr = '<?php'.PHP_EOL;
		$tstr .= '$translations = array('.PHP_EOL;
		foreach($translations as $key => $value)
		{
			if(!empty($value))
			{
				$tstr .= "\t'".$key."' => '".addcslashes($value,"'")."',".PHP_EOL;
			}
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

	// test English short name for language
	function test_getLanguageNamesInEnglish()
	{
		require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Languages.php';

		$languages = Piwik_LanguagesManager_API::getInstance()->getAvailableLanguages();
		foreach($languages as $language)
		{
			require PIWIK_INCLUDE_PATH . "/lang/$language.php";
			$name = $translations['General_EnglishLanguageName'];

			if($language != 'en')
			{
				$this->assertFalse($name == 'English');
			}

			$languageCode = substr($language, 0, 2);
			$this->assertTrue(isset($GLOBALS['Piwik_LanguageList'][$languageCode]));
			$names = $GLOBALS['Piwik_LanguageList'][$languageCode];

			if(isset($GLOBALS['Piwik_LanguageList'][$language]))
			{
				if(is_array($names))
				{
					$this->assertTrue(in_array($name, $names));
				}
				else
				{
					$this->assertTrue($name == $names, "$language: failed because $name == $names <br/>");
				}
			}
			else
			{
				if(is_array($names))
				{
					$this->fail("There are \"official\" language names to choose from for $languageCode, e.g., ". implode(', ', $names));
				}
				else
				{
					$this->assertTrue(strpos($name, $names) !== false);
				}
			}
		}
	}
}
