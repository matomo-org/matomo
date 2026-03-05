<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Translation\Loader;

/**
 * Loads a pseudo-language for developers.
 */
class DevelopmentLoader implements LoaderInterface
{
    public const LANGUAGE_ID = 'dev';

    private const MAP = [
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
        "s" => 'ş',
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

    public function load($language, array $directories)
    {
        if ($language !== self::LANGUAGE_ID) {
            return $this->loader->load($language, $directories);
        }

        return $this->getPseudoLocale($directories);
    }

    /**
     * @param string[] $directories
     * @return array<string, array<string, string>>
     */
    private function getPseudoLocale(array $directories): array
    {
        $fallbackTranslations = $this->loader->load($this->fallbackLanguage, $directories);

        $translations = [];
        foreach ($fallbackTranslations as $section => $sectionFallbackTranslations) {
            if ($section === 'Intl') {
                $translations[$section] = $sectionFallbackTranslations;
                continue;
            }

            $sectionTranslations = [];
            foreach ($sectionFallbackTranslations as $key => $translation) {
                $sectionTranslations[$key] = $this->pseudoLocalize($translation);
            }

            $translations[$section] = $sectionTranslations;
        }

        return $translations;
    }

    private function pseudoLocalize(string $translation): string
    {
        $protectedTokens = [];
        $tokenized = preg_replace_callback(
            "/<[^>]+>|&[A-Za-z0-9#]+;|%%|%(?:[0-9]+[$])?[+\\-0'#]*[0-9]*(?:\\.[0-9]+)?[bcdeEfFgGosuxX]/",
            function ($matches) use (&$protectedTokens) {
                $token = "\x1D" . count($protectedTokens) . "\x1E";
                $protectedTokens[$token] = $matches[0];
                return $token;
            },
            $translation
        );

        if ($tokenized === null) {
            $tokenized = $translation;
        }

        $accented = strtr($tokenized, self::MAP);
        if (!empty($protectedTokens)) {
            $accented = strtr($accented, $protectedTokens);
        }

        return "[" . $accented . "]";
    }
}
