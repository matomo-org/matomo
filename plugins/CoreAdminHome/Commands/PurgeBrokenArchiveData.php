<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Archive\ArchivePurger;
use Piwik\Date;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Log\NullLogger;
use Piwik\Period\Month;

/**
 * Command that allows users to force purge old or invalid archive data. In the event of a failure
 * in the archive purging scheduled task, this command can be used to manually delete old/invalid archives.
 */
class PurgeBrokenArchiveData extends ConsoleCommand
{
    /**
     * @var ArchivePurger
     */
    private $archivePurger;

    public function __construct(?ArchivePurger $archivePurger = null)
    {
        parent::__construct();

        $this->archivePurger = $archivePurger;
    }

    protected function configure()
    {
        $currentMonth = new Month(Date::today());
        $this->setName('core:purge-broken-archive-data');
        $this->setDescription('Purges broken archive data from archive tables.');
        $this->addOptionalArgument(
            "startMonth",
            "The start month to purge data from in format YYYY-MM. Defaults to current month",
            $currentMonth->getDateStart()->toString('Y-m')
        );
        $this->addOptionalArgument(
            "endMonth",
            "The end month to purge data to in format YYYY-MM. Defaults to current month",
            $currentMonth->getDateStart()->toString('Y-m')
        );
        $this->setHelp("Broken archives are removed from all archive tables between supplied months inclusive.\n\n"
                     . "Note: archive purging is done during scheduled task execution, so under normal circumstances, you should not need to "
                     . "run this command manually.");
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        // during normal command execution, we don't want the INFO level logs logged by the ArchivePurger service
        // to display in the console, so we use a NullLogger for the service
        $logger = null;
        if (!$output->isVerbose()) {
            $logger = new NullLogger();
        }

        $archivePurger = $this->archivePurger ?: new ArchivePurger($model = null, $purgeDatesOlderThan = null, $logger);

        $startMonthStr = $this->getInput()->getArgument('startMonth');
        $endMonthStr = $this->getInput()->getArgument('endMonth');

        $yearMonthRegex = '/^(19[7-9][0-9]|2[0-9]{3})-(0[1-9]|1[0-2])$/';
        try {
            // check format of string
            if (!preg_match($yearMonthRegex, $startMonthStr)) {
                throw new \Exception();
            }
            $startMonth = new Month(Date::factory($startMonthStr));
        } catch (\Exception $e) {
            $output->writeln("Invalid Argument - startMonth - $startMonthStr");
            return self::INVALID;
        }

        try {
            if (!preg_match($yearMonthRegex, $endMonthStr)) {
                throw new \Exception();
            }
            $endMonth = new Month(Date::factory($endMonthStr));
        } catch (\Exception $e) {
            $output->writeln("Invalid Argument - endMonth - $endMonthStr");
            return self::INVALID;
        }
        $startMonthStr = $startMonth->getDateStart()->toString('Y-m');
        $endMonthStr = $endMonth->getDateStart()->toString('Y-m');
        $output->writeln("Purging broken archives between $startMonthStr and $endMonthStr");
        $rowsPurged = $archivePurger->purgeBrokenArchives($startMonth, $endMonth);
        $output->writeln("Purging complete: Rows purged - $rowsPurged");

        return self::SUCCESS;
    }
}
