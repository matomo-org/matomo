<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Site;
use Piwik\Period\Factory as PeriodFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a simple interface for invalidating report data by date ranges, site IDs and periods.
 */
class InvalidateReportData extends ConsoleCommand
{
    const ALL_OPTION_VALUE = 'all';

    protected function configure()
    {
        $this->setName('core:invalidate-report-data');
        $this->setDescription('Invalidate archived report data by date range, site and period.');
        $this->addOption('dates', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'List of dates or date ranges to invalidate report data for, eg, 2015-01-03 or 2015-01-05,2015-02-12.');
        $this->addOption('sites', null, InputOption::VALUE_REQUIRED,
            'List of site IDs to invalidate report data for, eg, "1,2,3,4" or "all" for all sites.',
            self::ALL_OPTION_VALUE);
        $this->addOption('periods', null, InputOption::VALUE_REQUIRED,
            'List of period types to invalidate report data for. Can be one or more of the following values: day, '
            . 'week, month, year or "all" for all of them.',
            self::ALL_OPTION_VALUE);
        $this->addOption('cascade', null, InputOption::VALUE_NONE,
            'If supplied, invalidation will cascade, invalidating child period types even if they aren\'t specified in'
            . ' --periods. For example, if --periods=week, --cascade will cause the days within those weeks to be '
            . 'invalidated as well. If --periods=month, then weeks and days will be invalidated. Note: if a period '
            . 'falls partly outside of a date range, then --cascade will also invalidate data for child periods '
            . 'outside the date range. For example, if --dates=2015-09-14,2015-09-15 & --periods=week, --cascade will'
            . ' also invalidate all days within 2015-09-13,2015-09-19, even those outside the date range.');
        $this->setHelp('Invalidate archived report data by date range, site and period. Invalidated archive data will '
            . 'be re-archived during the next core:archive run. If your log data has changed for some reason, this '
            . 'command can be used to make sure reports are generated using the new, changed log data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');

        $cascade = $input->getOption('cascade');

        $sites = $this->getSitesToInvalidateFor($input);
        $periodTypes = $this->getPeriodTypesToInvalidateFor($input);
        $dateRanges = $this->getDateRangesToInvalidateFor($input);

        $datesByPeriod = $this->getPeriodsToInvalidateFor($periodTypes, $dateRanges, $cascade);

        foreach ($datesByPeriod as $periodType => $dates) {
            $output->writeln("Invalidating $periodType periods...");

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln("  [ dates = " . implode(', ', $dates) . " ]");
            }

            $invalidationResult = $invalidator->markArchivesAsInvalidated($sites, $dates, $periodType);

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln($invalidationResult->makeOutputLogs());
            }
        }
    }

    private function getSitesToInvalidateFor(InputInterface $input)
    {
        $sites = $input->getOption('sites');
        if (empty($sites)) {
            throw new \InvalidArgumentException("Invalid value given for --sites option: '$sites'.");
        }

        return Site::getIdSitesFromIdSitesString($sites);
    }

    private function getPeriodTypesToInvalidateFor(InputInterface $input)
    {
        $periods = $input->getOption('periods');
        if (empty($periods)) {
            throw new \InvalidArgumentException("The --periods argument is required.");
        }

        if ($periods == self::ALL_OPTION_VALUE) {
            $result = array_keys(Piwik::$idPeriods);
            unset($result[4]); // remove 'range' period
            return $result;
        }

        $periods = explode(',', $periods);
        $periods = array_map('trim', $periods);

        foreach ($periods as $periodIdentifier) {
            if ($periodIdentifier == 'range') {
                throw new \InvalidArgumentException("Invalidating range periods is not currently supported.");
            }

            if (!isset(Piwik::$idPeriods[$periodIdentifier])) {
                throw new \InvalidArgumentException("Invalid period type '$periodIdentifier' supplied in --periods.");
            }
        }

        return $periods;
    }

    /**
     * @param InputInterface $input
     * @return Date[][]
     */
    private function getDateRangesToInvalidateFor(InputInterface $input)
    {
        $dateRanges = $input->getOption('dates');
        if (empty($dateRanges)) {
            throw new \InvalidArgumentException("The --dates option is required.");
        }

        $result = array();
        foreach ($dateRanges as $dateRangeString) {
            $parts = explode(',', $dateRangeString);

            if (count($parts) == 1) {
                $parts[1] = $parts[0];
            } else if (count($parts) > 2) {
                throw new \InvalidArgumentException("Invalid date range specifier: '$dateRangeString'.");
            }

            // validate dates
            Date::factory($parts[0]);
            Date::factory($parts[1]);

            $result[] = array($parts[0], $parts[1]);
        }
        return $result;
    }

    /**
     * TODO test in a unit test
     *
     * @param string[] $periodTypes
     * @param Date[][] $dateRanges
     * @param bool $cascade
     * @return Date[][]
     */
    public function getPeriodsToInvalidateFor($periodTypes, $dateRanges, $cascade)
    {
        $result = array();

        foreach ($dateRanges as $range) {
            /**
             *  @var Date $startDate
             *  @var Date $endDate
             */
            list($startDate, $endDate) = $range;

            foreach ($periodTypes as $type) {
                /** @var Range $periodObj */
                $periodObj = PeriodFactory::build($type, $startDate . ',' . $endDate);

                $subperiods = $periodObj->getSubperiods();
                foreach ($subperiods as $subperiod) {
                    $result[$type][] = $subperiod->getDateStart()->toString();
                }

                if ($cascade) {
                    $realStartDate = reset($subperiods)->getDateStart()->toString();
                    $realEndDate = reset($subperiods)->getDateEnd()->toString();

                    $this->addPeriodsToCascadeOn($result, $type, $realStartDate, $realEndDate);
                }
            }
        }

        foreach ($result as $periodType => $dates) {
            $result[$periodType] = array_unique($dates);
        }

        return $result;
    }

    /**
     * TODO
     *
     * @param Date[][] $result
     * @param string $periodType
     * @param string $startDate
     * @param string $endDate
     */
    private function addPeriodsToCascadeOn(&$result, $periodType, $startDate, $endDate)
    {
        $childPeriodType = $this->getChildPeriod($periodType);
        if (empty($childPeriodType)) {
            return;
        }

        $childPeriods = PeriodFactory::build($childPeriodType, $startDate . ',' . $endDate);

        $subperiods = $childPeriods->getSubperiods();
        foreach ($subperiods as $childPeriod) {
            $result[$childPeriod->getLabel()][] = $childPeriod->getDateStart()->toString();
        }

        /** @var Date $realStartDate */
        $realStartDate = reset($subperiods)->getDateStart();
        if ($realStartDate->isEarlier(Date::factory($startDate))) {
            $startDate = $realStartDate->toString();
        }

        /** @var Date $realEndDate */
        $realEndDate = end($subperiods)->getDateEnd();
        if ($realEndDate->isLater(Date::factory($endDate))) {
            $endDate = $realEndDate->toString();
        }

        $this->addPeriodsToCascadeOn($result, $childPeriodType, $startDate, $endDate);
    }

    private function getChildPeriod($periodType)
    {
        switch ($periodType) {
            case 'year':
                return 'month';
            case 'month':
                return 'week';
            case 'week':
                return 'day';
            default:
                return null;
        }
    }
}
