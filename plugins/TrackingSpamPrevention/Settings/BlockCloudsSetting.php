<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TrackingSpamPrevention\Settings;

use Piwik\Settings\Plugin\SystemSetting;

class BlockCloudsSetting extends SystemSetting
{
    private $oldValue;

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function setValue($value)
    {
        $this->oldValue = $this->getValue();

        parent::setValue($value);
    }
}
