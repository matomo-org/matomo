<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Mail;
use Piwik\Mail\OAuth\AccessTokenProvider;
use Piwik\Mail\OAuth\TokenRequestFailedException;
use Piwik\Plugin\ConsoleCommand;
use Piwik\SettingsPiwik;
use Symfony\Component\Console\Formatter\OutputFormatter;

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

        if (strtolower((string) ($config->mail['type'] ?? '')) === 'xoauth2') {
            // always test with a new access token, not with a cached token or a remembered failure
            StaticContainer::get(AccessTokenProvider::class)->forgetAccessToken();
        }

        try {
            $mail->send();
        } catch (TokenRequestFailedException $e) {
            if ($e->getDetail() !== '') {
                $this->getOutput()->writeln('Reason: ' . OutputFormatter::escape($e->getDetail()));
            }

            throw $e;
        }

        $this->getOutput()->writeln('Message sent to ' . $email);

        return self::SUCCESS;
    }
}
