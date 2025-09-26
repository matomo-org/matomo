<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\LanguagesManager\LanguagesHelper;

abstract class TokenEmailNotification extends TokenNotification
{
    /**
     * A list of recipient emails
     *
     * @var array
     */
    private $recipients;

    /**
     * Data in the format of ['email@example.com' => ['item1' => 'value1'], ...] that will be passed to the email class
     *
     * @var array
     */
    private $emailData;

    /**
     * @param array{login: string, tokenId: string, tokenName: string, tokenDate: string} $tokens
     * @param array $recipients
     * @param array $emailData
     */
    public function __construct(
        array $tokens,
        array $recipients,
        array $emailData
    ) {
        parent::__construct($tokens);

        $this->recipients = array_filter($recipients);
        $this->emailData = $emailData;
    }

    abstract public function getEmailClass(): string;

    public function dispatch(): bool
    {
        $that = $this;
        foreach ($this->recipients as $recipient) {
            LanguagesHelper::doWithUserLanguage($recipient, function () use ($recipient, $that) {
                $email = StaticContainer::getContainer()->make(
                    $that->getEmailClass(),
                    [
                        'notification' => $that,
                        'recipient' => $recipient,
                        'emailData' => $that->emailData[$recipient] ?? [],
                    ]
                );
                $email->safeSend();
            });
        }

        return true;
    }
}
