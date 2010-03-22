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
 * Displays inline help using the jquery UI CSS
 */
function smarty_modifier_inlineHelp($text)
{
	return 
		'<div class="ui-widget">'.
			'<div class="ui-state-highlight ui-corner-all" style="margin-top:20px; padding:0 .7em;">'.
				'<p style="font-size:8pt;"><span class="ui-icon ui-icon-info" style="float:left;margin-right:.3em;"></span>'.
					$text.
				'</p>'.
			'</div>'.
		'</div>';
}
