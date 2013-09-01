<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 *
 */
namespace Piwik\Translate;

use Exception;
use Piwik\Common;
use Piwik\PluginsManager;
use Piwik\Translate\Filter\ByBaseTranslations;
use Piwik\Translate\Filter\ByParameterCount;
use Piwik\Translate\Filter\EncodedEntities;
use Piwik\Translate\Filter\EmptyTranslations;
use Piwik\Translate\Filter\UnnecassaryWhitespaces;
use Piwik\Translate\Validate\CoreTranslations;
use Piwik\Translate\Validate\NoScripts;

/**
 * Writes clean translations to file
 *
 * @package Piwik
 * @package Piwik_Translate
 */
class Writer
{
    /**
     * current language to write files for
     *
     * @var string
     */
    protected $_language = '';

    /**
     * Name of a plugin (if set in contructor)
     *
     * @var string|null
     */
    protected $_pluginName = null;

    /**
     * base translations (english) for the current instance
     *
     * @var array
     */
    protected $_baseTranslations = array();

    /**
     * translations to write to file
     *
     * @var array
     */
    protected $_translations = array();

    /**
     * Errors occured while cleaning the translations
     *
     * @var array
     */
    protected $_cleanErrors = array();

    const __UNCLEANED__  = 'uncleaned';
    const __CLEANED__    = 'cleaned';

    protected $_currentState = self::__UNCLEANED__;

    /**
     * If $pluginName is given, Writer will be initialized for the given plugin if it exists
     * Otherwise it will be initialized for core translations
     *
     * @param string  $language    ISO 639-1 alpha-2 language code
     * @param string  $pluginName  optional plugin name
     * @throws \Exception
     */
    public function __construct($language, $pluginName=null)
    {
        $this->setLanguage($language);

        if (!empty($pluginName)) {
            $installedPlugins = PluginsManager::getInstance()->readPluginsDirectory();

            if (!in_array($pluginName, $installedPlugins)) {

                throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($pluginName)));
            }

