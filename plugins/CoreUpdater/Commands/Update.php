<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreUpdater\Controller;
use Piwik\Plugins\CoreUpdater\NoUpdatesFoundException;
use Piwik\Plugins\UserCountry\LocationProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CloudAdmin
 */
class Update extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:update');

        $this->setDescription('Triggers upgrades. Use it after Piwik core or any plugin files have been updated.');

        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only prints out the SQL requests that would be executed during the upgrade');
    }

    /**
     * Execute command like: ./console core:update --dry-run
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doDryRun = (bool) $input->getOption('dry-run');

        try {
            $this->makeUpdate($input, $output, $doDryRun);

            if(!$doDryRun) {
                $this->writeSuccessMessage($output, array("Piwik has been successfully updated!"));
            }

        } catch(NoUpdatesFoundException $e) {
            // Do not fail if no updates were found
            $output->writeln("<info>".$e->getMessage()."</info>");
        } catch (\Exception $e) {
            // Fail in case of any other error during upgrade
            $output->writeln("<error>" . $e->getMessage() . "</error>");
            throw $e;
        }
    }

    protected function makeUpdate(InputInterface $input, OutputInterface $output, $doDryRun)
    {
        $this->checkAllRequiredOptionsAreNotEmpty($input);

        $updateController = new Controller();
        echo $updateController->runUpdaterAndExit($doDryRun);
    }
}