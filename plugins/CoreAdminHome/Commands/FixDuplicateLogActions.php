<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Common;
use Piwik\DataAccess\ArchiveInvalidator;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreAdminHome\Utility\DuplicateActionRemover;
use Piwik\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes duplicate log_action rows and fixes references to these duplicate rows
 * in
 */
class FixDuplicateLogActions extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:fix-duplicate-log-actions');
        $this->addOption('invalidate-archives', null, InputOption::VALUE_NONE, "If supplied, archives for logs that use duplicate actions will be invalidated."
            . " On the next cron archive run, the reports for those dates will be re-processed.");
        $this->setDescription('Removes duplicates in the log action table and fixes references to the duplicates in '
                            . 'related tables. NOTE: This action can take a long time to run!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invalidateArchives = $input->getOption('invalidate-archives');

        $timer = new Timer();

        $archiveInvalidator = $invalidateArchives ? new ArchiveInvalidator() : null;
        $resolver = new DuplicateActionRemover($archiveInvalidator);

        list($numberRemoved, $archivesAffected) = $resolver->removeDuplicateActionsFromDb();

        if (!$invalidateArchives) {
            $output->writeln("The following archives used duplicate actions and should be invalidated if you want correct reports:");
            foreach ($archivesAffected as $archiveInfo) {
                $output->writeln("\t[ idSite = {$archiveInfo['idsite']}, date = {$archiveInfo['server_time']} ]");
            }
        }

        $table = Common::prefixTable('log_action');
        $this->writeSuccessMessage($output, array(
            "Found and deleted $numberRemoved duplicate action entries in the $table table.",
            "References in log_link_visit_action, log_conversion and log_conversion_item were corrected.",
            $timer->__toString()
        ));
    }
}
