<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable;

use Piwik\Settings\Setting;

class MeasurableSettings extends \Piwik\Plugins\WebsiteMeasurable\MeasurableSettings
{
    /** @var Setting */
    public $trustvisitorcookies;

    protected function shouldShowSettingsForType($type)
    {
        return $type === Type::ID;
    }

}
