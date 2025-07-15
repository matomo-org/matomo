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
    public const ALL_DATES_STRING = 'all';

    /**
     * For tests.
     *
     * @var Date
     */
    public static $todayOverride = null;

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
        $this->setName('core:purge-broken-archive-data');
        $this->setDescription('Purges broken archive data from archive tables.');
        $this->addOptionalArgument(
            "dateStart",
            "The start date to purge data from. Defaults to start of current month",
            self::getToday()->getStartOfMonth()->toString('Y-m-d')
        );
        $this->addOptionalArgument(
            "dateEnd",
            "The end date to purge data to. Defaults to end of current month",
            self::getToday()->getEndOfMonth()->toString('Y-m-d')
        );
        $this->setHelp("Broken archives are removed from all archive tables between supplied dates.\n\n"
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

        $dateStartStr = $this->getInput()->getArgument('dateStart');
        $dateEndStr = $this->getInput()->getArgument('dateEnd');

        try {
            $startMonth = new Month(Date::factory($dateStartStr));
        } catch (\Exception $e) {
            $output->writeln("Invalid Argument - dateStart - $dateStartStr");
            return self::INVALID;
        }

        try {
            $endMonth = new Month(Date::factory($dateEndStr));
        } catch (\Exception $e) {
            $output->writeln("Invalid Argument - dateEnd - $dateEndStr");
            return self::INVALID;
        }
        $startMonthStr = $startMonth->getDateStart()->toString('Y-m');
        $endMonthStr = $endMonth->getDateStart()->toString('Y-m');
        $output->writeln("Purging broken archives between $startMonthStr and $endMonthStr");
        $rowsPurged = $archivePurger->purgeBrokenArchives($startMonth, $endMonth);
        $output->writeln("Purging complete: Rows purged - $rowsPurged");

        return self::SUCCESS;
    }

    private static function getToday()
    {
        return self::$todayOverride ?: Date::today();
    }
}
