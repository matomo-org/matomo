<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Mail;
use Piwik\Plugin\ConsoleCommand;
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
        $this->addArgument('email', InputArgument::REQUIRED, 'The destination email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $body    = 'Congrats, your Matomo is correctly configured to send emails from the command line.';

        $mail = new Mail();
        $mail->addTo($email, 'Matomo User');
        $mail->setFrom($email, 'Matomo');
        $mail->setSubject('Test Message');
        $mail->setBodyText($body);
        $mail->send();
        $output->writeln('Message sent');
    }
}
