<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Proxy
 */
namespace Piwik\Plugins\Proxy;

use Piwik\Version;

/**
 * Proxy services for the UI
 *
 * @package Proxy
 */
class Proxy extends \Piwik\Plugin
{
    /**
     * Return information about this plugin.
     *
     * @see Piwik_Plugin
     *
     * @return array
     */
    public function getInformation()
    {
        return array(
            'description'          => 'Proxy services',
            'author'               => 'Piwik',
            'author_homepage'      => 'http://piwik.org/',
            'version'              => Version::VERSION,
            'translationAvailable' => false,
        );
    }
}
