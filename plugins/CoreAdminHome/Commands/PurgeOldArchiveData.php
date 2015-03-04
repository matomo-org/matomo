<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Archive;
use Piwik\Archive\Purger;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Timer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO
 */
class PurgeOldArchiveData extends ConsoleCommand
{
    const ALL_DATES_STRING = 'all';

    /**
     * @var Purger
     */
    private $archivePurger;

    public function __construct(Purger $archivePurger = null)
    {
        parent::__construct();

        $this->archivePurger = $archivePurger ?: new Purger();
    }

    protected function configure()
    {
        $this->setName('core:purge-old-archive-data');
        $this->setDescription('Purges old and invalid archive data from archive tables.');
        $this->addArgument("dates", InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            "The months of the archive tables to purge data from. By default, only deletes from the current month. Use '" . self::ALL_DATES_STRING. "' for all dates.",
            array(Date::today()->toString()));
        $this->addOption('exclude-outdated', null, InputOption::VALUE_NONE, "Do not purge outdated archive data.");
        $this->addOption('exclude-invalidated', null, InputOption::VALUE_NONE, "Do not purge invalidated archive data.");
        $this->setHelp("By default old and invalidated archives are purged. Custom ranges are also purged with outdated archives.\n\n"
                     . "Note: archive purging is done during scheduled task execution, so under normal circumstances, you should not need to "
                     . "run this command manually.");

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $dates = $this->getDatesToPurgeFor($input);

        $excludeOutdated = $input->getOption('exclude-outdated');
        if ($excludeOutdated) {
            $output->writeln("Skipping purge outdated archive data.");
        } else {
            foreach ($dates as $date) {
                $message = sprintf("Purging outdated archives for %s...", $date->toString('Y_m'));
                $this->performTimedPurging($output, $message, function () use ($date, $self) {
                    $self->archivePurger->purgeOutdatedArchives($date);
                });
            }
        }

        $excludeInvalidated = $input->getOption('exclude-invalidated');
        if ($excludeInvalidated) {
            $output->writeln("Skipping purge invalidated archive data.");
        } else {
            $this->performTimedPurging($output, "Purging invalidated archives...", function () use ($self) {
                $self->archivePurger->purgeInvalidatedArchives();
            });
        }
    }

    /**
     * @param InputInterface $input
     * @return Date[]
     */
    private function getDatesToPurgeFor(InputInterface $input)
    {
        $dates = array();

        $dateSpecifier = $input->getArgument('dates');
        if (count($dateSpecifier) === 1
            && reset($dateSpecifier) == self::ALL_DATES_STRING
        ) {
            foreach (ArchiveTableCreator::getTablesArchivesInstalled() as $table) {
                $tableDate = ArchiveTableCreator::getDateFromTableName($table);

                list($year, $month) = explode('_', $tableDate);

                $dates[] = Date::factory($year . '-' . $month . '-' . '01');
            }
        } else {
            foreach ($dateSpecifier as $date) {
                $dates[] = Date::factory($date);
            }
        }

        return $dates;
    }

    private function performTimedPurging(OutputInterface $output, $startMessage, $callback)
    {
        $timer = new Timer();

        $output->write($startMessage);

        $callback();

        $output->writeln("Done. <comment>[" . $timer->__toString() . "]</comment>");
    }
}
