<?php

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
