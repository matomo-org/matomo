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

/**
 * Write translations to file
 *
 * @package Piwik
 */
class Piwik_TranslationWriter
{
    static private $baseTranslation = null;

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
        if (!Piwik_Common::isValidFilename($lang) ||
            ($base !== 'lang' && $base !== 'tmp')
        ) {
            throw new Exception(Piwik_TranslateException('General_ExceptionLanguageFileNotFound', array($lang)));
        }

        return PIWIK_INCLUDE_PATH . '/' . $base . '/' . $lang . '.php';
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

        require $path;
        return $translations;
    }

    /**
     * Output translations to string
     *
     * @param array $translations Array of translations ( key => translated string )
     * @return string
     */
    static public function outputTranslation($translations)
    {
        if (!self::$baseTranslation) {
            self::$baseTranslation = self::loadTranslation('en');
        }
        $en = self::$baseTranslation;

        $output = array('<?php', '$translations = array(');
        $deferoutput = array();

        /* write all the translations that exist in en.php */
        foreach ($en as $key => $en_translation) {
            if (isset($translations[$key]) && !empty($translations[$key])) {
                $tmp = self::quote($translations[$key]);
                $output[] = "\t'$key' => $tmp,";
            }
        }

        /* write the remaining translations (that don't exist in en.php) */
        foreach ($translations as $key => $translation) {
            if (!isset($en[$key]) && !empty($translation)) {
                $tmp = self::quote($translation);
                $deferoutput[] = "\t'$key' => $tmp,";
            }
        }

        if (count($deferoutput) > 0) {
            $output[] = "\n\t// FOR REVIEW";
            $output = array_merge($output, $deferoutput);
        }

        $output[] = ');';

        return implode($output, "\n") . "\n";
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
