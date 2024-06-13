<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

/**
 * Loads a pseudo-language for developers where translation are equal to translation ids.
 */
class DevelopmentLoader implements LoaderInterface
{
    public const LANGUAGE_ID = 'dev';

    /**
     * from https://github.com/tryggvigy/pseudo-localization/blob/master/src/localize.js
     * by Tryggvi Gylfason
     * MIT license
     */
    const MAP = [
        "a" => 'ȧ',
        "A" => 'Ȧ',
        "b" => 'ƀ',
        "B" => 'Ɓ',
        "c" => 'ƈ',
        "C" => 'Ƈ',
        "d" => 'ḓ',
        "D" => 'Ḓ',
        "e" => 'ḗ',
        "E" => 'Ḗ',
        "f" => 'ƒ',
        "F" => 'Ƒ',
        "g" => 'ɠ',
        "G" => 'Ɠ',
        "h" => 'ħ',
        "H" => 'Ħ',
        "i" => 'ī',
        "I" => 'Ī',
        "j" => 'ĵ',
        "J" => 'Ĵ',
        "k" => 'ķ',
        "K" => 'Ķ',
        "l" => 'ŀ',
        "L" => 'Ŀ',
        "m" => 'ḿ',
        "M" => 'Ḿ',
        "n" => 'ƞ',
        "N" => 'Ƞ',
        "o" => 'ǿ',
        "O" => 'Ǿ',
        "p" => 'ƥ',
        "P" => 'Ƥ',
        "q" => 'ɋ',
        "Q" => 'Ɋ',
        "r" => 'ř',
        "R" => 'Ř',
//        "s" => 'ş', // avoid breaking format strings
        "S" => 'Ş',
        "t" => 'ŧ',
        "T" => 'Ŧ',
        "v" => 'ṽ',
        "V" => 'Ṽ',
        "u" => 'ŭ',
        "U" => 'Ŭ',
        "w" => 'ẇ',
        "W" => 'Ẇ',
        "x" => 'ẋ',
        "X" => 'Ẋ',
        "y" => 'ẏ',
        "Y" => 'Ẏ',
        "z" => 'ẑ',
        "Z" => 'Ẑ',
    ];

    /**
     * Decorated loader.
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var string
     */
    private $fallbackLanguage = 'en';

    /**
     * @param LoaderInterface $loader Decorate another loader to add the pseudo-language.
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load($language, array $directories)
    {
        if ($language !== self::LANGUAGE_ID) {
            return $this->loader->load($language, $directories);
        }

        return $this->getPseudoLocale($directories);
    }

    private function getDevelopmentTranslations(array $directories)
    {
        $fallbackTranslations = $this->loader->load($this->fallbackLanguage, $directories);

        $translations = array();
        foreach ($fallbackTranslations as $section => $sectionFallbackTranslations) {
            $translationIds = array_keys($sectionFallbackTranslations);
            $sectionTranslations = $this->prefixTranslationsWithSection($section, $translationIds);

            $translations[$section] = array_combine($translationIds, $sectionTranslations);
        }

        return $translations;
    }

    private function getPseudoLocale(array $directories)
    {
        $fallbackTranslations = $this->loader->load($this->fallbackLanguage, $directories);

        $translations = [];
        foreach ($fallbackTranslations as $section => $sectionFallbackTranslations) {
            if ($section === 'Intl') {
                $translations[$section] = $sectionFallbackTranslations;
                continue;
            }

            $translations[$section] = array_map(function ($translation) {
                $accented = strtr($translation, self::MAP);
                return "[" . $accented . "]";
            }, $sectionFallbackTranslations);
        }

        return $translations;
    }

    private function prefixTranslationsWithSection($section, $translationIds)
    {
        return array_map(function ($translation) use ($section) {
            return $section . '_' . $translation;
        }, $translationIds);
    }
}
