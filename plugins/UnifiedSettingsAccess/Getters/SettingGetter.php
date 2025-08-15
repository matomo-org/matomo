<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UnifiedSettingsAccess\Getters;

use Piwik\Piwik;

abstract class SettingGetter
{
    /**
     * @var string
     */
    protected $pluginName;

    /**
     * @var string
     */
    protected $settingName;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $idSite;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var mixed
     */
    protected $myValue;

    public function __construct(string $pluginName, string $settingName, string $type, $defaultValue = null, int $idSite = null)
    {
        $this->pluginName = $pluginName;
        $this->settingName = $settingName;
        $this->type = $type;
        $this->idSite = $idSite;
        $this->defaultValue = $defaultValue;
    }

    abstract public function getSetting();

    protected function convertValue(): void
    {
        settype($this->myValue, $this->type);
    }

    protected function fallbackDefaultValue($notSetValue = null): void
    {
        if ($notSetValue === $this->myValue) {
            $this->myValue = $this->defaultValue;
        }
    }

    protected function postUpdateEvent(): void
    {
        Piwik::postEvent('UnifiedSettingsAccess.updateValue', [
            $this->pluginName,
            $this->settingName,
            &$this->myValue
        ]);
    }
}
