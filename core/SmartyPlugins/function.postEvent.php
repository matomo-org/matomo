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
 * Posts an event from a smarty template. This event can then be hooked by another plugin.
 * The event will be posted along with a string value that plugins can edit.
 * This is useful to allow other plugins to add content at a specific entry point in the template.
 * This string will be returned by the smarty function.
 *
 * Examples:
 * <pre>
 *         {postEvent name="template_footerUserCountry"}
 * </pre>
 *
 * Plugins can then hook on this event by using the Piwik_AddAction function:
 *     Piwik_AddAction('template_footerUserCountry', 'functionToHookOnThisEvent');
 *
 * @param $params array([name] => The name of the event)
 * @param $smarty
 * @throws Exception
 * @return string The string eventually modified by the plugins listening to this event
 */
function smarty_function_postEvent($params, &$smarty)
{
    if (!isset($params['name'])) {
        throw new Exception("The smarty function postEvent needs a 'name' parameter.");
    }
    $eventName = $params['name'];

    $str = '';
    Piwik_PostEvent($eventName, $str);
    return $str;
}
