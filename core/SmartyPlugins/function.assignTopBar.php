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
 * Smarty {assignTopBar} function plugin.
 * Initialize top nav bar text and links.
 *
 * @param array $params
 * @param Smarty $smarty
 */
function smarty_function_assignTopBar($params, &$smarty)
{
	$topBarElements = array(
		array('CoreHome', Piwik_Translate('General_YourDashboard'), array('module' => 'CoreHome', 'action' => 'index')),
		array('Widgetize', Piwik_Translate('General_Widgets'),  array('module' => 'Widgetize', 'action' => 'index')), 
		array('API', Piwik_Translate('General_API'), array('module' => 'API', 'action' => 'listAllAPI')),
		array('Feedback', Piwik_Translate('General_GiveUsYourFeedback'), array('module' => 'Feedback', 'action' => 'index', 'keepThis' => 'true', 'TB_iframe' => 'true', 'height' => '400', 'width' => '350'), 'title="'.Piwik_Translate('General_GiveUsYourFeedback').'" class="thickbox"'),
	);
	$smarty->assign("topBarElements", $topBarElements);
}
