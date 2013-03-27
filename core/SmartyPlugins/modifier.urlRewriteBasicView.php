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
 * Rewrites the given URL so that it looks like a URL that can be loaded directly.
 * Useful for users who don't handle javascript / ajax, they can still use piwik with these rewritten URLs.
 *
 * @param array $parameters
 * @return string
 */
function smarty_modifier_urlRewriteBasicView($parameters)
{
    // replace module=X by moduleToLoad=X
    // replace action=Y by actionToLoad=Y
    $parameters['moduleToLoad'] = $parameters['module'];
    unset($parameters['module']);

    if (isset($parameters['action'])) {
        $parameters['actionToLoad'] = $parameters['action'];
        unset($parameters['action']);
    } else {
        $parameters['actionToLoad'] = null;
    }
    $url = Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);

    // add module=CoreHome&action=showInContext
    $url = $url . '&amp;module=CoreHome&amp;action=showInContext';
    return Piwik_Common::sanitizeInputValue($url);
}
