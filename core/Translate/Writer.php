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
use Piwik\Translate\Filter\FilterAbstract;
use Piwik\Translate\Validate\ValidateAbstract;

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
     * translations to write to file
     *
     * @var array
     */
    protected $_translations = array();

    /**
     * Validators to check translations with
     *
     * @var ValidateAbstract[]
     */
    protected $_validators = array();

    /**
     * Message why validation failed
     *
     * @var string|null
     */
    protected $_validationMessage = null;

    /**
     * Filters to to apply to translations
     *
     * @var FilterAbstract[]
     */
    protected $_filters = array();

    /**
     * Messages which filter changed the data
     *
     * @var array
     */
    protected $_filterMessages = array();

    const __UNFILTERED__  = 'unfiltered';
    const __FILTERED__    = 'filtered';

    protected $_currentState = self::__UNFILTERED__;

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
        return !empty($this->_translations);
    }

    /**
     * Set the translations to write (and cleans them)
     *
     * @param $translations
     */
    public function setTranslations($translations)
    {
        $this->_currentState = self::__UNFILTERED__;
        $this->_translations = $translations;
        $this->_applyFilters();
    }

    /**
     * Get translations from file
     *
     * @param  string  $lang  ISO 639-1 alpha-2 language code
     * @throws Exception
     * @return array   Array of translations ( plugin => ( key => translated string ) )
     */
    public function getTranslations($lang)
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
     * @throws \Exception
     * @return bool|int  False if failure, or number of bytes written
     */
    public function save()
    {
        $this->_applyFilters();

        if (!$this->hasTranslations() || !$this->isValid()) {
            throw new Exception('unable to save empty or invalid translations');
        }

        $path = $this->getTranslationPath();

        Common::mkdir(dirname($path));

        return file_put_contents($path, $this->__toString());
    }

    /**
     * Save translations to  temporary file; translations should already be cleansed.
     *
     * @throws \Exception
     * @return bool|int False if failure, or number of bytes written
     */
    public function saveTemporary()
    {
        $this->_applyFilters();

        if (!$this->hasTranslations() || !$this->isValid()) {
            throw new Exception('unable to save empty or invalid translations');
        }

        $path = $this->getTemporaryTranslationPath();

        Common::mkdir(dirname($path));

        return file_put_contents($path, $this->__toString());
    }

    /**
     * Adds an validator to check before saving
     *
     * @param ValidateAbstract $validator
     */
    public function addValidator(ValidateAbstract $validator)
    {
        $this->_validators[] = $validator;
    }

    /**
     * Returns if translations are valid to save or not
     *
     * @return bool
     */
    public function isValid()
    {
        $this->_applyFilters();

        $this->_validationMessage = null;

        foreach ($this->_validators AS $validator) {
            if (!$validator->isValid($this->_translations)) {
                $this->_validationMessage = $validator->getMessage();
                return false;
            }
        }

        return true;
    }

    /**
     * Returns last validation message
     *
     * @return null|string
     */
    public function getValidationMessage()
    {
        return $this->_validationMessage;
    }

    /**
     * Returns if the were translations removed while cleaning
     *
     * @return bool
     */
    public function wasFiltered()
    {
        return !empty($this->_filterMessages);
    }

    /**
     * Returns the cleaning errors
     *
     * @return array
     */
    public function getFilterMessages()
    {
        return $this->_filterMessages;
    }

    /**
     * @param FilterAbstract $filter
     */
    public function addFilter(FilterAbstract $filter)
    {
        $this->_filters[] = $filter;
    }

    /**
     * @throws \Exception
     *
     * @return bool   error state
     */
    protected function _applyFilters()
    {
        // skip if already cleaned
        if ($this->_currentState == self::__FILTERED__) {
            return $this->wasFiltered();
        }

        $this->_filterMessages = array();

        // skip if not translations available
        if (!$this->hasTranslations()) {
            $this->_currentState = self::__FILTERED__;
            return false;
        }

        $cleanedTranslations = $this->_translations;

        foreach ($this->_filters AS $filter) {

            $cleanedTranslations = $filter->filter($cleanedTranslations);
            $filteredData = $filter->getFilteredData();
            if (!empty($filteredData)) {
                $this->_filterMessages[] = get_class($filter) . " changed: " .var_export($filteredData, 1);
            }
        }

        $this->_currentState = self::__FILTERED__;

        if ($cleanedTranslations != $this->_translations) {
            $this->_filterMessages[] = 'translations have been cleaned';
        }

        $this->_translations = $cleanedTranslations;
        return $this->wasFiltered();
    }
}
