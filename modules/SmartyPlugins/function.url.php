<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Visualization
 */

require_once "Url.php";

/**
 * Smarty {url} function plugin
 *
 * Examples:
 * <pre>
 * {url module="API"} will rewrite the URL modifying the module GET parameter
 * {url module="API" method="getKeywords"} will rewrite the URL modifying the parameters module=API method=getKeywords
 * </pre>
 * 
 * @see Piwik_Url::getCurrentQueryStringWithParametersModified()
 * @param	array
 * @param	Smarty
 * @return	string
 */
function smarty_function_url($params, &$smarty)
{
	return Piwik_Url::getCurrentQueryStringWithParametersModified( $params );
}