            $this->_pluginName = $pluginName;
        }

        $this->_baseTranslations = $this->_loadTranslation('en');
        $this->setTranslations($this->_loadTranslation($this->getLanguage()));
    }

    /**
     * @param string $language  ISO 639-1 alpha-2 language code
     *
     * @throws \Exception
     */
    public function setLanguage($language)
    {
        if (!preg_match('/^([a-z]{2,3}(-[a-z]{2,3})?)$/i', $language)) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($language)));
        }

        $this->_language = strtolower($language);
    }

    /**
     * @return string ISO 639-1 alpha-2 language code
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Returns if there are translations available or not
     * @return bool
     */
    public function hasTranslations()
    {
        return !empty($this->_baseTranslations) && !empty($this->_translations);
    }

    /**
     * Set the translations to write (and cleans them)
     *
     * @param $translations
     */
    public function setTranslations($translations)
    {
        $this->_currentState = self::__UNCLEANED__;
        $this->_translations = $translations;
        $this->_cleanTranslations();
    }

    /**
     * Load translations from file
     *
     * @param  string  $lang  ISO 639-1 alpha-2 language code
     * @throws Exception
     * @return array   Array of translations ( plugin => ( key => translated string ) )
     */
    protected function _loadTranslation($lang)
    {
        $path = $this->_getTranslationPath('lang', $lang);
        if (!is_readable($path)) {
            return array();
        }

        $data = file_get_contents($path);
        $translations = json_decode($data, true);
        return $translations;
    }

    /**
     * Returns the temporary path for translations
     *
     * @return string
     */
    public function getTemporaryTranslationPath()
    {
        return $this->_getTranslationPath('tmp');
    }

    /**
     * Returns the path to translation files
     *
     * @return string
     */
    public function getTranslationPath()
    {
        return $this->_getTranslationPath('lang');
    }

    /**
     * Get translation file path based on given params
     *
     * @param string $base Optional base directory (either 'lang' or 'tmp')
     * @param string|null $lang  forced language
     * @throws \Exception
     * @return string path
     */
    protected function _getTranslationPath($base, $lang=null)
    {
        if (empty($lang)) $lang = $this->getLanguage();

        if (!empty($this->_pluginName)) {

            if ($base == 'tmp') {
                return sprintf('%s/tmp/plugins/%s/lang/%s.json', PIWIK_INCLUDE_PATH, $this->_pluginName, $lang);
            } else {
                return sprintf('%s/plugins/%s/lang/%s.json', PIWIK_INCLUDE_PATH, $this->_pluginName, $lang);
            }
        }

        return sprintf('%s/%s/%s.json', PIWIK_INCLUDE_PATH, $base, $lang);
    }


    /**
     * Converts translations to a string that can be written to a file
     *
     * @return string
     */
    public function __toString()
    {
        /*
         * Use JSON_UNESCAPED_UNICODE and JSON_PRETTY_PRINT for PHP >= 5.4
         */
        $options = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) $options |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_PRETTY_PRINT'))      $options |= JSON_PRETTY_PRINT;

        return json_encode($this->_translations, $options);
    }

    /**
     * Save translations to file; translations should already be cleaned.
     *
     * @return bool|int  False if failure, or number of bytes written
     */
    public function save()
    {
        $path = $this->getTranslationPath();

        Common::mkdir(dirname($path));

        return file_put_contents($path, $this->__toString());
    }

    /**
     * Save translations to  temporary file; translations should already be cleansed.
     *
     * @return bool|int False if failure, or number of bytes written
     */
    public function saveTemporary()
    {
        $path = $this->getTemporaryTranslationPath();

        Common::mkdir(dirname($path));

        return file_put_contents($path, $this->__toString());
    }

    /**
     * Returns if the were translations removed while cleaning
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->_cleanErrors);
    }

    /**
     * Returns the cleaning errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_cleanErrors;
    }

    /**
     * @throws \Exception
     *
     * @return bool   error state
     */
    protected function _cleanTranslations()
    {
        // skip if already cleaned
        if ($this->_currentState == self::__CLEANED__) {
            return $this->hasErrors();
        }

        $this->_cleanErrors = array();

        // skip if not translations available
        if (!$this->hasTranslations()) {
            $this->_currentState = self::__CLEANED__;
            return false;
        }

        $basefilter = new ByBaseTranslations($this->_baseTranslations);
        $cleanedTranslations = $basefilter->filter($this->_translations);
        $filteredData = $basefilter->getFilteredData();
        if (!empty($filteredData)) {
            $this->_cleanErrors[] = "removed translations that are not present in base translations: " .var_export($filteredData, 1);
        }

        $emptyfilter = new EmptyTranslations($this->_baseTranslations);
        $cleanedTranslations = $emptyfilter->filter($cleanedTranslations);
        $filteredData = $emptyfilter->getFilteredData();
        if (!empty($filteredData)) {
            $this->_cleanErrors[] = "removed empty translations: " .var_export($filteredData, 1);
        }

        $parameterFilter = new ByParameterCount($this->_baseTranslations);
        $cleanedTranslations = $parameterFilter->filter($cleanedTranslations);
        $filteredData = $parameterFilter->getFilteredData();
        if (!empty($filteredData)) {
            $this->_cleanErrors[] = "removed translations that had diffrent parameter counts: " .var_export($filteredData, 1);
        }

        $whitespaceFilter = new UnnecassaryWhitespaces($this->_baseTranslations);
        $cleanedTranslations = $whitespaceFilter->filter($cleanedTranslations);
        $filteredData = $whitespaceFilter->getFilteredData();
        if (!empty($filteredData)) {
            $this->_cleanErrors[] = "filtered unnecassary whitespaces in some translations: " .var_export($filteredData, 1);
        }

        $entityFilter = new EncodedEntities($this->_baseTranslations);
        $cleanedTranslations = $entityFilter->filter($cleanedTranslations);
        $filteredData = $entityFilter->getFilteredData();
        if (!empty($filteredData)) {
            $this->_cleanErrors[] = "converting entities to characters in some translations: " .var_export($filteredData, 1);
        }

        $noscriptValidator = new NoScripts();
        if (!$noscriptValidator->isValid($cleanedTranslations)) {
            throw new Exception($noscriptValidator->getError());
        }

        // check requirements for core translations
        if (empty($this->_pluginName)) {

            $baseValidator = new CoreTranslations($this->_baseTranslations);
            if(!$baseValidator->isValid($cleanedTranslations)) {
                throw new Exception($baseValidator->getError());
            }
        }

        $this->_currentState = self::__CLEANED__;

        if ($cleanedTranslations != $this->_translations) {
            $this->_cleanErrors[] = 'translations have been cleaned';
        }

        $this->_translations = $cleanedTranslations;
        return $this->hasErrors();
    }
}
