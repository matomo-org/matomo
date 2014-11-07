<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreUpdater\Controller;
use Piwik\Plugins\CoreUpdater\NoUpdatesFoundException;
use Piwik\Plugins\UserCountry\LocationProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @package CloudAdmin
 */
class Update extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:update');

        $this->setDescription('Triggers upgrades. Use it after Piwik core or any plugin files have been updated.');

        $this->addOption('yes', null, InputOption::VALUE_NONE, 'Directly execute the update without asking for confirmation');
    }

    /**
     * Execute command like: ./console core:update --yes
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeClearCaches();

        $yes = $input->getOption('yes');

        try {
            $this->makeUpdate($input, $output, true);

            if (!$yes) {
                $yes = $this->askForUpdateConfirmation($input, $output);
            }

            if ($yes) {
                $output->writeln("\nStarting the database upgrade process now. This may take a while, so please be patient.");

                $this->makeUpdate($input, $output, false);

                $this->writeSuccessMessage($output, array("Piwik has been successfully updated!"));
            } else {
                $this->writeSuccessMessage($output, array('Database upgrade not executed.'));
            }

        } catch(NoUpdatesFoundException $e) {
            // Do not fail if no updates were found
            $this->writeSuccessMessage($output, array($e->getMessage()));
        } catch (\Exception $e) {
            // Fail in case of any other error during upgrade
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            throw $e;
        }
    }

    private function askForUpdateConfirmation(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('<comment>A database upgrade is required. Execute update? (y/N) </comment>', false);

        return $helper->ask($input, $output, $question);
    }

    protected function executeClearCaches()
    {
        Filesystem::deleteAllCacheOnUpdate();
    }

    protected function makeUpdate(InputInterface $input, OutputInterface $output, $doDryRun)
    {
        $this->checkAllRequiredOptionsAreNotEmpty($input);

        $updateController = new Controller();
        $content = $updateController->runUpdaterAndExit($doDryRun);

        $output->writeln($content);
    }
}