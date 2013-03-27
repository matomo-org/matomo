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
 * Smarty - HTML Purifier block plugin
 *
 * File:     block.purify.php<br>
 * Type:     block<br>
 * Name:     purify<br>
 * Date:     Oct 20, 2010<br>
 * Purpose:  Purify template output.<br>
 * Install:  Drop into the plugin directory, call
 *           <code>{purify}HTML fragment{/purify}</code>
 *           from template.
 *
 * @param array $params
 * <pre>
 * Params:   assign: string (null)
 * </pre>
 * @param string $content
 * @param Smarty
 * @return string purified content
 */
function smarty_block_purify($params, $content, &$smarty)
{
    if (is_null($content)) {
        return;
    }

    $assign = null;

    foreach ($params as $_key => $_val) {
        switch ($_key) {
            case 'assign':
                $$_key = (string)$_val;
                break;

            default:
                $smarty->trigger_error("purify: unknown attribute '$_key'");
        }
    }

    $purifier = Piwik_HTMLPurifier::getInstance();
    $output = $purifier->purify($content);

    return $assign ? $smarty->assign($assign, $output) : $output;
}
