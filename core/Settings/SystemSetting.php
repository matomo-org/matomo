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
 * System Setting.
 *
 * @package Piwik
 * @subpackage Settings
 */
class SystemSetting extends Setting
{
    public function __construct($name, $title)
    {
        parent::__construct($name, $title);

        $this->displayedForCurrentUser = !Piwik::isUserIsAnonymous();
    }

}
