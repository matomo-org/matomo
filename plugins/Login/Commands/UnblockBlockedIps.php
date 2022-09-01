<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Commands;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnblockBlockedIps extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('login:unblock-blocked-ips');
        $this->setDescription('Unblocks all currently blocked IPs. Useful if you cannot log in to your Matomo anymore because your own IP is blocked');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Request::processRequest('Login.unblockBruteForceIPs');
        $message = sprintf('<info>%s</info>', Piwik::translate('General_Done'));

        $output->writeln($message);
    }
}
