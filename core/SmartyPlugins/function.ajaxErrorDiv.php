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
 * Outputs the generic Ajax error div (displayed when ajax requests are throwing exceptions and returning error messages)
 *
 * @param array $params array([id] => ID of the HTML div, defaults to ajaxError)
 * @param $smarty
 * @return string Html of the error message div, hidden by default
 */
function smarty_function_ajaxErrorDiv($params, &$smarty)
{
    if (empty($params['id'])) {
        $id = 'ajaxError';
    } else {
        $id = $params['id'];
    }
    return '<div class="ajaxError" id="' . $id . '" style="display:none"></div>';
}
