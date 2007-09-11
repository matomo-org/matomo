<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {mailto} function plugin
 *
 * Examples:
 * <pre>
 * {mailto address="me@domain.com"}
 * {mailto address="me@domain.com" encode="javascript"}
 * {mailto address="me@domain.com" encode="hex"}
 * {mailto address="me@domain.com" subject="Hello to you!"}
 * {mailto address="me@domain.com" cc="you@domain.com,they@domain.com"}
 * {mailto address="me@domain.com" extra='class="mailto"'}
 * </pre>
 * @link http://smarty.php.net/manual/en/language.function.mailto.php {mailto}
 *          (Smarty online manual)
 * @version  1.2
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @author   credits to Jason Sweat (added cc, bcc and subject functionality)
 * @param    array
 * @param    Smarty
 * @return   string
 */
function smarty_function_url($params, &$smarty)
{
	$queryString = Piwik_Url::getCurrentQueryString();
	$queryString = htmlspecialchars($queryString);
	$urlValues = Piwik_Common::getArrayFromQueryString($queryString);
//	var_dump($urlValues);
	foreach($params as $key => $value)
	{
		$urlValues[$key] = $value;
	}
	
	return '?' . http_build_query($urlValues);
}

/* vim: set expandtab: */

?>
