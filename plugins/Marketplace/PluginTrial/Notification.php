<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Notification as MatomoNotification;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Url;

class Notification
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
     * Dismisses the notification for the current user
     *
     * @return void
     */
    public function setNotificationDismissed(): void
    {
        $this->storage->setNotificationDismissed();
        $this->removeFromSession();
    }

    /**
     * Creates a plugin trial notification for the current user if needed
     *
     * @return void
     * @throws Exception
     */
    public function createNotificationIfNeeded(): void
    {
        if (!$this->storage->wasRequested() || $this->storage->isNotificationDismissed()) {
            return; // not requested or current user already dismissed the notification
        }

        $marketplaceUrl = Url::getCurrentQueryStringWithParametersModified([
            'module' => 'Marketplace',
            'action' => 'overview'
        ]);
        $link = sprintf('<a href="%s#popover=browsePluginDetail%%243A%s">', $marketplaceUrl, $this->pluginName);
        $message = '<b>' . Piwik::translate(
            'Marketplace_TrialRequestedNotification1',
            [htmlentities($this->storage->getDisplayName()), $link, '</a>']
        ) . '</b><br><br>';
        $message .= Piwik::translate('Marketplace_TrialRequestedNotification2', [htmlentities($this->storage->getDisplayName()), $link, '</a>']);

        $notification = new MatomoNotification($message);
        $notification->raw = true;
        $notification->context = MatomoNotification::CONTEXT_INFO;
        $notification->type = MatomoNotification::TYPE_PERSISTENT;
        MatomoNotification\Manager::cancel($this->getNotificationId());
        MatomoNotification\Manager::notify($this->getNotificationId(), $notification);
    }

    /**
     * Removes a notification from current users session
     *
     * @return void
     */
    public function removeFromSession(): void
    {
        MatomoNotification\Manager::cancel($this->getNotificationId());
    }

    private function getNotificationId(): string
    {
        return sprintf('Marketplace_PluginTrialRequest_%s_%s', md5(Piwik::getCurrentUserLogin()), $this->pluginName);
    }
}
