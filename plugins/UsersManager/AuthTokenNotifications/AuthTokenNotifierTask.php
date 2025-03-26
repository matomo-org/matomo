<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\AuthTokenNotifications;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\UsersManager\AuthTokenProvider;
use Piwik\Scheduler\Schedule\Daily;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;

/**
 * Used to automatically update installed GeoIP 2 databases, and manages the updater's
 * scheduled task.
 */
class AuthTokenNotifierTask extends Task
{
    public const LAST_RUN_TIME_OPTION_NAME = 'AuthTokenNotifier.lastRunTime';

    public function __construct()
    {
        // all checks whether emails can be sent are done in the actual Mail class

        parent::__construct($this, 'sendEmails', null, new Daily());
    }

    /**
     * @return AuthTokenNotification[]
     */
    private function getAuthTokensToNotify(): array
    {
        $tokensToNotify = [];
        $providers = PluginManager::getInstance()->findComponents(
            'AuthTokenProvider',
            AuthTokenProviderInterface::class
        );

        /** @var AuthTokenProvider $provider */
        foreach ($providers as $provider) {
            array_push($tokensToNotify, ...$provider->getAuthTokensToNotify());
        }

        return $tokensToNotify;
    }

    /**
     * Attempts to download new location & ISP GeoIP databases and
     * replace the existing ones w/ them.
     */
    public function sendEmails()
    {
        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, Date::factory('today')->getTimestamp());

            $tokensToNotify = $this->getAuthTokensToNotify();

            // notification emails should be using `safeSend()` method so we don't do try/catch here
            foreach ($tokensToNotify as $tokenToNotify) {
                $tokenToNotify->sendNotification();
            }

            // reschedule for next run
            /** @var Scheduler $scheduler */
            $scheduler = StaticContainer::getContainer()->get(Scheduler::class);
            // reschedule to ensure it's not run again in an hour
            $scheduler->rescheduleTask(new AuthTokenNotifierTask());
        } catch (Exception $ex) {
            // message will already be prefixed w/ 'GeoIP2AutoUpdater: '
            Log::error($ex);
            throw $ex;
        }
    }
}
