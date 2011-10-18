<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'LanguagesManager/API.php';

class Test_LanguagesManager extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	// test all languages
	function test_getTranslationsForLanguages()
	{
		$allLanguages = Piwik_Common::getLanguagesList();
		$allCountries = Piwik_Common::getCountriesList();
		$englishStrings = Piwik_LanguagesManager_API::getInstance()->getTranslationsForLanguage('en');
		$englishStringsWithParameters = array();
		$expectedLanguageKeys = array();
		foreach($englishStrings as $englishString)
		{
			$stringLabel = $englishString['label'];
			$stringValue = $englishString['value'];
			$count = $this->getCountParametersToReplace($stringValue);
			if($count > 0)
			{
				$englishStringsWithParameters[$stringLabel] = $count;
			}
			$englishStringsIndexed[$stringLabel] = $stringValue;
			$expectedLanguageKeys[] = $stringLabel;
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
			$this->assertTrue(count($strings) > 100, "$language: expecting at least 100 translations in the language file");
			$this->assertTrue(strlen($content) == 0, "$language: buffer was ".strlen($content)." long but should be zero. Translation file for '$language' must be buggy.");
			
			$cleanedStrings = array();
			foreach($strings as $string)
			{
				$stringLabel = $string['label'];
				$stringValue = $string['value'];
				
				$plugin = substr($stringLabel, 0, strpos($stringLabel, '_'));
				$plugins[$plugin] = true;
				// Testing that the translated string is not empty => '',
				if(empty($stringValue) || trim($stringValue) === '')
				{
					$writeCleanedFile = true;
            		echo "$language: The string $stringLabel is empty in the translation file, removing the line. <br/>\n";
            		$cleanedStrings[$stringLabel] = false;
				}
				elseif(!in_array($stringLabel, $expectedLanguageKeys)
					// translation files should not contain 3rd plugin translations, but if they are there, we shall not delete them
					// since translators have spent time working on it... at least for now we shall leave them in (until V2 and plugin repository is done)
					&& !in_array($plugin, array('GeoIP', 'Forecast', 'EntryPage', 'UserLanguage'))
					)
				{
					$writeCleanedFile = true;
            		echo "$language: The string $stringLabel was not found in the English language file, removing the line. <br/>\n";
            		$cleanedStrings[$stringLabel] = false;
				}
				else
				{
	    			// checking that translated strings have the same number of %s as the english source strings
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
			
				// If the translation is the same as in English, we remove it from the translation file (as it might have been copied by
				// the translator but this would skew translation stats
				if(isset($englishStringsIndexed[$stringLabel])
					&& $englishStringsIndexed[$stringLabel] == $stringValue
					//Currently hackjed for Persian since only the Farsi translation seems affected by "english copy paste"
					&& $language == 'fa')
				{
					$writeCleanedFile = true;
					echo "$language: The string $stringLabel is the same as in English, removing... <br/>\n";
					$cleanedStrings[$stringLabel] = false;
				}
				// remove excessive line breaks (and leading/trailing whitespace) from translations
				if(!empty($cleanedStrings[$stringLabel]))
				{
					$stringNoLineBreak = trim($cleanedStrings[$stringLabel]);
					if($stringLabel != 'Login_MailPasswordRecoveryBody')
					{
						$stringNoLineBreak = str_replace(array("\n", "\r"), " ", $stringNoLineBreak);
					}
					if($cleanedStrings[$stringLabel] !== $stringNoLineBreak)
					{
						echo "$language: found unnecessary whitespace in some strings in $stringLabel <br/>\n";
						$writeCleanedFile = true;
						$cleanedStrings[$stringLabel] = $stringNoLineBreak;
					}
				}
				// Test locale
				if($stringLabel == 'General_Locale'
					&& !empty($cleanedStrings[$stringLabel]))
				{
					if(!preg_match('/^([a-z]{2})_([A-Z]{2})\.UTF-8$/', $cleanedStrings[$stringLabel], $matches))
					{
						$this->fail("$language: invalid locale in $stringLabel");
					}
					else if(!array_key_exists($matches[1], $allLanguages))
					{
						$this->fail("$language: invalid language code in $stringLabel");
					}
					else if(!array_key_exists(strtolower($matches[2]), $allCountries))
					{
						$this->fail("$language: invalid region (country code) in $stringLabel");
					}
				}
				if(isset($cleanedStrings[$stringLabel]))
				{
					$currentString = $cleanedStrings[$stringLabel];
					$decoded = Piwik_TranslationWriter::clean($currentString);
					if($currentString != $decoded )
					{
						echo "$language: found encoded entities in $stringLabel, converting entities to characters <br/>\n";
						$writeCleanedFile = true;
						$cleanedStrings[$stringLabel] = $decoded;
					}
				}
			}
			$this->assertTrue( !empty($cleanedStrings['General_TranslatorName'] ), "$language: translator info not specified");
			$this->assertTrue( !empty($cleanedStrings['General_TranslatorEmail'] ), "$language: translator info not specified");
			if(!empty($cleanedStrings['General_LayoutDirection'])
				&& !in_array($cleanedStrings['General_LayoutDirection'], array('rtl','ltr')))
			{
				$writeCleanedFile = true;
				$cleanedStrings['General_LayoutDirection'] = false;
				echo "$language: General_LayoutDirection must be rtl or ltr";
			}
			if($writeCleanedFile)
			{
				$this->writeCleanedTranslationFile($cleanedStrings, $language);
			}
		}
//		var_dump('Unique plugins found: ' . var_export($plugins, 1));
		$this->pass();
	}
	
	private function writeCleanedTranslationFile($translations, $language)
	{
		$path = Piwik_TranslationWriter::getTranslationPath($language, 'tmp');
		Piwik_TranslationWriter::saveTranslation($translations, $path);
		$this->fail('Translation file errors detected in '.$language.'... 
					Wrote cleaned translation file in: '.$path .".
					You can copy the cleaned files to /lang/<br/>\n");
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
					$this->assertTrue(in_array($name, $names), "$language: failed because $name not a known language name <br/>");
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
					$this->assertTrue(strpos($name, $names[0]) !== false);
				}
				else
				{
					$this->fail("$language: expected an array of language names");
				}
			}
		}
	}

	// test format of DataFile/Languages.php
	function test_getLanguagesList()
	{
		$languages = Piwik_Common::getLanguagesList();
		$this->assertTrue( count($languages) > 0 );
		foreach($languages as $langCode => $langs) {
			$this->assertTrue(strlen($langCode) == 2, "$langCode length = 2");
			$this->assertTrue(is_array($langs) && count($langs) >= 1, "$langCode array(names) >= 1");
		}
	}
}
