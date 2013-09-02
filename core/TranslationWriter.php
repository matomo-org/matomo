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
namespace Piwik;

use Exception;
use Piwik\Common;

/**
 * Write translations to file
 *
 * @package Piwik
 */
class TranslationWriter
{
    static private $baseTranslation = null;
    static public $disableJsonOptions = false;

    /**
     * Clean a string that may contain HTML special chars, single/double quotes, HTML entities, leading/trailing whitespace
     *
     * @param string $s
     * @return string
     */
    static public function clean($s)
    {
        return html_entity_decode(trim($s), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Quote a "C" string
     *
     * @param string $s
     * @return string
     */
    static public function quote($s)
    {
        return "'" . addcslashes($s, "'") . "'";
    }

    /**
     * Get translation file path
     *
     * @param string $lang ISO 639-1 alpha-2 language code
     * @param string $base Optional base directory (either 'lang' or 'tmp')
     * @throws Exception
     * @return string path
     */
    static public function getTranslationPath($lang, $base = 'lang')
    {
        if (!Common::isValidFilename($lang) ||
            ($base !== 'lang' && $base !== 'tmp')
        ) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($lang)));
        }

        return PIWIK_INCLUDE_PATH . '/' . $base . '/' . $lang . '.json';
    }

    /**
     * Load translations from file
     *
     * @param string $lang ISO 639-1 alpha-2 language code
     * @throws Exception
     * @return array $translations Array of translations ( key => translated string )
     */
    static public function loadTranslation($lang)
    {
        $path = self::getTranslationPath($lang);
        if (!is_readable($path)) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($lang)));
        }

        $data = file_get_contents($path);
        $translations = json_decode($data, true);
        return $translations;
    }

    /**
     * Output translations to string
     *
     * @param array $translations multidimensional Array of translations ( plugin => array (key => translated string ) )
     * @return string
     */
    static public function outputTranslation($translations)
    {
        if (!self::$baseTranslation) {
            self::$baseTranslation = self::loadTranslation('en');
        }
        $en = self::$baseTranslation;

        $cleanedTranslations = array();

        // filter out all translations that don't exist in english translations
        foreach ($en AS $plugin => $enTranslations) {
            foreach ($enTranslations as $key => $en_translation) {
                if (isset($translations[$plugin][$key]) && !empty($translations[$plugin][$key])) {
                    $cleanedTranslations[$plugin][$key] = $translations[$plugin][$key];
                }
            }
        }

        $options = 0;
        if (!self::$disableJsonOptions) {
            if (defined('JSON_UNESCAPED_UNICODE')) {
                $options |= JSON_UNESCAPED_UNICODE;
            }
            if (defined('JSON_PRETTY_PRINT')) {
                $options |= JSON_PRETTY_PRINT;
            }
        }
        return json_encode($cleanedTranslations, $options);
    }

    /**
     * Save translations to file; translations should already be cleansed.
     *
     * @param array $translations Array of translations ( key => translated string )
     * @param string $destinationPath Path of file to save translations to
     * @return bool|int False if failure, or number of bytes written
     */
    static public function saveTranslation($translations, $destinationPath)
    {
        return file_put_contents($destinationPath, self::outputTranslation($translations));
    }
}