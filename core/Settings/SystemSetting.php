<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Settings;

use Piwik\Piwik;

/**
 * Describes a system wide setting. Only the super user can change this type of setting and
 * the value of this setting will affect all users.
 *
 * @package Piwik
 * @subpackage Settings
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

        $this->displayedForCurrentUser = Piwik::isUserIsSuperUser();
    }

    /**
     * Returns the display order. User settings are displayed after system settings.
     * 
     * @return int
     */
    public function getOrder()
    {
        return 30;
    }
}