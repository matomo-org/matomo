<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Intl;

class Locale
{
    public static function setLocale($locale)
    {
        $localeVariant = str_replace('UTF-8', 'UTF8', $locale);

        setlocale(LC_ALL, $locale, $localeVariant);
        setlocale(LC_CTYPE, '');
    }

    public static function setDefaultLocale()
    {
        self::setLocale('en_US.UTF-8');
    }
}
