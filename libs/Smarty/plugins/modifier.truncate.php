<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (function_exists('mb_strlen') && function_exists('mb_substr'))
    {
        if (mb_strlen($string, 'UTF-8') > $length) {
            $length -= min($length, mb_strlen($etc, 'UTF-8'));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, 'UTF-8'));
            }
            if(!$middle) {
                return mb_substr($string, 0, $length, 'UTF-8') . $etc;
            }
            return mb_substr($string, 0, $length/2, 'UTF-8') . $etc . mb_substr($string, -$length/2, $length/2, 'UTF-8');
        }
        return $string;
    }

    if (strlen($string) > $length) {
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        }
        if(!$middle) {
            return substr($string, 0, $length) . $etc;
        }
        return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
    }
    return $string;
}

/* vim: set expandtab: */

?>
