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
    private $wasSet = false;

    public function getOldValue()
    {
        return $this->oldValue;
    }

    /**
     * An instance that was never assigned to reports no change, so that saving unrelated settings
     * does not read as this one being turned on and trigger an IP range sync.
     */
    public function hasValueChanged(): bool
    {
        return $this->wasSet && ((bool) $this->getValue() !== (bool) $this->oldValue);
    }

    public function setValue($value)
    {
        $this->oldValue = $this->getValue();
        $this->wasSet = true;

        parent::setValue($value);
    }
}
