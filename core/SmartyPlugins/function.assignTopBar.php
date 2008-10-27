<?php
/**
 * Enter description here...
 *
 * @param array $params
 * @param Smarty $smarty
 */
function smarty_function_assignTopBar($params, &$smarty)
{
	$topBarElements = array(
		array('CoreHome', 'Your Dashboard', array('module' => 'CoreHome', 'action' => 'index')),
		array('Widgetize', 'Widgets',  array('module' => 'Widgetize', 'action' => 'index')), 
		array('API', 'API', array('module' => 'API', 'action' => 'listAllAPI')),
		array('Feedback', 'Give us Feedback!', array('module' => 'Feedback', 'action' => 'index', 'keepThis' => 'true', 'TB_iframe' => 'true', 'height' => '400', 'width' => '350'), 'title="Give us Feedback!" class="thickbox"'),
	);
	$smarty->assign("topBarElements", $topBarElements);
}
