<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: modifier.sumtime.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_Visualization
 */

/**
 */
function smarty_modifier_urlRewriteWithParameters($parameters)
{
	return Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);
}

