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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Math.php 4104 2007-03-18 17:57:13Z thomas $
 */


/**
 * Utility class for proxying math function to bcmath functions, if present,
 * otherwise to PHP builtin math operators, with limited detection of overflow conditions.
 * Sampling of PHP environments and platforms suggests that at least 80% to 90% support bcmath.
 * Thus, this file should be as light as possible.
 *
 * @category   Zend
 * @package    Zend_Locale
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Locale_Math
{
    // support unit testing without using bcmath functions 
    public static $_bcmathDisabled = false;

    public static $add   = 'bcadd';
    public static $sub   = 'bcsub';
    public static $pow   = 'bcpow';
    public static $mul   = 'bcmul';
    public static $div   = 'bcdiv';
    public static $comp  = 'bccomp';
    public static $sqrt  = 'bcsqrt';
    public static $mod   = 'bcmod';
    public static $scale = 'bcscale';

    public static function isBcmathDisabled()
    {
        return self::$_bcmathDisabled;
    }

    /**
     * Surprisingly, the results of this implementation of round()
     * prove better than the native PHP round(). For example, try:
     *   round(639.795, 2);
     *   round(267.835, 2);
     *   round(0.302515, 5);
     *   round(0.36665, 4);
     * then try:
     *   Zend_Locale_Math::round('639.795', 2);
     */
    public static function round($op1, $precision = 0)
    {
        if (self::$_bcmathDisabled) {
            return (string) round($op1, $precision);
        }
        $op1 = trim($op1);
        $length = strlen($op1);
        if (($decPos = strpos($op1, '.')) === false) {
            $op1 .= '.0';
            $decPos = $length;
            $length += 2;
        }
        if ($precision < 0 && abs($precision) > $decPos) {
            return '0';
        }
        $digitsBeforeDot = $length - ($decPos + 1);
        if ($precision >= ($length - ($decPos + 1))) {
            return $op1;
        }
        if ($precision === 0) {
            $triggerPos = 1;
            $roundPos   = -1;
        } elseif ($precision > 0) {
            $triggerPos = $precision + 1;
            $roundPos   = $precision;
        } else {
            $triggerPos = $precision;
            $roundPos   = $precision -1;
        }
        $triggerDigit = $op1[$triggerPos + $decPos];
        if ($precision < 0) {
            // zero fill digits to the left of the decimal place
            $op1 = substr($op1, 0, $decPos + $precision) . str_pad('', abs($precision), '0');
        }
        if ($triggerDigit >= '5') {
            if ($roundPos + $decPos == -1) {
                return str_pad('1', $decPos + 1, '0');
            }
            $roundUp = str_pad('', $length, '0');
            $roundUp[$decPos] = '.';
            $roundUp[$roundPos + $decPos] = '1';
            return bcadd($op1, $roundUp, $precision);
        } elseif ($precision >= 0) {
            return substr($op1, 0, $decPos + ($precision ? $precision + 1: 0));
        }
        return (string) $op1;
    }

    /**
     * Normalizes an input to standard english notation
     * Fixes a problem of BCMath with setLocale which is PHP related
     * 
     * @param   integer  $value  Value to normalize
     * @return  string           Normalized string without BCMath problems
     */
    public static function normalize($value)
    {
        $value = (string) $value;
        $convert = localeconv();
        $value = str_replace($convert['thousands_sep'], "",$value);
        $value = str_replace($convert['positive_sign'], "",$value);
        if (!empty($convert['negative_sign']) and (strpos($value, $convert['negative_sign']))) {
            $value = str_replace($convert['negative_sign'], "",$value);
            $value = "-".$value;
        }
        return $value;
    }
}

if ((defined('TESTS_ZEND_LOCALE_BCMATH_ENABLED') && !TESTS_ZEND_LOCALE_BCMATH_ENABLED)
    || !extension_loaded('bcmath')) {
    require_once 'Zend/Locale/Math/PhpMath.php';
}

?>
