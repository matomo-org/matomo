<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Translate;

use Exception;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Translate\Filter\FilterAbstract;
use Piwik\Translate\Validate\ValidateAbstract;

/**
 * Writes clean translations to file
 *
 */
class Writer
{
    /**
     * current language to write files for
     *
     * @var string
     */
    protected $language = '';

    /**
     * Name of a plugin (if set in contructor)
     *
     * @var string|null
     */
    protected $pluginName = null;

    /**
     * translations to write to file
     *
     * @var array
     */
    protected $translations = array();

    /**
     * Validators to check translations with
     *
     * @var ValidateAbstract[]
     */
    protected $validators = array();

    /**
     * Message why validation failed
     *
     * @var string|null
     */
    protected $validationMessage = null;

    /**
     * Filters to to apply to translations
     *
     * @var FilterAbstract[]
     */
    protected $filters = array();

    /**
     * Messages which filter changed the data
     *
     * @var array
     */
    protected $filterMessages = array();

    const UNFILTERED = 'unfiltered';
    const FILTERED   = 'filtered';

    protected $currentState = self::UNFILTERED;

    /**
     * If $pluginName is given, Writer will be initialized for the given plugin if it exists
     * Otherwise it will be initialized for core translations
     *
     * @param string $language ISO 639-1 alpha-2 language code
     * @param string $pluginName optional plugin name
     * @throws \Exception
     */
    public function __construct($language, $pluginName = null)
    {
        $this->setLanguage($language);

        if (!empty($pluginName)) {
            $installedPlugins = \Piwik\Plugin\Manager::getInstance()->readPluginsDirectory();

            if (!in_array($pluginName, $installedPlugins)) {

                throw new Exception(Piwik::translate('General_ExceptionLanguageFileNotFound', array($pluginName)));
            }

            $this->pluginName = $pluginName;
        }
    }

    /**
     * @param string $language ISO 639-1 alpha-2 language code
     *
     * @throws \Exception
     */
    public function setLanguage($language)
    {
        if (!preg_match('/^([a-z]{2,3}(-[a-z]{2,3})?)$/i', $language)) {
            throw new Exception(Piwik::translate('General_ExceptionLanguageFileNotFound', array($language)));
        }

        $this->language = strtolower($language);
    }

    /**
     * @return string ISO 639-1 alpha-2 language code
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Returns if there are translations available or not
     * @return bool
     */
    public function hasTranslations()
    {
        return !empty($this->translations);
    }

    /**
     * Set the translations to write (and cleans them)
     *
     * @param $translations
     */
    public function setTranslations($translations)
    {
        $this->currentState = self::UNFILTERED;
        $this->translations = $translations;
        $this->applyFilters();
    }

    /**
     * Get translations from file
     *
     * @param  string $lang ISO 639-1 alpha-2 language code
     * @throws Exception
     * @return array   Array of translations ( plugin => ( key => translated string ) )
     */
    public function getTranslations($lang)
    {
        $path = $this->getTranslationPathBaseDirectory('lang', $lang);

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
        return $this->getTranslationPathBaseDirectory('tmp');
    }

    /**
     * Returns the path to translation files
     *
     * @return string
     */
    public function getTranslationPath()
    {
        return $this->getTranslationPathBaseDirectory('lang');
    }

    /**
     * Get translation file path based on given params
     *
     * @param string $base Optional base directory (either 'lang' or 'tmp')
     * @param string|null $lang forced language
     * @throws \Exception
     * @return string path
     */
    protected function getTranslationPathBaseDirectory($base, $lang = null)
    {
        if (empty($lang)) {
            $lang = $this->getLanguage();
        }

        if (!empty($this->pluginName)) {

            if ($base == 'tmp') {
                return sprintf('%s/tmp/plugins/%s/lang/%s.json', PIWIK_INCLUDE_PATH, $this->pluginName, $lang);
            } else {
                return sprintf('%s/plugins/%s/lang/%s.json', PIWIK_INCLUDE_PATH, $this->pluginName, $lang);
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
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $options |= JSON_UNESCAPED_UNICODE;
        }
        if (defined('JSON_PRETTY_PRINT')) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->translations, $options);
    }

    /**
     * Save translations to file; translations should already be cleaned.
     *
     * @throws \Exception
     * @return bool|int  False if failure, or number of bytes written
     */
    public function save()
    {
        $this->applyFilters();

        if (!$this->hasTranslations() || !$this->isValid()) {
            throw new Exception('unable to save empty or invalid translations');
        }

        $path = $this->getTranslationPath();

        Filesystem::mkdir(dirname($path));

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
        $this->applyFilters();

        if (!$this->hasTranslations() || !$this->isValid()) {
            throw new Exception('unable to save empty or invalid translations');
        }

        $path = $this->getTemporaryTranslationPath();

        Filesystem::mkdir(dirname($path));

        return file_put_contents($path, $this->__toString());
    }

    /**
     * Adds an validator to check before saving
     *
     * @param ValidateAbstract $validator
     */
    public function addValidator(ValidateAbstract $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Returns if translations are valid to save or not
     *
     * @return bool
     */
    public function isValid()
    {
        $this->applyFilters();

        $this->validationMessage = null;

        foreach ($this->validators as $validator) {
            if (!$validator->isValid($this->translations)) {
                $this->validationMessage = $validator->getMessage();
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
        return $this->validationMessage;
    }

    /**
     * Returns if the were translations removed while cleaning
     *
     * @return bool
     */
    public function wasFiltered()
    {
        return !empty($this->filterMessages);
    }

    /**
     * Returns the cleaning errors
     *
     * @return array
     */
    public function getFilterMessages()
    {
        return $this->filterMessages;
    }

    /**
     * @param FilterAbstract $filter
     */
    public function addFilter(FilterAbstract $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @throws \Exception
     *
     * @return bool   error state
     */
    protected function applyFilters()
    {
        // skip if already cleaned
        if ($this->currentState == self::FILTERED) {
            return $this->wasFiltered();
        }

        $this->filterMessages = array();

        // skip if not translations available
        if (!$this->hasTranslations()) {
            $this->currentState = self::FILTERED;
            return false;
        }

        $cleanedTranslations = $this->translations;

        foreach ($this->filters as $filter) {

            $cleanedTranslations = $filter->filter($cleanedTranslations);
            $filteredData = $filter->getFilteredData();
            if (!empty($filteredData)) {
                $this->filterMessages[] = get_class($filter) . " changed: " . var_export($filteredData, 1);
            }
        }

        $this->currentState = self::FILTERED;

        if ($cleanedTranslations != $this->translations) {
            $this->filterMessages[] = 'translations have been cleaned';
        }

        $this->translations = $cleanedTranslations;
        return $this->wasFiltered();
    }
}
