<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Smarty unescape modifier plugin
 *
 * Type:     modifier<br>
 * Name:     unescape<br>
 * Purpose:  Unescape the string
 * @param string $string
 * @param string $char_set
 * @return string
 */
function smarty_modifier_unescape($string, $char_set = 'UTF-8')
{
    return html_entity_decode($string, ENT_QUOTES, $char_set);
}

/* vim: set expandtab: */
