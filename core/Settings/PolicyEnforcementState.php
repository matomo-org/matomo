<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Storage\Factory;
use Piwik\Settings\Storage\Storage;

/**
 * Stored enforcement state of a single policy-controlled setting.
 *
 * A policy-controlled setting has two separate storage areas:
 *
 *  1. The value of the underlying Matomo setting, written from whichever settings
 *     page owns it and read through the setting's own getter trait. Compliance
 *     never writes to it, and only reads it to judge whether the underlying
 *     configuration already satisfies a policy requirement on its own.
 *  2. The enforcement state handled by this class, which records whether the
 *     setting is being forced on, in the same way a compliance policy as a whole
 *     can be turned on or off.
 *
 * The state is deliberately independent of any compliance policy: a setting has one
 * enforcement state per scope, not one per policy it subscribes to. Storing it under
 * the setting's own plugin means it is reachable from the setting, is removed along
 * with the plugin's other settings when the plugin is uninstalled, and can be pinned
 * from `config.ini.php` like any other system setting.
 *
 * Three states are distinguished:
 *
 *  - `true` / `false` — the state was set explicitly for this scope
 *  - `null` — no state was ever stored, so the setting follows the enforcement
 *    state of the policies it subscribes to
 */
class PolicyEnforcementState
{
    /**
     * Suffix of the setting name the enforcement state is stored under. It intentionally
     * carries no policy name: the state belongs to the setting, not to a policy.
     */
    public const SETTING_NAME_SUFFIX = '_policy_enforced';

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $settingName;

    public function __construct(string $pluginName, string $settingName)
    {
        $this->pluginName = $pluginName;
        $this->settingName = $settingName;
    }

    /**
     * Returns the stored enforcement state for the given scope, or null when no state
     * was ever stored and the setting therefore follows its policies.
     *
     * @param int|null $idSite The website to read the state of, or null for the instance wide state.
     */
    public function get(?int $idSite = null): ?bool
    {
        $value = $this->makeSetting($idSite)->getValue();

        return is_null($value) ? null : (bool) $value;
    }

    /**
     * Stores the enforcement state for the given scope.
     *
     * @param int|null $idSite The website to write the state of, or null for the instance wide state.
     * @param bool|null $isEnforced Null removes the stored state, making the setting follow its policies again.
     * @throws Exception when the state cannot be written by the current user
     */
    public function set(?int $idSite, ?bool $isEnforced): void
    {
        $this->assertWritableByCurrentUser($idSite);

        // Setting::setValue() cannot express the removal of a state, as its boolean
        // validation rejects null. The state is therefore written through the very storage
        // the setting reads from, which both persists it and keeps the request cached
        // values coherent with what was just written.
        $storage = $this->getStorage($idSite);

        if (is_null($isEnforced)) {
            $storage->unsetValue($this->settingName);
        } else {
            $storage->setValue($this->settingName, $isEnforced);
        }

        $storage->save();
    }

    /**
     * Whether the current user is allowed to change the enforcement state of this
     * setting. Returns false when the state is pinned in `config.ini.php`.
     */
    public function isWritableByCurrentUser(?int $idSite = null): bool
    {
        return $this->makeSetting($idSite)->isWritableByCurrentUser();
    }

    /**
     * Fails unless the current user may change the enforcement state of the given scope.
     * Callers that write more than one scope use this to reject the whole change before
     * any of it is written.
     *
     * @param int|null $idSite The website to check, or null for the instance wide state.
     * @throws Exception when the state cannot be written by the current user
     */
    public function assertWritableByCurrentUser(?int $idSite = null): void
    {
        if ($this->isWritableByCurrentUser($idSite)) {
            return;
        }

        throw new Exception(Piwik::translate(
            'CoreAdminHome_PluginSettingChangeNotAllowed',
            [$this->settingName, $this->pluginName]
        ));
    }

    private function makeSetting(?int $idSite): Setting
    {
        if (is_null($idSite)) {
            return new SystemSetting($this->settingName, null, FieldConfig::TYPE_BOOL, $this->pluginName);
        }

        return new MeasurableSetting($this->settingName, null, FieldConfig::TYPE_BOOL, $this->pluginName, $idSite);
    }

    private function getStorage(?int $idSite): Storage
    {
        /** @var Factory $factory */
        $factory = StaticContainer::get(Factory::class);

        if (is_null($idSite)) {
            return $factory->getPluginStorage($this->pluginName, '');
        }

        return $factory->getMeasurableSettingsStorage($idSite, $this->pluginName);
    }
}
