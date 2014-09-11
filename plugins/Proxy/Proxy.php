<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Proxy;

use Piwik\Version;

/**
 * Proxy services for the UI
 *
 */
class Proxy extends \Piwik\Plugin
{
    /**
     * Return information about this plugin.
     *
     * @see Piwik\Plugin
     *
     * @return array
     */
    public function getInformation()
    {
        return array(
            'description'          => 'Proxy services',
            'authors'              => array(array('name' => 'Piwik', 'homepage' => 'http://piwik.org/')),
            'version'              => Version::VERSION,
            'translationAvailable' => false,
        );
    }
}
