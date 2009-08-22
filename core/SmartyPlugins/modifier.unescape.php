<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
 * @author   Anthon Pang <anthon.pang at gmail dot com>
 * @param string
 * @return string
 */
function smarty_modifier_unescape($string, $char_set = 'UTF-8')
{
	return html_entity_decode($string, ENT_QUOTES, $char_set);
}

/* vim: set expandtab: */
