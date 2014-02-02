<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings;

use Piwik\Piwik;

/**
 * Describes a system wide setting. Only the Super User can change this type of setting and
 * the value of this setting will affect all users.
 * 
 * See {@link \Piwik\Plugin\Settings}.
 *
 *
 * @api
 */
class SystemSetting extends Setting
{
    /**
     * Constructor.
     * 
     * @param string $name The persisted name of the setting.
     * @param string $title The display name of the setting.
     */
    public function __construct($name, $title)
    {
        parent::__construct($name, $title);

        $this->displayedForCurrentUser = Piwik::hasUserSuperUserAccess();
    }

    /**
     * Returns the display order. System settings are displayed before user settings.
     * 
     * @return int
     */
    public function getOrder()
    {
        return 30;
    }
}
