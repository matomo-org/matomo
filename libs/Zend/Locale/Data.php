<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Data.php 4521 2007-04-17 09:41:35Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * include needed classes
 */
require_once 'Zend/Locale.php';
require_once 'Zend/Locale/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Locale
 * @subpackage Data
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Locale_Data
{
    /**
     * locale files
     *
     * @var ressource
     * @access private
     */
    private static $_ldml = array();


    /**
     * list of values which are collected
     *
     * @var array
     * @access private
     */
    private static $_list = array();


    /**
     * Read the content from locale
     *
     * Can be called like:
     * <ldml>
     *     <delimiter>test</delimiter>
     *     <second type='myone'>content</second>
     *     <second type='mysecond'>content2</second>
     *     <third type='mythird' />
     * </ldml>
     *
     * Case 1: _readFile('ar','/ldml/delimiter')             -> returns [] = test
     * Case 1: _readFile('ar','/ldml/second[@type=myone]')   -> returns [] = content
     * Case 2: _readFile('ar','/ldml/second','type')         -> returns [myone] = content; [mysecond] = content2
     * Case 3: _readFile('ar','/ldml/delimiter',,'right')    -> returns [right] = test
     * Case 4: _readFile('ar','/ldml/third','type','myone')  -> returns [myone] = mythird
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $attribute
     * @param  string $value
     * @access private
     */
    private static function _readFile($locale, $path, $attribute, $value)
    {

        // ohne attribute - alle Werte auslesen
        // mit attribute - nur diesen Wert auslesen
        if (!empty(self::$_ldml[(string) $locale])) {

            $result = self::$_ldml[(string) $locale]->xpath($path);
            if (!empty($result)) {
                foreach ($result as &$found) {

                    if (empty($value)) {

                        if (empty($attribute)) {
                            // Case 1
                            self::$_list[] = (string) $found;
                        } else if (empty(self::$_list[(string) $found[$attribute]])){
                            // Case 2
                            self::$_list[(string) $found[$attribute]] = (string) $found;
                        }

                    } else if (empty (self::$_list[$value])) {

                        if (empty($attribute)) {
                            // Case 3
                            self::$_list[$value] = (string) $found;
                        } else {
                            // Case 4
                           self::$_list[$value] = (string) $found[$attribute];
                        }

                    }
                }
            }
        }
    }

    /**
     * Find possible routing to other path or locale
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $attribute
     * @param  string $value
     * @throws Zend_Locale_Exception
     * @access private
     */
    private static function _findRoute($locale, $path, $attribute, $value)
    {
        // load locale file if not already in cache
        // needed for alias tag when referring to other locale
        if (empty(self::$_ldml[(string) $locale])) {
            $filename = dirname(__FILE__) . '/Data/' . $locale . '.xml';
            if (!file_exists($filename)) {
                throw new Zend_Locale_Exception("Missing locale file '$filename' for '$locale' locale.");
            }

            self::$_ldml[(string) $locale] = simplexml_load_file($filename);
        }

        // search for 'alias' tag in the search path for redirection
        $search = '';
        $tok = strtok($path, '/');

        // parse the complete path
        if (!empty(self::$_ldml[(string) $locale])) {
            while ($tok !== false) {
                $search = $search . '/' . $tok;
                if ((strpos($tok, '[@') !== false) and (strpos($tok, ']') === false)) {
                    $tok = strtok('/');
                    continue;
                }
                $result = self::$_ldml[(string) $locale]->xpath($search . '/alias');

                // alias found
                if (!empty($result)) {

                    $source = $result[0]['source'];
                    $newpath = $result[0]['path'];

                    // new path - path //ldml is to ignore
                    if ($newpath != '//ldml') {
                        // other path - parse to make real path

                        while (substr($newpath,0,3) == '../') {
                            $newpath = substr($newpath, 3);
                            $search = substr($search, 0, strrpos($search, '/'));
                        }

                        // truncate ../ to realpath otherwise problems with alias
                        $path = $search . '/' . $newpath;
                        while (($tok = strtok('/'))!== false) {
                            $path = $path . '/' . $tok;
                        }
                    }

                    // reroute to other locale
                    if ($source != 'locale') {
                        $locale = $source;
                    }

                    self::_getFile($locale, $path, $attribute, $value);
                    return false;
                }

                $tok = strtok('/');
            }
        }
        return true;
    }


    /**
     * Read the right LDML file
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $attribute
     * @param  string $value
     * @access private
     */
    private static function _getFile($locale, $path, $attribute = false, $value = false)
    {
        $result = self::_findRoute($locale, $path, $attribute, $value);
        if ($result) {
            self::_readFile($locale, $path, $attribute, $value);
        }

        // parse required locales reversive
        // example: when given zh_Hans_CN
        // 1. -> zh_Hans_CN
        // 2. -> zh_Hans
        // 3. -> zh
        // 4. -> root
        if (($locale != 'root') && ($result)) {
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));
            if (!empty($locale)) {
                self::_getFile($locale, $path, $attribute, $value);
            } else {
                self::_getFile('root', $path, $attribute, $value);
            }
        }
    }


    /**
     * Read the LDML file, get a single path defined value
     *
     * @param  string $locale
     * @param  string $path
     * @param  string $value
     * @return array of string
     * @access public
     */
    public static function getContent($locale, $path, $value = false)
    {
        self::$_list = array();

        if (empty($locale)) {
            $locale = new Zend_Locale();
        }

        if (!Zend_Locale::isLocale($locale)) {
            throw new Zend_Locale_Exception("Locale ($locale) is a unknown locale");
        }

        if ($locale instanceof Zend_Locale) {
            $locale = $locale->toString();
        }

        switch(strtolower($path)) {
            case 'languagelist':
                self::_getFile($locale, '/ldml/localeDisplayNames/languages/language', 'type');
                break;

            case 'language':
                self::_getFile($locale, '/ldml/localeDisplayNames/languages/language[@type=\''
                             . $value . '\']', 'type');
                break;

            case 'scriptlist':
                self::_getFile($locale, '/ldml/localeDisplayNames/scripts/script', 'type');
                break;

            case 'script':
                self::_getFile($locale, '/ldml/localeDisplayNames/scripts/script[@type=\''
                             . $value . '\']', 'type');
                break;

            case 'territorylist':
                self::_getFile($locale, '/ldml/localeDisplayNames/territories/territory', 'type');
                break;

            case 'territory':
                self::_getFile($locale, '/ldml/localeDisplayNames/territories/territory[@type=\''
                             . $value . '\']', 'type');
                break;

            case 'variantlist':
                self::_getFile($locale, '/ldml/localeDisplayNames/variants/variant', 'type');
                break;

            case 'variant':
                self::_getFile($locale, '/ldml/localeDisplayNames/variants/variant[@type=\''
                             . $value . '\']', 'type');
                break;

            case 'keylist':
                self::_getFile($locale, '/ldml/localeDisplayNames/keys/key', 'type');
                break;

            case 'key':
                self::_getFile($locale, '/ldml/localeDisplayNames/keys/key[@type=\''
                             . $value . '\']', 'type');
                break;

            case 'typelist':
                self::_getFile($locale, '/ldml/localeDisplayNames/types/type', 'type');
                break;

            case 'type':
                if (($value == 'calendar') or
                    ($value == 'collation') or
                    ($value == 'currency')) {
                    self::_getFile($locale, '/ldml/localeDisplayNames/types/type[@key=\''
                                 . $value . '\']', 'type');
                } else {
                    self::_getFile($locale, '/ldml/localeDisplayNames/types/type[@type=\''
                                 . $value . '\']', 'type');
                }
                break;

            case 'orientation':
                self::_getFile($locale, '/ldml/layout/orientation', 'lines',      'lines');
                self::_getFile($locale, '/ldml/layout/orientation', 'characters', 'characters');
                break;

            case 'casing':
                self::_getFile($locale, '/ldml/layout/inList', 'casing', 'casing');
                break;

            case 'characters':
                self::_getFile($locale, '/ldml/characters/exemplarCharacters');
                break;

            case 'delimiters':
                self::_getFile($locale, '/ldml/delimiters/quotationStart',          '', 'quoteStart');
                self::_getFile($locale, '/ldml/delimiters/quotationEnd',            '', 'quoteEnd');
                self::_getFile($locale, '/ldml/delimiters/alternateQuotationStart', '', 'quoteStartAlt');
                self::_getFile($locale, '/ldml/delimiters/alternateQuotationEnd',   '', 'quoteEndAlt');
                break;

            case 'measurement':
                self::_getFile($locale, '/ldml/measurement/measurementSystem', 'type', 'measurement');
                break;

            case 'papersize':
                self::_getFile($locale, '/ldml/measurement/paperSize/height', '', 'height');
                self::_getFile($locale, '/ldml/measurement/paperSize/width',  '', 'width');
                break;

            case 'datechars':
                self::_getFile($locale, '/ldml/dates/localizedPatternChars', '', 'chars');
                break;

            case 'defcalendarformat':
                self::_getFile($locale, '/ldml/dates/calendars/default', 'type', 'default');
                break;

            case 'defmonthformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/months/monthContext[@type=\'format\']/default', 'type', 'default');
                break;

            case 'monthlist':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/months/monthContext[@type=\''
                             . $value[1] . '\']/monthWidth[@type=\''
                             . $value[2] . '\']/month', 'type');
                break;

            case 'month':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/months/monthContext[@type=\''
                             . $value[1] . '\']/monthWidth[@type=\''
                             . $value[2] . '\']/month[@type=\'' . $value[3] . '\']', 'type');
                break;

            case 'defdayformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/days/dayContext[@type=\'format\']/default', 'type', 'default');
                break;

            case 'daylist':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/days/dayContext[@type=\''
                             . $value[1] . '\']/dayWidth[@type=\''
                             . $value[2] . '\']/day', 'type');
                break;

            case 'day':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/days/dayContext[@type=\''
                             . $value[1] . '\']/dayWidth[@type=\''
                             . $value[2] . '\']/day[@type=\'' . $value[3] . '\']', 'type');
                break;

            case 'week':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/minDays', 'count', 'mindays');
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/firstDay', 'day', 'firstday');
                break;

            case 'weekend':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/weekendStart', 'day', 'startday');
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/weekendStart', 'time', 'starttime');
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/weekendEnd', 'day', 'endday');
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/week/weekendEnd', 'time', 'endtime');
                break;

            case 'daytime':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/am', '', 'am');
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/pm', '', 'pm');
                break;

            case 'erashortlist':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/eras/eraAbbr/era', 'type');
                break;

            case 'erashort':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/eras/eraAbbr/era[@type=\'' . $value[1] . '\']', 'type');
                break;

            case 'eralist':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/eras/eraNames/era', 'type');
                break;

            case 'era':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/eras/eraNames/era[@type=\'' . $value[1] . '\']', 'type');
                break;

            case 'defdateformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/dateFormats/default', 'choice', 'default');
                break;

            case 'dateformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/dateFormats/dateFormatLength[@type=\''
                             . $value[1] . '\']/dateFormat/pattern', '', 'pattern');
                break;

            case 'deftimeformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/timeFormats/default', 'choice', 'default');
                break;

            case 'timeformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/timeFormats/timeFormatLength[@type=\''
                             . $value[1] . '\']/timeFormat/pattern', '', 'pattern');
                break;

            case 'datetimeformat':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/dateTimeFormats/dateTimeFormatLength/dateTimeFormat/pattern',
                               '', 'pattern');
                break;

            case 'calendarfields':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/fields/field', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                                 . $value . '\']/fields/field[@type=\'' . $key . '\']/displayName', '', $key);
                }
                break;

            case 'relativedates':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value . '\']/fields/relative', 'type');
                break;

            case 'relativedate':
                self::_getFile($locale, '/ldml/dates/calendars/calendar[@type=\''
                             . $value[0] . '\']/fields/relative[@type=\'' . $value[1] . '\']', 'type');
                break;

            case 'timezones':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                                 . $key . '\']/exemplarCity', '', $key);
                }
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                break;

            case 'timezone':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                             . $value . '\']/exemplarCity', '', $value);
                break;

            case 'timezonestandard':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone','type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                                 . $key . '\']/long/standard', '', $key);
                }
                break;

            case 'timezonestandardshort':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                                 . $key . '\']/short/standard', '', $key);
                }
                break;

            case 'timezonedaylight':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                                 . $key . '\']/long/daylight', '', $key);
                }
                break;

            case 'timezonedaylightshort':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/zone', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/dates/timeZoneNames/zone[@type=\''
                                 . $key . '\']/short/daylight', '', $key);
                }
                break;

            case 'timezoneformat':
                self::_getFile($locale, '/ldml/dates/timeZoneNames/hourFormat',     '', 'hourformat');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/hoursFormat',    '', 'hoursformat');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/gmtFormat',      '', 'gmtformat');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/regionFormat',   '', 'regionformat');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/fallbackFormat', '', 'fallbackformat');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/abbreviationFallback',
                                        'type', 'abbreviationfallback');
                self::_getFile($locale, '/ldml/dates/timeZoneNames/preferenceOrdering',
                                        'type', 'preferenceOrdering');
                break;

            case 'numbersymbols':
                self::_getFile($locale, '/ldml/numbers/symbols/decimal',         '', 'decimal');
                self::_getFile($locale, '/ldml/numbers/symbols/group',           '', 'group');
                self::_getFile($locale, '/ldml/numbers/symbols/list',            '', 'list');
                self::_getFile($locale, '/ldml/numbers/symbols/percentSign',     '', 'percent');
                self::_getFile($locale, '/ldml/numbers/symbols/nativeZeroDigit', '', 'zero');
                self::_getFile($locale, '/ldml/numbers/symbols/patternDigit',    '', 'pattern');
                self::_getFile($locale, '/ldml/numbers/symbols/plusSign',        '', 'plus');
                self::_getFile($locale, '/ldml/numbers/symbols/minusSign',       '', 'minus');
                self::_getFile($locale, '/ldml/numbers/symbols/exponential',     '', 'exponent');
                self::_getFile($locale, '/ldml/numbers/symbols/perMille',        '', 'mille');
                self::_getFile($locale, '/ldml/numbers/symbols/infinity',        '', 'infinity');
                self::_getFile($locale, '/ldml/numbers/symbols/nan',             '', 'nan');
                break;

            case 'decimalnumberformat':
                self::_getFile($locale, '/ldml/numbers/decimalFormats/decimalFormatLength/decimalFormat/pattern',
                                        '', 'default');
                break;

            case 'scientificnumberformat':
                self::_getFile($locale, 
                               '/ldml/numbers/scientificFormats/scientificFormatLength/scientificFormat/pattern',
                               '', 'default');
                break;

            case 'percentnumberformat':
                self::_getFile($locale, '/ldml/numbers/percentFormats/percentFormatLength/percentFormat/pattern',
                                        '', 'default');
                break;

            case 'currencyformat':
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern',
                               '', 'default');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/beforeCurrency/currencyMatch',
                               '', 'beforMatch');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/beforeCurrency/surroundingMatch',
                               '', 'beforSurround');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/beforeCurrency/insertBetween',
                               '', 'beforBetween');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/afterCurrency/currencyMatch',
                               '', 'afterMatch');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/afterCurrency/surroundingMatch',
                               '', 'afterSurround');
                self::_getFile($locale, 
                               '/ldml/numbers/currencyFormats/currencySpacing/afterCurrency/insertBetween',
                               '', 'afterBetween');
                break;

            case 'currencynames':
                self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\''
                                 . $key . '\']/displayName', '', $key);
                }
                break;

            case 'currencyname':
                self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\''
                             . $value . '\']/displayName', '', $value);
                break;

            case 'currencysymbols':
                self::_getFile($locale, '/ldml/numbers/currencies/currency', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\''
                                 . $key . '\']/symbol', '', $key);
                }
                break;

            case 'currencysymbol':
                self::_getFile($locale, '/ldml/numbers/currencies/currency[@type=\''
                             . $value . '\']/symbol', '', $value);
                break;

            case 'questionstrings':
                self::_getFile($locale, '/ldml/posix/messages/yesstr',  '', 'yes');
                self::_getFile($locale, '/ldml/posix/messages/nostr',   '', 'no');
                self::_getFile($locale, '/ldml/posix/messages/yesexpr', '', 'yesexpr');
                self::_getFile($locale, '/ldml/posix/messages/noexpr',  '', 'noexpr');
                break;

            case 'currencyfraction':
                self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\''
                             . $value . '\']', 'digits', 'digits');
                self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\''
                             . $value . '\']', 'rounding', 'rounding');
                break;

            case 'currencydigitlist':
                self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info', 'iso4217');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info[@iso4217=\''
                                 . $key . '\']', 'digits', $key);
                }
                break;

            case 'currencyroundinglist':
                self::_getFile('supplementalData', '/supplementalData/currencyData/fractions/info', 'iso4217');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData',
                                   '/supplementalData/currencyData/fractions/info[@iso4217=\''
                                 . $key . '\']', 'rounding', $key);
                }
                break;

            case 'currencyforregion':
                self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\''
                             . $value . '\']/currency', 'iso4217');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $keyvalue) {
                    self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\''
                             . $value . '\']/currency[@iso4217=\'' . $key . '\']', 'from', $key);
                }
                break;

            case 'currencyforregionlist':
                self::_getFile('supplementalData', '/supplementalData/currencyData/region', 'iso3166');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData', '/supplementalData/currencyData/region[@iso3166=\''
                                 . $key . '\']/currency', 'iso4217', $key);
                }
                break;

            case 'regionforterritory':
                self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\''
                             . $value . '\']', 'contains', $value);
                break;

            case 'regionforterritorylist':
                self::_getFile('supplementalData', '/supplementalData/territoryContainment/group', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData', '/supplementalData/territoryContainment/group[@type=\''
                                 . $key . '\']', 'contains', $key);
                }
                break;

            case 'scriptforlanguage':
                self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\''
                             . $value . '\']', 'scripts', $value);
                break;

            case 'scriptforlanguagelist':
                self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\''
                                 . $key . '\']', 'scripts', $key);
                }
                break;

            case 'territoryforlanguage':
                self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\''
                             . $value . '\']', 'territories', $value);
                break;

            case 'territoryforlanguagelist':
                self::_getFile('supplementalData', '/supplementalData/languageData/language', 'type');
                $_temp = self::$_list;
                self::$_list = array();
                foreach ($_temp as $key => $found) {
                    self::_getFile('supplementalData', '/supplementalData/languageData/language[@type=\''
                                 . $key . '\']', 'territories', $key);
                }
                break;
            default :
                throw new Zend_Locale_Exception("Unknown detail ($path) for parsing locale data.");
                break;
        }
        return self::$_list;
    }
}
