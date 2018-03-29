<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Intl;

/**
 * Internationalized Domain Name and Puny Code
 */
class Idn
{
    const BASE = 36;
    const TMIN = 1;
    const TMAX = 26;
    const SKEW = 38;
    const DAMP = 700;
    const INITIAL_BIAS = 72;
    const INITIAL_N = 0x80;

    /**
     * Convert IDN ASCII format (Punycode) domain name to UTF-8.
     * In case of a decoding failure the original string is returned.
     *
     * @param string $domain
     *
     * @return string
     */
    public static function decodeIdn($domain)
    {
        // use the idn/intl pecl extension if available
        if (function_exists('idn_to_utf8')) {
            return idn_to_utf8($domain, IDNA_DEFAULT,  INTL_IDNA_VARIANT_UTS46) ?: $domain;
        }

        if ( ! ($length = strlen($domain)) ||
            $length !== strspn($domain, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-')
        ) {
            return $domain;
        }

        return implode('.', array_map(function ($s) {
            if (substr($s, 0, 4) !== 'xn--') {
                return $s;
            }

            return self::punycodeDecode($substr($s, 4)) ?: $s;
        }, explode('.', $domain))) ?: $domain;
    }

    /**
     * PHP port of GNU libidn2 puny_decode.c
     *
     * @param string $input
     *
     * @return string|false
     *
     * @see     https://github.com/libidn/libidn2
     * @license https://www.gnu.org/licenses/lgpl.html LGPL v3 or later
     */
    private static function punycodeDecode($input)
    {
        $inputLength = strlen($input);
        $n = self::INITIAL_N;
        $out = $i = 0;
        $bias = self::INITIAL_BIAS;

        /* Handle the basic code points:  Let b be the number of input code */
        /* points before the last delimiter, or 0 if there is none, then    */
        /* copy the first b code points to the output.                      */

        $b = strrpos($input, '-');
        $output = [];

        for ($j = 0; $j < $b; ++$j) {
            $output[] = ord($input[$j]);
        }

        $out += $b;

        /* Main decoding loop:  Start just after the last delimiter if any  */
        /* basic code points were copied; start at the beginning otherwise. */
        for ($in = $b > 0 ? $b + 1 : 0; $in < $inputLength; ++$out) {

            /* in is the index of the next ASCII code point to be consumed, */
            /* and out is the number of code points in the output array.    */

            /* Decode a generalized variable-length integer into delta,  */
            /* which gets added to i.  The overflow checking is easier   */
            /* if we increase i as we go, then subtract off its starting */
            /* value at the end to obtain delta.                         */

            for ($oldi = $i, $w = 1, $k = self::BASE; ; $k += self::BASE) {
                if ($in >= $inputLength)  return false;

                $digit = self::decodeDigit(ord($input[$in++]));

                if ($digit >= self::BASE)  return false; // bad input
                if ($digit > (PHP_INT_MAX - $i) / $w)  return false; // overflow

                $i += $digit * $w;
                $t = $k <= $bias /* + self::TMIN */ ? self::TMIN :     /* +tmin not needed */
                     ($k >= $bias + self::TMAX ? self::TMAX : $k - $bias);

                if ($digit < $t)  break;
                if ($w > PHP_INT_MAX / (self::BASE - $t))  return false; // overflow

                $w *= (self::BASE - $t);
            }

            $bias = self::adapt($i - $oldi, $out + 1, $oldi === 0);

            /* i was supposed to wrap around from out+1 to 0,   */
            /* incrementing n each time, so we'll fix that now: */

            if ($i / ($out + 1) > PHP_INT_MAX - $n)  return false; // overflow

            $n += (int) ($i / ($out + 1));
            $i %= ($out + 1);

            /* Insert n at position i of the output: */
            array_splice($output, $i++, 0, [$n]);
        }

        // @see https://tools.ietf.org/html/rfc2044
        $return = '';

        foreach ($output as $k => $v) {
            if ($v <= 0x1f || $v === 0x2e) {
                return false;
            }

            if ($v <= 0x7f) {
                $return .= chr($v);
            } elseif ($v <= 0x7ff) {
                $return .= chr(192 + ($v >> 6))
                         . chr(128 + ($v & 63));
            } elseif ($v <= 0xffff) {
                $return .= chr(224 + ($v >> 12))
                         . chr(128 + (($v >> 6) & 63))
                         . chr(128 + ($v & 63));
            } elseif ($v <= 0x1fffff) {
                $return .= chr(240 + ($v >> 18))
                         . chr(128 + (($v >> 12) & 63))
                         . chr(128 + (($v >> 6) & 63))
                         . chr(128 + ($v & 63));
            } elseif ($v <= 0x3ffffff) {
                $return .= chr(248 + ($v >> 24))
                         . chr(128 + (($v >> 18) & 63))
                         . chr(128 + (($v >> 12) & 63))
                         . chr(128 + (($v >> 6) & 63))
                         . chr(128 + ($v & 63));
            } elseif ($v <= 0x7fffffff) {
                $return .= chr(252 + ($v >> 30))
                         . chr(128 + (($v >> 24) & 63))
                         . chr(128 + (($v >> 18) & 63))
                         . chr(128 + (($v >> 12) & 63))
                         . chr(128 + (($v >> 6) & 63))
                         . chr(128 + ($v & 63));
            } else {
                return false;
            }
        }

        return $return;
    }

    private static function decodeDigit($cp)
    {
        return $cp - 48 < 10 ? $cp - 22 : ($cp - 65 < 26 ? $cp - 65 : ($cp - 97 < 26 ? $cp - 97 : self::BASE));
    }

    private static function adapt($delta, $numpoints, $firsttime)
    {
        $delta = $firsttime ? (int) ($delta / self::DAMP) : $delta >> 1;
        $delta += (int) ($delta / $numpoints);

        for ($k = 0; $delta > ((self::BASE - self::TMIN) * self::TMAX) / 2; $k += self::BASE) {
            $delta = (int) ($delta / (self::BASE - self::TMIN));
        }

        return $k + (int) ((self::BASE - self::TMIN + 1) * $delta / ($delta + self::SKEW));
    }
}
