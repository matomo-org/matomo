<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Config;
use Piwik\Mail;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;

/**
 */
class TestEmail extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:test-email');
        $this->setDescription('Send a test email');
        $this->addRequiredArgument('emailAddress', 'The destination email address');
    }

    protected function doExecute(): int
    {
        $config = Config::getInstance();

        $email = $this->getInput()->getArgument('emailAddress');
        $matomoUrl = SettingsPiwik::getPiwikUrl();
        $body    = "Hello, world! <br/> This is a test email sent from $matomoUrl";
        $subject = "This is a test email sent from $matomoUrl";

        $mail = new Mail();
        $mail->setSmtpDebug(true);
        $mail->addTo($email, 'Matomo User');
        $mail->setFrom($config->General['noreply_email_address'], $config->General['noreply_email_name']);
        $mail->setSubject($subject);
        $mail->setWrappedHtmlBody($body);
        $mail->send();
        $this->getOutput()->writeln('Message sent to ' . $email);

        return self::SUCCESS;
    }
}
