<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Scheduler\Schedule\Daily;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;

/**
 * Send token notifications for each provider
 */
class TokenNotifierTask extends Task
{
    public const LAST_RUN_TIME_OPTION_NAME = 'TokenNotifier.lastRunTime';

    public function __construct()
    {
        parent::__construct($this, 'dispatchNotifications', null, new Daily());
    }

    /**
     * Get a list of providers (class names) that may provide token notifications to be dispatched
     *
     * @return array
     */
    private function getTokenProviderClasses(): array
    {
        return PluginManager::getInstance()->findComponents(
            'TokenProvider',
            TokenNotificationProviderInterface::class
        );
    }

    /**
     * Dispatch notifications for each provider and its tokens
     */
    public function dispatchNotifications()
    {
        $container = StaticContainer::getContainer();

        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, Date::factory('today')->getTimestamp());

            foreach ($this->getTokenProviderClasses() as $providerClass) {
                /** @var TokenNotificationProviderInterface $provider */
                $provider = $container->get($providerClass);
                foreach ($provider->getTokenNotificationsForDispatch() as $tokenNotification) {
                    $dispatched = $tokenNotification->dispatch();
                    if ($dispatched) {
                        $provider->setTokenNotificationDispatched($tokenNotification->getTokenId());
                    }
                }
            }

            // reschedule for next run
            /** @var Scheduler $scheduler */
            $scheduler = $container->get(Scheduler::class);
            // reschedule to ensure it's not run again in an hour
            $scheduler->rescheduleTask(new static());
        } catch (Exception $ex) {
            $container->get(LoggerInterface::class)->error($ex);
            throw $ex;
        }
    }
}
