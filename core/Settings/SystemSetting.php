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
 * System wide setting. Only the super user can change this kind of settings and the value of the setting effects all
 * users.
 *
 * @package Piwik
 * @subpackage Settings
 *
 * @api
 */
class SystemSetting extends Setting
{
    public function __construct($name, $title)
    {
        parent::__construct($name, $title);

        $this->displayedForCurrentUser = Piwik::isUserIsSuperUser();
    }

    public function getOrder()
    {
        return 30;
    }
}
