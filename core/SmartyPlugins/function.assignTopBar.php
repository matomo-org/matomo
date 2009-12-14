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
	$topBarElements = array();
	$elements = array(
		array('CoreHome', Piwik_Translate('General_Dashboard'), array('module' => 'CoreHome', 'action' => 'index')),
		array('MultiSites', Piwik_Translate('General_MultiSitesSummary'), array('module' => 'MultiSites', 'action' => 'index')),
		array('Widgetize', Piwik_Translate('General_Widgets'),  array('module' => 'Widgetize', 'action' => 'index')), 
		array('API', Piwik_Translate('General_API'), array('module' => 'API', 'action' => 'listAllAPI')),
		array('Feedback', Piwik_Translate('General_GiveUsYourFeedback'), array('module' => 'Feedback', 'action' => 'index'), 'id="topbar-feedback"'),
	);

	foreach($elements as $element)
	{
		if(Piwik_PluginsManager::getInstance()->isPluginActivated($element[0]))
		{
			$topBarElements[] = $element;
		}
	}

	$smarty->assign("topBarElements", $topBarElements);
}
