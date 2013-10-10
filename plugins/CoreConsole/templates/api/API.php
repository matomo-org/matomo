<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_PLUGINNAME
 */
namespace Piwik\Plugins\PLUGINNAME;

/**
 * API for plugin PLUGINNAME
 *
 * @package Piwik_PLUGINNAME
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Example method. Please remove if you do not need this API method.
     * You can call this API method like this:
     * /index.php?module=API&method=PLUGINNAME.getAnswerToLife
     * /index.php?module=API&method=PLUGINNAME.getAnswerToLife?truth=0
     *
     * @param  bool $truth
     *
     * @return bool
     */
    public function getAnswerToLife($truth = true)
    {
        if ($truth) {

            return 42;
        }

        return 24;
    }
}