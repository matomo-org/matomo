<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LeftMenu;

/**
 * API for plugin LeftMenu
 *
 * @method static \Piwik\Plugins\LeftMenu\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns true if the left menu is enabled for the current user.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;

        $settings = new Settings('LeftMenu');

        $global = $settings->globalEnabled->getValue();
        $user   = $settings->userEnabled->getValue();

        if (empty($user) || 'no' === $user) {
            return false;
        }

        if ($user === 'default' && !$global) {
            return false;
        }

        return true;
    }
}
