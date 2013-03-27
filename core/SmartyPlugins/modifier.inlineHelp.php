<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Displays inline help using the jquery UI CSS
 * @param string $text
 * @return string
 */
function smarty_modifier_inlineHelp($text)
{
    return
        '<div class="ui-widget">' .
        '<div class="ui-inline-help ui-state-highlight ui-corner-all">' .
        '<span class="ui-icon ui-icon-info" style="float:left;margin-right:.3em;"></span>' .
        $text .
        '</div>' .
        '</div>';
}
