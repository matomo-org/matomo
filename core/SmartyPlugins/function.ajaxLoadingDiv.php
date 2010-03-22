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
 * Outputs the generic Ajax Loading div (displayed when ajax requests are triggered)
 * 
 * @param id=$ID_NAME ID of the HTML div, defaults to ajaxLoading
 * @return	string Html of the Loading... div
 */
function smarty_function_ajaxLoadingDiv($params, &$smarty)
{
	if(empty($params['id'])) 
	{
		$id = 'ajaxLoading';
	}
	else
	{
		$id = $params['id'];
	}
	return '<div id="'.$id.'" style="display:none">'.
				'<div id="loadingPiwik"><img src="themes/default/images/loading-blue.gif" alt="" /> '. 
					Piwik_Translate('General_LoadingData') .
				' </div>'.
			'</div>';
	;
}
