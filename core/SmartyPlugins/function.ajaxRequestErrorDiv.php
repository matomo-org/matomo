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
 * Outputs the generic Ajax request error div
 * will be displayed when the ajax request fails (connectivity, server error, etc)
 *
 * @return    string Html of the div
 */
function smarty_function_ajaxRequestErrorDiv()
{
    return '<div id="loadingError">' . Piwik_Translate('General_ErrorRequest') . '</div>';
}
