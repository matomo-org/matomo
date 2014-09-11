<?php
/**
 * Piwik - free/libre analytics platform
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
        $settings = new Settings('LeftMenu');
        $default  = $settings->globalEnabled->getValue();

        if (!$settings->userEnabled->isReadableByCurrentUser()) {
            return $default;
        }

        $user = $settings->userEnabled->getValue();

        if (empty($user) || $user === 'system') {
            return $default;
        }

        if ($user === 'no') {
            return false;
        }

        return true;
    }
}
