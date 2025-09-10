<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Container\StaticContainer;

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

    public function __construct(
        string $tokenId,
        string $tokenName,
        string $tokenCreationDate,
        array $recipients,
        array $emailData
    ) {
        parent::__construct($tokenId, $tokenName, $tokenCreationDate);

        $this->recipients = $recipients;
        $this->emailData = $emailData;
    }

    abstract public function getEmailClass(): string;

    public function dispatch(): bool
    {
        foreach ($this->recipients as $recipient) {
            $email = StaticContainer::getContainer()->make(
                $this->getEmailClass(),
                [
                    'notification' => $this,
                    'recipient' => $recipient,
                    'emailData' => $this->emailData[$recipient] ?? [],
                ]
            );
            $email->safeSend();
        }

        return true;
    }
}
