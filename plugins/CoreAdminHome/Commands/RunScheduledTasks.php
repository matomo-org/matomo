<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\FrontController;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreAdminHome\API;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunScheduledTasks extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:run-scheduled-tasks');
        $this->setDescription('Will run all scheduled tasks due to run at this time.');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'If set, it will execute all tasks even the ones not due to run at this time.');
        $this->addOption('token-auth', null, InputOption::VALUE_REQUIRED, 'The API token of a user that has super user access, see http://piwik.org/faq/general/#faq_114');
    }

    /**
     * Execute command like: ./console core:run-scheduled-tasks
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $_GET['token_auth'] = $this->askForTokenAuth($input, $output);
        $this->forceRunAllTasksIfRequested($input);

        FrontController::getInstance()->init();
        API::getInstance()->runScheduledTasks();

        $this->writeSuccessMessage($output, array('Scheduled Tasks executed'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RuntimeException
     */
    private function askForTokenAuth(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($tokenAuth) {
            if (empty($tokenAuth)) {
                throw new \InvalidArgumentException('You have to specify a token_auth');
            }

            return $tokenAuth;
        };

        $tokenAuth = $input->getOption('token-auth');

        if (empty($tokenAuth)) {
            $dialog    = $this->getHelperSet()->get('dialog');
            $tokenAuth = $dialog->askAndValidate($output, 'Enter the token_auth of a user having super user access: ', $validate);
        } else {
            $validate($tokenAuth);
        }

        return $tokenAuth;
    }

    private function forceRunAllTasksIfRequested(InputInterface $input)
    {
        $force = $input->getOption('force');

        if ($force) {
            $GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS'] = true;
        }
    }
}
