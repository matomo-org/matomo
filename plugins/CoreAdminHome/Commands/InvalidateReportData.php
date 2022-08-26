<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Segment;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Site;
use Piwik\Period\Factory as PeriodFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a simple interface for invalidating report data by date ranges, site IDs and periods.
 */
class InvalidateReportData extends ConsoleCommand
{
    const ALL_OPTION_VALUE = 'all';

    private $allSegments = null;

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
        $this->addOption('segment', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'List of segments to invalidate report data for. This can be the segment string itself, the segment name from the UI or the ID of the segment.'
            . ' If specifying the segment definition, make sure it is encoded properly (it should be the same as the segment parameter in the URL.');
        $this->addOption('cascade', null, InputOption::VALUE_NONE,
            'If supplied, invalidation will cascade, invalidating child period types even if they aren\'t specified in'
            . ' --periods. For example, if --periods=week, --cascade will cause the days within those weeks to be '
            . 'invalidated as well. If --periods=month, then weeks and days will be invalidated. Note: if a period '
            . 'falls partly outside of a date range, then --cascade will also invalidate data for child periods '
            . 'outside the date range. For example, if --dates=2015-09-14,2015-09-15 & --periods=week, --cascade will'
            . ' also invalidate all days within 2015-09-13,2015-09-19, even those outside the date range.');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'For tests. Runs the command w/o actually '
            . 'invalidating anything.');
        $this->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'To invalidate data for a specific plugin only.');
        $this->addOption('ignore-log-deletion-limit', null, InputOption::VALUE_NONE,
            'Ignore the log purging limit when invalidating archives. If a date is older than the log purging threshold (which means '
            . 'there should be no log data for it), we normally skip invalidating it in order to prevent losing any report data. In some cases, '
            . 'however it is useful, if, for example, your site was imported from Google, and there is never any log data.');
        $this->setHelp('Invalidate archived report data by date range, site and period. Invalidated archive data will '
            . 'be re-archived during the next core:archive run. If your log data has changed for some reason, this '
            . 'command can be used to make sure reports are generated using the new, changed log data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');

        $cascade = $input->getOption('cascade');
        $dryRun = $input->getOption('dry-run');
        $plugin = $input->getOption('plugin');
        $ignoreLogDeletionLimit = $input->getOption('ignore-log-deletion-limit');

        $sites = $this->getSitesToInvalidateFor($input);
        $periodTypes = $this->getPeriodTypesToInvalidateFor($input);
        $dateRanges = $this->getDateRangesToInvalidateFor($input);
        $segments = $this->getSegmentsToInvalidateFor($input, $sites, $output);

        $logger = StaticContainer::get(LoggerInterface::class);

        foreach ($periodTypes as $periodType) {
            if ($periodType === 'range') {
                continue; // special handling for range below
            }
            foreach ($dateRanges as $dateRange) {
                foreach ($segments as $segment) {
                    $segmentStr = $segment ? $segment->getString() : '';

                    $logger->info("Invalidating $periodType periods in $dateRange [segment = $segmentStr]...");

                    $dates = $this->getPeriodDates($periodType, $dateRange);

                    if ($dryRun) {
                        $message = "[Dry-run] invalidating archives for site = [ " . implode(', ', $sites)
                            . " ], dates = [ " . implode(', ', $dates) . " ], period = [ $periodType ], segment = [ "
                            . "$segmentStr ], cascade = [ " . (int)$cascade . " ]";
                        if (!empty($plugin)) {
                            $message .= ", plugin = [ $plugin ]";
                        }
                        $logger->info($message);
                    } else {
                        $invalidationResult = $invalidator->markArchivesAsInvalidated($sites, $dates, $periodType, $segment, $cascade,
                            false, $plugin, $ignoreLogDeletionLimit);

                        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                            foreach ($invalidationResult->makeOutputLogs() as $outputLog) {
                                $logger->info($outputLog);
                            }
                        }
                    }
                }
            }
        }

        $periods = trim($input->getOption('periods'));
        $isUsingAllOption = $periods === self::ALL_OPTION_VALUE;
        if ($isUsingAllOption || in_array('range', $periodTypes)) {
            $rangeDates = array();
            foreach ($dateRanges as $dateRange) {
                if ($isUsingAllOption
                    && !Period::isMultiplePeriod($dateRange, 'day')) {
                    continue; // not a range, nothing to do... only when "all" option is used
                }

                $rangeDates[] = $this->getPeriodDates('range', $dateRange);
            }
            if (!empty($rangeDates)) {
                foreach ($segments as $segment) {
                    $segmentStr = $segment ? $segment->getString() : '';
                    if ($dryRun) {
                        $dateRangeStr = implode(';', $dateRanges);
                        $logger->info("Invalidating range periods overlapping $dateRangeStr [segment = $segmentStr]...");
                    } else {
                        $invalidator->markArchivesOverlappingRangeAsInvalidated($sites, $rangeDates, $segment);
                    }
                }
            }
        }
    }

    private function getSitesToInvalidateFor(InputInterface $input)
    {
        $sites = $input->getOption('sites');

        $siteIds = Site::getIdSitesFromIdSitesString($sites);
        if (empty($siteIds)) {
            throw new \InvalidArgumentException("Invalid --sites value: '$sites'.");
        }

        $allSiteIds = SitesManagerAPI::getInstance()->getAllSitesId();
        foreach ($siteIds as $idSite) {
            if (!in_array($idSite, $allSiteIds)) {
                throw new \InvalidArgumentException("Invalid --sites value: '$sites', there are no sites with IDs = $idSite");
            }
        }

        return $siteIds;
    }

    private function getPeriodTypesToInvalidateFor(InputInterface $input)
    {
        $periods = $input->getOption('periods');
        if (empty($periods)) {
            throw new \InvalidArgumentException("The --periods argument is required.");
        }

        if ($periods == self::ALL_OPTION_VALUE) {
            $result = array_keys(Piwik::$idPeriods);
            return $result;
        }

        $periods = explode(',', $periods);
        $periods = array_map('trim', $periods);

        foreach ($periods as $periodIdentifier) {
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

        return $dateRanges;
    }

    private function getPeriodDates($periodType, $dateRange)
    {
        if (!isset(Piwik::$idPeriods[$periodType])) {
            throw new \InvalidArgumentException("Invalid period type '$periodType'.");
        }

        try {

            $period = PeriodFactory::build($periodType, $dateRange);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException("Invalid date or date range specifier '$dateRange'", $code = 0, $ex);
        }

        $result = array();
        if ($periodType === 'range') {
            $result[] = $period->getDateStart();
            $result[] = $period->getDateEnd();
        } elseif ($period instanceof Range) {
            foreach ($period->getSubperiods() as $subperiod) {
                $result[] = $subperiod->getDateStart();
            }
        } else {
            $result[] = $period->getDateStart();
        }
        return $result;
    }

    private function getSegmentsToInvalidateFor(InputInterface $input, $idSites, OutputInterface $output)
    {
        $segments = $input->getOption('segment');
        $segments = array_map('trim', $segments);
        $segments = array_unique($segments);

        if (empty($segments)) {
            return array(null);
        }

        $result = array();
        foreach ($segments as $segmentOptionValue) {
            $segmentDefinition = $this->findSegment($segmentOptionValue, $idSites, $input, $output);
            if (empty($segmentDefinition)) {
                continue;
            }

            $result[] = new Segment($segmentDefinition, $idSites);
        }
        return $result;
    }

    private function findSegment($segmentOptionValue, $idSites, InputInterface $input, OutputInterface $output)
    {
        $logger = StaticContainer::get(LoggerInterface::class);

        $allSegments = $this->getAllSegments();
        foreach ($allSegments as $segment) {
            if (!empty($segment['enable_only_idsite'])
                && !in_array($segment['enable_only_idsite'], $idSites)
            ) {
                continue;
            }

            if ($segmentOptionValue == $segment['idsegment']) {
                $logger->debug("Matching '$segmentOptionValue' by idsegment with segment {segment}.", ['segment' => json_encode($segment)]);
                return $segment['definition'];
            }

            if (strtolower($segmentOptionValue) == strtolower($segment['name'])) {
                $logger->debug("Matching '$segmentOptionValue' by name with segment {segment}.", ['segment' => json_encode($segment)]);
                return $segment['definition'];
            }

            if ($segment['definition'] == $segmentOptionValue
                || $segment['definition'] == urldecode($segmentOptionValue)
            ) {
                $logger->debug("Matching '{value}' by definition with segment {segment}.", ['value' => $segmentOptionValue, 'segment' => json_encode($segment)]);
                return $segment['definition'];
            }
        }

        $logger->warning("'$segmentOptionValue' did not match any stored segment, but invalidating it anyway.");
        return $segmentOptionValue;
    }

    private function getAllSegments()
    {
        if ($this->allSegments === null) {
            $this->allSegments = API::getInstance()->getAll();
        }
        return $this->allSegments;
    }
}
