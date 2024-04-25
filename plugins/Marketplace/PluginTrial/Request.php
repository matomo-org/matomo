<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;

class Request
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var Storage
     */
    private $storage;

    public function __construct(string $pluginName, Storage $storage)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->storage = $storage;
    }

    /**
     * Creates a trial request and sends a mail to all super users
     *
     * @return void
     */
    public function create(): void
    {
        if ($this->wasRequested()) {
            return; // already requested
        }

        $this->storage->setRequested();

        $this->sendEmailToSuperUsers();
    }

    /**
     * Cancels a trial request
     *
     * @return void
     */
    public function cancel(): void
    {
        if (!$this->wasRequested()) {
            return; // not requested
        }

        $this->storage->clearStorage();
    }


    /**
     * Returns if a plugin was already requested
     *
     * @return bool
     */
    public function wasRequested(): bool
    {
        return $this->storage->wasRequested();
    }

    /**
     * Send notification email to all super users
     *
     * @return void
     */
    private function sendEmailToSuperUsers(): void
    {
        $superUsers = Piwik::getAllSuperUserAccessEmailAddresses();

        foreach ($superUsers as $login => $email) {
            $email = StaticContainer::getContainer()->make(
                RequestTrialNotificationEmail::class,
                [
                    'emailAddress' => $email,
                    'login' => $login,
                    'pluginName' => $this->pluginName,
                ]
            );

            $email->safeSend();
        }
    }
}
