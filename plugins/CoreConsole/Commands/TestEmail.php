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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class TestEmail extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:test-email');
        $this->setDescription('Send a test email');
        $this->addArgument('emailAddress', InputArgument::REQUIRED, 'The destination email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::getInstance();

        $email = $input->getArgument('emailAddress');
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
        $output->writeln('Message sent to ' . $email);
    }
}
