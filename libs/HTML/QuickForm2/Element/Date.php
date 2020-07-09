<?php
/**
 * Date element
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2009, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Date.php 297453 2010-04-04 10:22:39Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 group of elements
 */
// require_once 'HTML/QuickForm2/Container/Group.php';
/**
 * Base class for HTML_QuickForm2 select element
 */
// require_once 'HTML/QuickForm2/Element/Select.php';

/**
 * Class for a group of elements used to input dates (and times).
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Date extends HTML_QuickForm2_Container_Group
{
    public function getType()
    {
        return 'date';
    }

   /**
    * Various options to control the element's display.
    * @var      array
    */
    protected $data = array(
        'language'           => 'en',
        'format'           => 'dMY',
        'minYear'          => 2001,
        'maxYear'          => 2010,
        'addEmptyOption'   => false,
        'emptyOptionValue' => '',
        'emptyOptionText'  => '&nbsp;',
        'optionIncrement'  => array('i' => 1, 's' => 1)
        );

   /**
    * Class constructor
    * 
    * The following keys may appear in $data array:
    * - 'language': date language
    * - 'format': Format of the date, based on PHP's date() function.
    *   The following characters are currently recognised in format string:
    *   <pre>  
    *       D => Short names of days
    *       l => Long names of days
    *       d => Day numbers
    *       M => Short names of months
    *       F => Long names of months
    *       m => Month numbers
    *       Y => Four digit year
    *       y => Two digit year
    *       h => 12 hour format
    *       H => 23 hour  format
    *       i => Minutes
    *       s => Seconds
    *       a => am/pm
    *       A => AM/PM
    *   </pre>
    * - 'minYear': Minimum year in year select
    * - 'maxYear': Maximum year in year select
    * - 'addEmptyOption': Should an empty option be added to the top of
    *    each select box?
    * - 'emptyOptionValue': The value passed by the empty option.
    * - 'emptyOptionText': The text displayed for the empty option.
    * - 'optionIncrement': Step to increase the option values by (works for 'i' and 's')
    *
    * @param    string  Element name
    * @param    mixed   Attributes (either a string or an array)
    * @param    array   Element data (label, options and data used for element creation)
    */
    public function __construct($name = null, $attributes = null, $data = null)
    {
        parent::__construct($name, $attributes, $data);

        $locale =& $this->locale[$this->data['language']];
        $backslash = false;
        $separators = array();
        $separator =  '';

        for ($i = 0, $length = strlen($this->data['format']); $i < $length; $i++) {
            $sign = $this->data['format'][$i];
            if ($backslash) {
                $backslash  = false;
                $separator .= $sign;
            } else {
                $loadSelect = true;
                switch ($sign) {
                    case 'D':
                        // Sunday is 0 like with 'w' in date()
                        $options = $locale['weekdays_short'];
                        break;
                    case 'l':
                        $options = $locale['weekdays_long'];
                        break;
                    case 'd':
                        $options = $this->createOptionList(1, 31);
                        break;
                    case 'M':
                        $options = $locale['months_short'];
                        array_unshift($options , '');
                        unset($options[0]);
                        break;
                    case 'm':
                        $options = $this->createOptionList(1, 12);
                        break;
                    case 'F':
                        $options = $locale['months_long'];
                        array_unshift($options , '');
                        unset($options[0]);
                        break;
                    case 'Y':
                        $options = $this->createOptionList(
                            $this->data['minYear'],
                            $this->data['maxYear'],
                            $this->data['minYear'] > $this->data['maxYear']? -1: 1
                        );
                        break;
                    case 'y':
                        $options = $this->createOptionList(
                            $this->data['minYear'],
                            $this->data['maxYear'],
                            $this->data['minYear'] > $this->data['maxYear']? -1: 1
                        );
                        array_walk($options, function (&$v, $k) {
                            $v = substr($v, -2);
                        });
                        break;
                    case 'h':
                        $options = $this->createOptionList(1, 12);
                        break;
                    case 'g':
                        $options = $this->createOptionList(1, 12);
                        array_walk($options, function (&$v, $k) {
                            $v = intval($v);
                        });
                        break;
                    case 'H':
                        $options = $this->createOptionList(0, 23);
                        break;
                    case 'i':
                        $options = $this->createOptionList(0, 59, $this->data['optionIncrement']['i']);
                        break;
                    case 's':
                        $options = $this->createOptionList(0, 59, $this->data['optionIncrement']['s']);
                        break;
                    case 'a':
                        $options = array('am' => 'am', 'pm' => 'pm');
                        break;
                    case 'A':
                        $options = array('AM' => 'AM', 'PM' => 'PM');
                        break;
                    case 'W':
                        $options = $this->createOptionList(1, 53);
                        break;
                    case '\\':
                        $backslash  = true;
                        $loadSelect = false;
                        break;
                    default:
                        $separator .= (' ' == $sign? '&nbsp;': $sign);
                        $loadSelect = false;
                }

                if ($loadSelect) {
                    if (0 < count($this)) {
                        $separators[] = $separator;
                    }
                    $separator = '';
                    // Should we add an empty option to the top of the select?
                    if (!is_array($this->data['addEmptyOption']) && $this->data['addEmptyOption'] ||
                        is_array($this->data['addEmptyOption']) && !empty($this->data['addEmptyOption'][$sign])) {

                        // Using '+' array operator to preserve the keys
                        if (is_array($this->data['emptyOptionText']) && !empty($this->data['emptyOptionText'][$sign])) {
                            $options = array($this->data['emptyOptionValue'] => $this->data['emptyOptionText'][$sign]) + $options;
                        } else {
                            $options = array($this->data['emptyOptionValue'] => $this->data['emptyOptionText']) + $options;
                        }
                    }
                    $this->addSelect($sign, $this->getAttributes())->loadOptions($options);
                }
            }
        }
        $separators[] = $separator . ($backslash? '\\': '');
        $this->setSeparator($separators);
    }

   /**
    * Creates an option list containing the numbers from the start number to the end, inclusive
    *
    * @param    int     The start number
    * @param    int     The end number
    * @param    int     Increment by this value
    * @return   array   An array of numeric options.
    */
    protected function createOptionList($start, $end, $step = 1)
    {
        for ($i = $start, $options = array(); $start > $end? $i >= $end: $i <= $end; $i += $step) {
            $options[$i] = sprintf('%02d', $i);
        }
        return $options;
    }

   /**
    * Trims leading zeros from the (numeric) string
    *
    * @param    string  A numeric string, possibly with leading zeros
    * @return   string  String with leading zeros removed
    */
    protected function trimLeadingZeros($str)
    {
        if (0 == strcmp($str, $this->data['emptyOptionValue'])) {
            return $str;
        }
        $trimmed = ltrim($str, '0');
        return strlen($trimmed)? $trimmed: '0';
    }


   /**
    * Tries to convert the given value to a usable date before setting the
    * element value
    * @param    int|string|array    A timestamp, a string compatible with strtotime()
    *                               or an array that fits the element names
    */
    public function setValue($value)
    {
        if (empty($value)) {
            $value = array();
        } elseif (is_scalar($value)) {
            if (!is_numeric($value)) {
                $value = strtotime($value);
            }
            // might be a unix epoch, then we fill all possible values
            $arr = explode('-', date('w-j-n-Y-g-G-i-s-a-A-W', (int)$value));
            $value = array(
                'D' => $arr[0],
                'l' => $arr[0],
                'd' => $arr[1],
                'M' => $arr[2],
                'm' => $arr[2],
                'F' => $arr[2],
                'Y' => $arr[3],
                'y' => $arr[3],
                'h' => $arr[4],
                'g' => $arr[4],
                'H' => $arr[5],
                'i' => $this->trimLeadingZeros($arr[6]),
                's' => $this->trimLeadingZeros($arr[7]),
                'a' => $arr[8],
                'A' => $arr[9],
                'W' => $this->trimLeadingZeros($arr[10])
            );
        } else {
            $value = array_map(array($this, 'trimLeadingZeros'), $value);
        }
        return parent::setValue($value);
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * Since the date element also accepts a timestamp as value, the default
    * group behavior is changed.
    */
    public function updateValue()
    {
        $name = $this->getName();
        foreach ($this->getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($name))) {
                $this->setValue($value);
                return;
            }
        }
        parent::updateValue();
    }

   /**
    * Options in different languages
    *
    * Note to potential translators: to avoid encoding problems please send
    * your translations with "weird" letters encoded as HTML Unicode entities
    *
    * @var      array
    */
    protected $locale = array(
        'en'    => array (
            'weekdays_short'=> array ('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
            'weekdays_long' => array ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'months_long'   => array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December')
        ),
        'de'    => array (
            'weekdays_short'=> array ('So', 'Mon', 'Di', 'Mi', 'Do', 'Fr', 'Sa'),
            'weekdays_long' => array ('Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'),
            'months_short'  => array ('Jan', 'Feb', 'M&#xe4;rz', 'April', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'),
            'months_long'   => array ('Januar', 'Februar', 'M&#xe4;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember')
        ),
        'fr'    => array (
            'weekdays_short'=> array ('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'),
            'weekdays_long' => array ('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
            'months_short'  => array ('Jan', 'F&#xe9;v', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Ao&#xfb;t', 'Sep', 'Oct', 'Nov', 'D&#xe9;c'),
            'months_long'   => array ('Janvier', 'F&#xe9;vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Ao&#xfb;t', 'Septembre', 'Octobre', 'Novembre', 'D&#xe9;cembre')
        ),
        'hu'    => array (
            'weekdays_short'=> array ('V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo'),
            'weekdays_long' => array ('vas&#xe1;rnap', 'h&#xe9;tf&#x151;', 'kedd', 'szerda', 'cs&#xfc;t&#xf6;rt&#xf6;k', 'p&#xe9;ntek', 'szombat'),
            'months_short'  => array ('jan', 'feb', 'm&#xe1;rc', '&#xe1;pr', 'm&#xe1;j', 'j&#xfa;n', 'j&#xfa;l', 'aug', 'szept', 'okt', 'nov', 'dec'),
            'months_long'   => array ('janu&#xe1;r', 'febru&#xe1;r', 'm&#xe1;rcius', '&#xe1;prilis', 'm&#xe1;jus', 'j&#xfa;nius', 'j&#xfa;lius', 'augusztus', 'szeptember', 'okt&#xf3;ber', 'november', 'december')
        ),
        'pl'    => array (
            'weekdays_short'=> array ('Nie', 'Pn', 'Wt', '&#x15a;r', 'Czw', 'Pt', 'Sob'),
            'weekdays_long' => array ('Niedziela', 'Poniedzia&#x142;ek', 'Wtorek', '&#x15a;roda', 'Czwartek', 'Pi&#x105;tek', 'Sobota'),
            'months_short'  => array ('Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Pa&#x17a;', 'Lis', 'Gru'),
            'months_long'   => array ('Stycze&#x144;', 'Luty', 'Marzec', 'Kwiecie&#x144;', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpie&#x144;', 'Wrzesie&#x144;', 'Pa&#x17a;dziernik', 'Listopad', 'Grudzie&#x144;')
        ),
        'sl'    => array (
            'weekdays_short'=> array ('Ned', 'Pon', 'Tor', 'Sre', 'Cet', 'Pet', 'Sob'),
            'weekdays_long' => array ('Nedelja', 'Ponedeljek', 'Torek', 'Sreda', 'Cetrtek', 'Petek', 'Sobota'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Avg', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januar', 'Februar', 'Marec', 'April', 'Maj', 'Junij', 'Julij', 'Avgust', 'September', 'Oktober', 'November', 'December')
        ),
        'ru'    => array (
            'weekdays_short'=> array ('&#x412;&#x441;', '&#x41f;&#x43d;', '&#x412;&#x442;', '&#x421;&#x440;', '&#x427;&#x442;', '&#x41f;&#x442;', '&#x421;&#x431;'),
            'weekdays_long' => array ('&#x412;&#x43e;&#x441;&#x43a;&#x440;&#x435;&#x441;&#x435;&#x43d;&#x44c;&#x435;', '&#x41f;&#x43e;&#x43d;&#x435;&#x434;&#x435;&#x43b;&#x44c;&#x43d;&#x438;&#x43a;', '&#x412;&#x442;&#x43e;&#x440;&#x43d;&#x438;&#x43a;', '&#x421;&#x440;&#x435;&#x434;&#x430;', '&#x427;&#x435;&#x442;&#x432;&#x435;&#x440;&#x433;', '&#x41f;&#x44f;&#x442;&#x43d;&#x438;&#x446;&#x430;', '&#x421;&#x443;&#x431;&#x431;&#x43e;&#x442;&#x430;'),
            'months_short'  => array ('&#x42f;&#x43d;&#x432;', '&#x424;&#x435;&#x432;', '&#x41c;&#x430;&#x440;', '&#x410;&#x43f;&#x440;', '&#x41c;&#x430;&#x439;', '&#x418;&#x44e;&#x43d;', '&#x418;&#x44e;&#x43b;', '&#x410;&#x432;&#x433;', '&#x421;&#x435;&#x43d;', '&#x41e;&#x43a;&#x442;', '&#x41d;&#x43e;&#x44f;', '&#x414;&#x435;&#x43a;'),
            'months_long'   => array ('&#x42f;&#x43d;&#x432;&#x430;&#x440;&#x44c;', '&#x424;&#x435;&#x432;&#x440;&#x430;&#x43b;&#x44c;', '&#x41c;&#x430;&#x440;&#x442;', '&#x410;&#x43f;&#x440;&#x435;&#x43b;&#x44c;', '&#x41c;&#x430;&#x439;', '&#x418;&#x44e;&#x43d;&#x44c;', '&#x418;&#x44e;&#x43b;&#x44c;', '&#x410;&#x432;&#x433;&#x443;&#x441;&#x442;', '&#x421;&#x435;&#x43d;&#x442;&#x44f;&#x431;&#x440;&#x44c;', '&#x41e;&#x43a;&#x442;&#x44f;&#x431;&#x440;&#x44c;', '&#x41d;&#x43e;&#x44f;&#x431;&#x440;&#x44c;', '&#x414;&#x435;&#x43a;&#x430;&#x431;&#x440;&#x44c;')
        ),
        'es'    => array (
            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mi&#xe9;', 'Jue', 'Vie', 'S&#xe1;b'),
            'weekdays_long' => array ('Domingo', 'Lunes', 'Martes', 'Mi&#xe9;rcoles', 'Jueves', 'Viernes', 'S&#xe1;bado'),
            'months_short'  => array ('Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'),
            'months_long'   => array ('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')
        ),
        'da'    => array (
            'weekdays_short'=> array ('S&#xf8;n', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L&#xf8;r'),
            'weekdays_long' => array ('S&#xf8;ndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'L&#xf8;rdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'December')
        ),
        'is'    => array (
            'weekdays_short'=> array ('Sun', 'M&#xe1;n', '&#xde;ri', 'Mi&#xf0;', 'Fim', 'F&#xf6;s', 'Lau'),
            'weekdays_long' => array ('Sunnudagur', 'M&#xe1;nudagur', '&#xde;ri&#xf0;judagur', 'Mi&#xf0;vikudagur', 'Fimmtudagur', 'F&#xf6;studagur', 'Laugardagur'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Ma&#xed;', 'J&#xfa;n', 'J&#xfa;l', '&#xc1;g&#xfa;', 'Sep', 'Okt', 'N&#xf3;v', 'Des'),
            'months_long'   => array ('Jan&#xfa;ar', 'Febr&#xfa;ar', 'Mars', 'Apr&#xed;l', 'Ma&#xed;', 'J&#xfa;n&#xed;', 'J&#xfa;l&#xed;', '&#xc1;g&#xfa;st', 'September', 'Okt&#xf3;ber', 'N&#xf3;vember', 'Desember')
        ),
        'it'    => array (
            'weekdays_short'=> array ('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'),
            'weekdays_long' => array ('Domenica', 'Luned&#xec;', 'Marted&#xec;', 'Mercoled&#xec;', 'Gioved&#xec;', 'Venerd&#xec;', 'Sabato'),
            'months_short'  => array ('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'),
            'months_long'   => array ('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre')
        ),
        'sk'    => array (
            'weekdays_short'=> array ('Ned', 'Pon', 'Uto', 'Str', '&#x8a;tv', 'Pia', 'Sob'),
            'weekdays_long' => array ('Nede&#x17e;a', 'Pondelok', 'Utorok', 'Streda', '&#x8a;tvrtok', 'Piatok', 'Sobota'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'M&#xe1;j', 'J&#xfa;n', 'J&#xfa;l', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Janu&#xe1;r', 'Febru&#xe1;r', 'Marec', 'Apr&#xed;l', 'M&#xe1;j', 'J&#xfa;n', 'J&#xfa;l', 'August', 'September', 'Okt&#xf3;ber', 'November', 'December')
        ),
        'cs'    => array (
            'weekdays_short'=> array ('Ne', 'Po', '&#xda;t', 'St', '&#x10c;t', 'P&#xe1;', 'So'),
            'weekdays_long' => array ('Ned&#x11b;le', 'Pond&#x11b;l&#xed;', '&#xda;ter&#xfd;', 'St&#x159;eda', '&#x10c;tvrtek', 'P&#xe1;tek', 'Sobota'),
            'months_short'  => array ('Led', '&#xda;no', 'B&#x159;e', 'Dub', 'Kv&#x11b;', '&#x10c;en', '&#x10c;ec', 'Srp', 'Z&#xe1;&#x159;', '&#x158;&#xed;j', 'Lis', 'Pro'),
            'months_long'   => array ('Leden', '&#xda;nor', 'B&#x159;ezen', 'Duben', 'Kv&#x11b;ten', '&#x10c;erven', '&#x10c;ervenec', 'Srpen', 'Z&#xe1;&#x159;&#xed;', '&#x158;&#xed;jen', 'Listopad', 'Prosinec')
        ),
        'hy'    => array (
            'weekdays_short'=> array ('&#x53f;&#x580;&#x56f;', '&#x535;&#x580;&#x56f;', '&#x535;&#x580;&#x584;', '&#x549;&#x580;&#x584;', '&#x540;&#x576;&#x563;', '&#x548;&#x582;&#x580;', '&#x547;&#x562;&#x569;'),
            'weekdays_long' => array ('&#x53f;&#x56b;&#x580;&#x561;&#x56f;&#x56b;', '&#x535;&#x580;&#x56f;&#x578;&#x582;&#x577;&#x561;&#x562;&#x569;&#x56b;', '&#x535;&#x580;&#x565;&#x584;&#x577;&#x561;&#x562;&#x569;&#x56b;', '&#x549;&#x578;&#x580;&#x565;&#x584;&#x577;&#x561;&#x562;&#x569;&#x56b;', '&#x540;&#x56b;&#x576;&#x563;&#x577;&#x561;&#x562;&#x569;&#x56b;', '&#x548;&#x582;&#x580;&#x562;&#x561;&#x569;', '&#x547;&#x561;&#x562;&#x561;&#x569;'),
            'months_short'  => array ('&#x540;&#x576;&#x57e;', '&#x553;&#x57f;&#x580;', '&#x544;&#x580;&#x57f;', '&#x531;&#x57a;&#x580;', '&#x544;&#x575;&#x57d;', '&#x540;&#x576;&#x57d;', '&#x540;&#x56c;&#x57d;', '&#x555;&#x563;&#x57d;', '&#x54d;&#x57a;&#x57f;', '&#x540;&#x56f;&#x57f;', '&#x546;&#x575;&#x574;', '&#x534;&#x56f;&#x57f;'),
            'months_long'   => array ('&#x540;&#x578;&#x582;&#x576;&#x57e;&#x561;&#x580;', '&#x553;&#x565;&#x57f;&#x580;&#x57e;&#x561;&#x580;', '&#x544;&#x561;&#x580;&#x57f;', '&#x531;&#x57a;&#x580;&#x56b;&#x56c;', '&#x544;&#x561;&#x575;&#x56b;&#x57d;', '&#x540;&#x578;&#x582;&#x576;&#x56b;&#x57d;', '&#x540;&#x578;&#x582;&#x56c;&#x56b;&#x57d;', '&#x555;&#x563;&#x578;&#x57d;&#x57f;&#x578;&#x57d;', '&#x54d;&#x565;&#x57a;&#x57f;&#x565;&#x574;&#x562;&#x565;&#x580;', '&#x540;&#x578;&#x56f;&#x57f;&#x565;&#x574;&#x562;&#x565;&#x580;', '&#x546;&#x578;&#x575;&#x565;&#x574;&#x562;&#x565;&#x580;', '&#x534;&#x565;&#x56f;&#x57f;&#x565;&#x574;&#x562;&#x565;&#x580;')
        ),
        'nl'    => array (
            'weekdays_short'=> array ('Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za'),
            'weekdays_long' => array ('Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December')
        ),
        'et'    => array (
            'weekdays_short'=> array ('P', 'E', 'T', 'K', 'N', 'R', 'L'),
            'weekdays_long' => array ('P&#xfc;hap&#xe4;ev', 'Esmasp&#xe4;ev', 'Teisip&#xe4;ev', 'Kolmap&#xe4;ev', 'Neljap&#xe4;ev', 'Reede', 'Laup&#xe4;ev'),
            'months_short'  => array ('Jaan', 'Veebr', 'M&#xe4;rts', 'Aprill', 'Mai', 'Juuni', 'Juuli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dets'),
            'months_long'   => array ('Jaanuar', 'Veebruar', 'M&#xe4;rts', 'Aprill', 'Mai', 'Juuni', 'Juuli', 'August', 'September', 'Oktoober', 'November', 'Detsember')
        ),
        'tr'    => array (
            'weekdays_short'=> array ('Paz', 'Pzt', 'Sal', '&#xc7;ar', 'Per', 'Cum', 'Cts'),
            'weekdays_long' => array ('Pazar', 'Pazartesi', 'Sal&#x131;', '&#xc7;ar&#x15f;amba', 'Per&#x15f;embe', 'Cuma', 'Cumartesi'),
            'months_short'  => array ('Ock', '&#x15e;bt', 'Mrt', 'Nsn', 'Mys', 'Hzrn', 'Tmmz', 'A&#x11f;st', 'Eyl', 'Ekm', 'Ksm', 'Arlk'),
            'months_long'   => array ('Ocak', '&#x15e;ubat', 'Mart', 'Nisan', 'May&#x131;s', 'Haziran', 'Temmuz', 'A&#x11f;ustos', 'Eyl&#xfc;l', 'Ekim', 'Kas&#x131;m', 'Aral&#x131;k')
        ),
        'no'    => array (
            'weekdays_short'=> array ('S&#xf8;n', 'Man', 'Tir', 'Ons', 'Tor', 'Fre', 'L&#xf8;r'),
            'weekdays_long' => array ('S&#xf8;ndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'L&#xf8;rdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'),
            'months_long'   => array ('Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember')
        ),
        'eo'    => array (
            'weekdays_short'=> array ('Dim', 'Lun', 'Mar', 'Mer', '&#x134;a&#x16D;', 'Ven', 'Sab'),
            'weekdays_long' => array ('Diman&#x109;o', 'Lundo', 'Mardo', 'Merkredo', '&#x134;a&#x16D;do', 'Vendredo', 'Sabato'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'A&#x16D;g', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januaro', 'Februaro', 'Marto', 'Aprilo', 'Majo', 'Junio', 'Julio', 'A&#x16D;gusto', 'Septembro', 'Oktobro', 'Novembro', 'Decembro')
        ),
        'ua'    => array (
            'weekdays_short'=> array ('&#x41d;&#x434;&#x43b;', '&#x41f;&#x43d;&#x434;', '&#x412;&#x442;&#x440;', '&#x421;&#x440;&#x434;', '&#x427;&#x442;&#x432;', '&#x41f;&#x442;&#x43d;', '&#x421;&#x431;&#x442;'),
            'weekdays_long' => array ('&#x41d;&#x435;&#x434;&#x456;&#x43b;&#x44f;', '&#x41f;&#x43e;&#x43d;&#x435;&#x434;&#x456;&#x43b;&#x43e;&#x43a;', '&#x412;&#x456;&#x432;&#x442;&#x43e;&#x440;&#x43e;&#x43a;', '&#x421;&#x435;&#x440;&#x435;&#x434;&#x430;', '&#x427;&#x435;&#x442;&#x432;&#x435;&#x440;', '&#x41f;\'&#x44f;&#x442;&#x43d;&#x438;&#x446;&#x44f;', '&#x421;&#x443;&#x431;&#x43e;&#x442;&#x430;'),
            'months_short'  => array ('&#x421;&#x456;&#x447;', '&#x41b;&#x44e;&#x442;', '&#x411;&#x435;&#x440;', '&#x41a;&#x432;&#x456;', '&#x422;&#x440;&#x430;', '&#x427;&#x435;&#x440;', '&#x41b;&#x438;&#x43f;', '&#x421;&#x435;&#x440;', '&#x412;&#x435;&#x440;', '&#x416;&#x43e;&#x432;', '&#x41b;&#x438;&#x441;', '&#x413;&#x440;&#x443;'),
            'months_long'   => array ('&#x421;&#x456;&#x447;&#x435;&#x43d;&#x44c;', '&#x41b;&#x44e;&#x442;&#x438;&#x439;', '&#x411;&#x435;&#x440;&#x435;&#x437;&#x435;&#x43d;&#x44c;', '&#x41a;&#x432;&#x456;&#x442;&#x435;&#x43d;&#x44c;', '&#x422;&#x440;&#x430;&#x432;&#x435;&#x43d;&#x44c;', '&#x427;&#x435;&#x440;&#x432;&#x435;&#x43d;&#x44c;', '&#x41b;&#x438;&#x43f;&#x435;&#x43d;&#x44c;', '&#x421;&#x435;&#x440;&#x43f;&#x435;&#x43d;&#x44c;', '&#x412;&#x435;&#x440;&#x435;&#x441;&#x435;&#x43d;&#x44c;', '&#x416;&#x43e;&#x432;&#x442;&#x435;&#x43d;&#x44c;', '&#x41b;&#x438;&#x441;&#x442;&#x43e;&#x43f;&#x430;&#x434;', '&#x413;&#x440;&#x443;&#x434;&#x435;&#x43d;&#x44c;')
        ),
        'ro'    => array (
            'weekdays_short'=> array ('Dum', 'Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sam'),
            'weekdays_long' => array ('Duminica', 'Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata'),
            'months_short'  => array ('Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'months_long'   => array ('Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie')
        ),
        'he'    => array (
            'weekdays_short'=> array ('&#1512;&#1488;&#1513;&#1493;&#1503;', '&#1513;&#1504;&#1497;', '&#1513;&#1500;&#1497;&#1513;&#1497;', '&#1512;&#1489;&#1497;&#1506;&#1497;', '&#1495;&#1502;&#1497;&#1513;&#1497;', '&#1513;&#1497;&#1513;&#1497;', '&#1513;&#1489;&#1514;'),
            'weekdays_long' => array ('&#1497;&#1493;&#1501; &#1512;&#1488;&#1513;&#1493;&#1503;', '&#1497;&#1493;&#1501; &#1513;&#1504;&#1497;', '&#1497;&#1493;&#1501; &#1513;&#1500;&#1497;&#1513;&#1497;', '&#1497;&#1493;&#1501; &#1512;&#1489;&#1497;&#1506;&#1497;', '&#1497;&#1493;&#1501; &#1495;&#1502;&#1497;&#1513;&#1497;', '&#1497;&#1493;&#1501; &#1513;&#1497;&#1513;&#1497;', '&#1513;&#1489;&#1514;'),
            'months_short'  => array ('&#1497;&#1504;&#1493;&#1488;&#1512;', '&#1508;&#1489;&#1512;&#1493;&#1488;&#1512;', '&#1502;&#1512;&#1509;', '&#1488;&#1508;&#1512;&#1497;&#1500;', '&#1502;&#1488;&#1497;', '&#1497;&#1493;&#1504;&#1497;', '&#1497;&#1493;&#1500;&#1497;', '&#1488;&#1493;&#1490;&#1493;&#1505;&#1496;', '&#1505;&#1508;&#1496;&#1502;&#1489;&#1512;', '&#1488;&#1493;&#1511;&#1496;&#1493;&#1489;&#1512;', '&#1504;&#1493;&#1489;&#1502;&#1489;&#1512;', '&#1491;&#1510;&#1502;&#1489;&#1512;'),
            'months_long'   => array ('&#1497;&#1504;&#1493;&#1488;&#1512;', '&#1508;&#1489;&#1512;&#1493;&#1488;&#1512;', '&#1502;&#1512;&#1509;', '&#1488;&#1508;&#1512;&#1497;&#1500;', '&#1502;&#1488;&#1497;', '&#1497;&#1493;&#1504;&#1497;', '&#1497;&#1493;&#1500;&#1497;', '&#1488;&#1493;&#1490;&#1493;&#1505;&#1496;', '&#1505;&#1508;&#1496;&#1502;&#1489;&#1512;', '&#1488;&#1493;&#1511;&#1496;&#1493;&#1489;&#1512;', '&#1504;&#1493;&#1489;&#1502;&#1489;&#1512;', '&#1491;&#1510;&#1502;&#1489;&#1512;')
        ),
        'sv'    => array (
            'weekdays_short'=> array ('S&#xf6;n', 'M&#xe5;n', 'Tis', 'Ons', 'Tor', 'Fre', 'L&#xf6;r'),
            'weekdays_long' => array ('S&#xf6;ndag', 'M&#xe5;ndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'L&#xf6;rdag'),
            'months_short'  => array ('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
            'months_long'   => array ('Januari', 'Februari', 'Mars', 'April', 'Maj', 'Juni', 'Juli', 'Augusti', 'September', 'Oktober', 'November', 'December')
        ),
        'pt'    => array (
            'weekdays_short'=> array ('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S&aacute;b'),
            'weekdays_long' => array ('Domingo', 'Segunda-feira', 'Ter&ccedil;a-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'S&aacute;bado'),
            'months_short'  => array ('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'),
            'months_long'   => array ('Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro')
        ),
        'tw'    => array (
            'weekdays_short'=> array ('&#36913;&#26085;','&#36913;&#19968;', '&#36913;&#20108;','&#36913;&#19977;', '&#36913;&#22235;','&#36913;&#20116;', '&#36913;&#20845;'),
            'weekdays_long' => array ('&#26143;&#26399;&#26085;', '&#26143;&#26399;&#19968;', '&#26143;&#26399;&#20108;', '&#26143;&#26399;&#19977;', '&#26143;&#26399;&#22235;', '&#26143;&#26399;&#20116;', '&#26143;&#26399;&#20845;'),
            'months_short'  => array ('&#19968;&#26376;', '&#20108;&#26376;', '&#19977;&#26376;', '&#22235;&#26376;', '&#20116;&#26376;', '&#20845;&#26376;', '&#19971;&#26376;', '&#20843;&#26376;', '&#20061;&#26376;', '&#21313;&#26376;', '&#21313;&#19968;&#26376;', '&#21313;&#20108;&#26376;'),
            'months_long'   => array ('&#19968;&#26376;', '&#20108;&#26376;', '&#19977;&#26376;', '&#22235;&#26376;', '&#20116;&#26376;', '&#20845;&#26376;', '&#19971;&#26376;', '&#20843;&#26376;', '&#20061;&#26376;', '&#21313;&#26376;', '&#21313;&#19968;&#26376;', '&#21313;&#20108;&#26376;')
        ),
        'pt-br' => array (
            'weekdays_short'=> array ('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'S&aacute;b'),
            'weekdays_long' => array ('Domingo', 'Segunda', 'Ter&ccedil;a', 'Quarta', 'Quinta', 'Sexta', 'S&aacute;bado'),
            'months_short'  => array ('Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'),
            'months_long'   => array ('Janeiro', 'Fevereiro', 'Mar&ccedil;o', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro')
        ),
        'sr'    => array (
            'weekdays_short'=> array ('&#1053;&#1077;&#1076;', '&#1055;&#1086;&#1085;', '&#1059;&#1090;&#1086;', '&#1057;&#1088;&#1077;', '&#1063;&#1077;&#1090;', '&#1055;&#1077;&#1090;', '&#1057;&#1091;&#1073;'),
            'weekdays_long' => array ('&#1053;&#1077;&#1076;&#1077;&#1113;&#1072;', '&#1055;&#1086;&#1085;&#1077;&#1076;&#1077;&#1113;&#1072;&#1082;', '&#1059;&#1090;&#1086;&#1088;&#1072;&#1082;', '&#1057;&#1088;&#1077;&#1076;&#1072;', '&#1063;&#1077;&#1090;&#1074;&#1088;&#1090;&#1072;&#1082;', '&#1055;&#1077;&#1090;&#1072;&#1082;', '&#1057;&#1091;&#1073;&#1086;&#1090;&#1072;'),
            'months_short'  => array ('&#1032;&#1072;&#1085;', '&#1060;&#1077;&#1073;', '&#1052;&#1072;&#1088;', '&#1040;&#1087;&#1088;', '&#1052;&#1072;&#1112;', '&#1032;&#1091;&#1085;', '&#1032;&#1091;&#1083;', '&#1040;&#1074;&#1075;', '&#1057;&#1077;&#1087;', '&#1054;&#1082;&#1090;', '&#1053;&#1086;&#1074;', '&#1044;&#1077;&#1094;'),
            'months_long'   => array ('&#1032;&#1072;&#1085;&#1091;&#1072;&#1088;', '&#1060;&#1077;&#1073;&#1088;&#1091;&#1072;&#1088;', '&#1052;&#1072;&#1088;&#1090;', '&#1040;&#1087;&#1088;&#1080;&#1083;', '&#1052;&#1072;&#1112;', '&#1032;&#1091;&#1085;', '&#1032;&#1091;&#1083;', '&#1040;&#1074;&#1075;&#1091;&#1089;&#1090;', '&#1057;&#1077;&#1087;&#1090;&#1077;&#1084;&#1073;&#1072;&#1088;', '&#1054;&#1082;&#1090;&#1086;&#1073;&#1072;&#1088;', '&#1053;&#1086;&#1074;&#1077;&#1084;&#1073;&#1072;&#1088;', '&#1044;&#1077;&#1094;&#1077;&#1084;&#1073;&#1072;&#1088;')
        ),
        'el' => array (
            'weekdays_short'=> array ('&#916;&#949;&#965;', '&#932;&#961;&#943;', '&#932;&#949;&#964;', '&#928;&#941;&#956;', '&#928;&#945;&#961;', '&#931;&#940;&#946;', '&#922;&#965;&#961;'),
            'weekdays_long' => array ('&#916;&#949;&#965;&#964;&#941;&#961;&#945;', '&#932;&#961;&#943;&#964;&#951;', '&#932;&#949;&#964;&#940;&#961;&#964;&#951;', '&#928;&#941;&#956;&#960;&#964;&#951;', '&#928;&#945;&#961;&#945;&#963;&#954;&#949;&#965;&#942;', '&#931;&#940;&#946;&#946;&#945;&#964;&#959;', '&#922;&#965;&#961;&#953;&#945;&#954;&#942;'),
            'months_short'  => array ('&#921;&#945;&#957;', '&#934;&#949;&#946;', '&#924;&#940;&#961;', '&#913;&#960;&#961;', '&#924;&#940;&#970;', 'Io&#973;&#957;', '&#921;&#959;&#973;&#955;', '&#913;&#973;&#947;', '&#931;&#949;&#960;', '&#927;&#954;&#964;', '&#925;&#959;&#941;', '&#916;&#949;&#954;'),
            'months_long'   => array ('&#921;&#945;&#957;&#959;&#965;&#940;&#961;&#953;&#959;&#962;', '&#934;&#949;&#946;&#961;&#959;&#965;&#940;&#961;&#953;&#959;&#962;', '&#924;&#940;&#961;&#964;&#953;&#959;&#962;', '&#913;&#960;&#961;&#943;&#955;&#953;&#959;&#962;', '&#924;&#940;&#970;&#959;&#962;', '&#921;&#959;&#973;&#957;&#953;&#959;&#962;', 'Io&#973;&#955;&#953;&#959;&#962;', '&#913;&#973;&#947;&#959;&#965;&#963;&#964;&#959;&#962;', '&#931;&#949;&#960;&#964;&#941;&#956;&#946;&#961;&#953;&#959;&#962;', '&#927;&#954;&#964;&#974;&#946;&#961;&#953;&#959;&#962;', '&#925;&#959;&#941;&#956;&#946;&#961;&#953;&#959;&#962;', '&#916;&#949;&#954;&#941;&#956;&#946;&#961;&#953;&#959;&#962;')
        )
    );
}
?>
