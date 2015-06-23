<?php
/**
* Piwik - free/libre analytics platform
*
* @link http://piwik.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/

namespace Piwik\Plugins\MobileAppMeasurable\tests\Framework\Mock;

use Piwik\Measurable\MeasurableSetting;
use Piwik\Measurable\MeasurableSettings;
use Piwik\Tracker;

class Type extends \Piwik\Plugins\MobileAppMeasurable\Type
{

    public function configureMeasurableSettings(MeasurableSettings $settings)
    {
        $appId = new MeasurableSetting('app_id', 'App-ID');
        $appId->validate = function ($value) {
            if (strlen($value) > 100) {
                throw new \Exception('Only 100 characters are allowed');
            }
        };

        $settings->addSetting($appId);
    }
}
