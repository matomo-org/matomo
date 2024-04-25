<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Config\GeneralConfig;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

class Storage
{
    private const OPTION_NAME = 'Marketplace.PluginTrialRequest.%s';
    private $pluginName;
    private $optionName;
    private $storage = [];

    public function __construct(string $pluginName)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->optionName = sprintf(self::OPTION_NAME, $pluginName);
        $this->loadStorage();
    }

    /**
     * Creates a trial request for the current user
     *
     * @return void
     */
    public function setRequested(): void
    {
        $this->storage = [
            'requestTime' => time(),
            'dismissed' => [],
            'requestedBy' => Piwik::getCurrentUserLogin(),
        ];
        $this->saveStorage();
    }

    /**
     * Returns if a plugin was already requested
     *
     * @return bool
     */
    public function wasRequested(): bool
    {
        if (empty($this->storage)) {
            return false;
        }

        $expirationTime = (int) GeneralConfig::getConfigValue('plugin_trial_request_expiration_in_days');

        if ($this->storage['requestTime'] < (time() - $expirationTime * 24 * 3600)) {
            $this->clearStorage(); // remove outdated request
            return false;
        }

        return true;
    }

    /**
     * Dismisses the trial request for the current user
     *
     * @return void
     */
    public function setNotificationDismissed(): void
    {
        $this->storage['dismissed'][] = Piwik::getCurrentUserLogin();
        $this->saveStorage();
    }

    /**
     * Returns if the current user has dismissed the trial request
     *
     * @return bool
     */
    public function isNotificationDismissed(): bool
    {
        return !empty($this->storage['dismissed']) && in_array(Piwik::getCurrentUserLogin(), $this->storage['dismissed']);
    }

    /**
     * Removes the trial request from storage
     *
     * @return void
     */
    public function clearStorage(): void
    {
        Option::delete($this->optionName);
    }

    /**
     * Returns the names of plugins where trial requests are stored for, sorted by request time descending
     *
     * @return array
     */
    public static function getPluginsInStorage(): array
    {
        $plugins = [];
        $trialRequests = Option::getLike(sprintf(self::OPTION_NAME, '%'));

        foreach ($trialRequests as $trialRequest => $data) {
            $data = json_decode($data, true);
            $plugins[str_replace(sprintf(self::OPTION_NAME, ''), '', $trialRequest)] = $data['requestTime'];
        }

        arsort($plugins);

        return array_keys($plugins);
    }

    protected function loadStorage(): void
    {
        $this->storage = json_decode(Option::get($this->optionName) ?: '[]', true);
    }

    protected function saveStorage(): void
    {
        Option::set($this->optionName, json_encode($this->storage));
    }
}
