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
use Piwik\Plugins\UsersManager\TokenProvider;
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
     * Get a list of providers that may require token notifications being dispatched
     *
     * @return array
     */
    private function getTokenProviders(): array
    {
        return PluginManager::getInstance()->findComponents(
            'TokenProvider',
            TokenProviderInterface::class
        );
    }

    /**
     * Dispatch notifications for each provider and its tokens
     */
    public function dispatchNotifications()
    {
        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, Date::factory('today')->getTimestamp());

            /** @var TokenProviderInterface $provider */
            foreach ($this->getTokenProviders() as $provider) {
                foreach ($provider->getTokensToNotify() as $tokenNotification) {
                    $tokenNotification->dispatch();
                    $provider->setTokenNotified($tokenNotification->getTokenId());
                }
            }

            // reschedule for next run
            /** @var Scheduler $scheduler */
            $scheduler = StaticContainer::getContainer()->get(Scheduler::class);
            // reschedule to ensure it's not run again in an hour
            $scheduler->rescheduleTask(new static());
        } catch (Exception $ex) {
            StaticContainer::get(LoggerInterface::class)->error($ex);
            throw $ex;
        }
    }
}
