<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\UserNotifications;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Scheduler\Schedule\Monthly;
use Piwik\Scheduler\Task;

/**
 * Send user notifications for each provider
 */
class UserNotifierTask extends Task
{
    public const LAST_RUN_TIME_OPTION_NAME = 'UserNotifier.lastRunTime';

    public function __construct()
    {
        $monthlyOnFirst = new Monthly();
        $monthlyOnFirst->setDay(1);

        parent::__construct($this, 'dispatchNotifications', null, $monthlyOnFirst);
    }

    /**
     * Get a list of providers (class names) that may provide user notifications to be dispatched
     *
     * @return array
     */
    private function getUserNotificationProviderClasses(): array
    {
        return PluginManager::getInstance()->findMultipleComponents(
            'UserNotifications',
            UserNotificationProviderInterface::class
        );
    }

    /**
     * Dispatch notifications for each provider and its users
     */
    public function dispatchNotifications()
    {
        $container = StaticContainer::getContainer();
        $logger = $container->get(LoggerInterface::class);

        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, (string) Date::factory('today')->getTimestamp());

            $notificationsToDispatchCount = 0;
            $notificationsDispatchedCount = 0;

            foreach ($this->getUserNotificationProviderClasses() as $providerClass) {
                /** @var UserNotificationProviderInterface $provider */
                $provider = $container->get($providerClass);

                $providerUserNotificationsForDispatch = $provider->getUserNotificationsForDispatch();
                $notificationsToDispatchCount += count($providerUserNotificationsForDispatch);

                foreach ($providerUserNotificationsForDispatch as $userNotification) {
                    $dispatched = $userNotification->dispatch();
                    if ($dispatched) {
                        $provider->setUserNotificationDispatched($userNotification->getUsers());
                        $notificationsDispatchedCount++;
                    }
                }
            }

            if ($notificationsToDispatchCount) {
                $logger->info(
                    "Number of user notifications dispatched: {number} of {total}.",
                    ['number' => $notificationsDispatchedCount, 'total' => $notificationsToDispatchCount]
                );
            } else {
                $logger->info("No user notifications to dispatch, task rescheduled.");
            }
        } catch (Exception $ex) {
            $container->get(LoggerInterface::class)->error($ex);
            throw $ex;
        }
    }
}
