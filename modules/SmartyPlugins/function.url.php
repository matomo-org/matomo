<?php
/**
 * Smarty plugin
 * @package Smarty
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
