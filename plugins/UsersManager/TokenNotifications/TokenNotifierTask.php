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
        // all checks whether emails can be sent are done in the actual Mail class
        parent::__construct($this, 'sendEmails', null, new Daily());
    }

    /**
     * Get a list of providers that may require token notifications being sent
     * For each of them get a list of tokens and a callback to call when the notification has been sent.
     *
     * @return array
     */
    private function getTokensToNotifyByProvider(): array
    {
        $tokensToNotifyByProvider = [];
        $providers = PluginManager::getInstance()->findComponents(
            'TokenProvider',
            TokenProviderInterface::class
        );

        /** @var TokenProvider $provider */
        foreach ($providers as $provider) {
            $tokensToNotifyByProvider[get_class($provider)] = [
                'tokensToNotify' => $provider->getTokensToNotify(),
                'onTokenNotified' => $provider->onTokenNotified(),
            ];
        }

        return $tokensToNotifyByProvider;
    }

    private function sendNotificationEmail(TokenNotificationInterface $notification): void
    {
        $email = StaticContainer::getContainer()->make(
            $notification->getEmailClass(),
            ['notification' => $notification]
        );
        $email->safeSend();
    }

    /**
     * Send notification email for each provider and its tokens
     */
    public function sendEmails()
    {
        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, Date::factory('today')->getTimestamp());

            $tokensToNotifyByProvider = $this->getTokensToNotifyByProvider();

            // we use `safeSend()` method (as above) so we don't need to do try/catch here
            foreach ($tokensToNotifyByProvider as $providerTokensToNotify) {
                /** @var TokenNotificationInterface $tokenToNotify */
                foreach ($providerTokensToNotify['tokensToNotify'] as $tokenToNotify) {
                    $this->sendNotificationEmail($tokenToNotify);
                    call_user_func($providerTokensToNotify['onTokenNotified'], $tokenToNotify->getTokenId());
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
