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
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreAdminHome\Utility\DuplicateActionRemover;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO
 */
class FixDuplicateLogActions extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:fix-duplicate-log-actions');
        $this->setDescription('Removes duplicates in the log action table and fixes references to the duplicates in '
                            . 'related tables. NOTE: This action can take a long time to run!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resolver = new DuplicateActionRemover();
        $numberRemoved = $resolver->removeDuplicateActionsFromDb();

        $table = Common::prefixTable('log_action');
        $this->writeSuccessMessage($output, array(
            "Found and deleted $numberRemoved duplicate action entries in the $table table.",
            "References in log_link_visit_action, log_conversion and log_conversion_item"
        ));
    }
}
