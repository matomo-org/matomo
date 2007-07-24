<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty count_words modifier plugin
 *
 * Type:     modifier<br>
 * Name:     count_words<br>
 * Purpose:  count the number of words in a text
 * @link http://smarty.php.net/manual/en/language.modifier.count.words.php
 *          count_words (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return integer
 */
function smarty_modifier_count_words($string)
{
    // split text by ' ',\r,\n,\f,\t
    $split_array = preg_split('/\s+/',$string);
    // count matches that contain alphanumerics
    $word_count = preg_grep('/[a-zA-Z0-9\\x80-\\xff]/', $split_array);

    return count($word_count);
}

/* vim: set expandtab: */

?>
