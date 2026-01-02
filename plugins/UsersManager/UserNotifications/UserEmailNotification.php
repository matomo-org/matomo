<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\UserNotifications;

use Piwik\Container\StaticContainer;

abstract class UserEmailNotification extends UserNotification
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
     * @param array $users A list of users this notification is about
     * @param array $recipients A list of recipients this notification will be sent to
     * @param array $emailData Optional additional data passed to the email notification indexed by a recipient
     */
    public function __construct(
        array $users,
        array $recipients,
        array $emailData = []
    ) {
        parent::__construct($users);

        $this->recipients = array_filter($recipients);
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
