<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Commands;

use Piwik\DataAccess\RawLogUpdater;
use Piwik\Network\IPUtils;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\DataAccess\RawLogFetcher;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AttributeHistoricalDataWithLocations extends ConsoleCommand
{
    const DATES_RANGE_ARGUMENT = 'dates-range';

    const PERCENT_STEP_ARGUMENT = 'percent-step';

    const PERCENT_STEP_ARGUMENT_DEFAULT = 5;

    const PROVIDER_ARGUMENT = 'provider';

    const SEGMENT_LIMIT_OPTION = 'segmentLimit';

    const SEGMENT_LIMIT_OPTION_DEFAULT = 1000;

    /**
     * @var RawLogFetcher
     */
    protected $fetcher;

    /**
     * @var RawLogUpdater
     */
    protected $updater;

    /**
     * @var VisitorGeolocator
     */
    protected $locationFetcher;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var int
     */
    private $amountOfVisits;

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $percentStep;

    /**
     * @var array
     */
    private $percentConfirmed = array();

    /**
     * @var array
     */
    protected $logVisitFieldsToUpdate = array(
        'location_country'   => LocationProvider::COUNTRY_CODE_KEY,
        'location_region'    => LocationProvider::REGION_CODE_KEY,
        'location_city'      => LocationProvider::CITY_NAME_KEY,
        'location_latitude'  => LocationProvider::LATITUDE_KEY,
        'location_longitude' => LocationProvider::LONGITUDE_KEY
    );

    protected function configure()
    {
        $this->setName('usercountry:attribute');

        $this->addArgument(
            self::DATES_RANGE_ARGUMENT,
            InputArgument::REQUIRED,
            'Attribute visits from this dates.'
        );

        $this->addArgument(
            self::PERCENT_STEP_ARGUMENT,
            InputArgument::OPTIONAL,
            'How often command should write about current state.',
            self::PERCENT_STEP_ARGUMENT_DEFAULT
        );

        $this->addArgument(
            self::PROVIDER_ARGUMENT,
            InputArgument::OPTIONAL,
            'Provider id which should be used to attribute visits. If empty then Piwik will use default provider.'
        );

        $this->addOption(
            self::SEGMENT_LIMIT_OPTION,
            null,
            InputOption::VALUE_OPTIONAL,
            'Number of segments in single iteration.',
            self::SEGMENT_LIMIT_OPTION_DEFAULT
        );

        $this->fetcher = new RawLogFetcher();
        $this->updater = new RawLogUpdater();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $this->getDateArgument($input, self::DATES_RANGE_ARGUMENT, 0);
        $to = $this->getDateArgument($input, self::DATES_RANGE_ARGUMENT, 1);
        $segmentLimit = $input->getOption(self::SEGMENT_LIMIT_OPTION);

        $this->percentStep = $this->getPercentStep($input);
        $this->amountOfVisits = $this->fetcher->countVisitsWithDatesLimit($from, $to);

        if (!$from || !$to) {
            $output->writeln(
                sprintf('Invalid from [%s] or to [%s].',
                    $from, $to
                )
            );

            return 1;
        }

        $providerId = $input->getArgument(self::PROVIDER_ARGUMENT);
        $this->locationFetcher = new VisitorGeolocator(LocationProvider::getProviderById($providerId) ?: null);

        $output->writeln(
            sprintf('Re-attribution for date range: %s to %s. %d visits to process with provider "%s".',
                $from, $to, $this->amountOfVisits, $this->locationFetcher->getProvider()->getId()
            )
        );

        $this->start = time();
        $lastId = 0;
        $visitFields = array_merge(
            array('idvisit', 'location_ip'),
            array_keys($this->logVisitFieldsToUpdate)
        );

        do {
            $logs = $this->fetcher->getVisitsWithDatesLimit($from, $to, $visitFields, $lastId, $segmentLimit);
            if (!empty($logs)) {
                $lastId = $logs[count($logs) - 1]['idvisit'];
            }

            /**
             * @var array $row
             */
            foreach ($logs as $row) {
                if (!$this->isRowComplete($output, $row)) {
                    continue;
                }

                $idVisit = $row['idvisit'];
                $ip = IPUtils::binaryToStringIP($row['location_ip']);
                $values = $this->parseRowIntoValues($row, $ip);

                ++$this->processed;
                $this->trackProgress($output);

                if ($this->shouldSkipAttribution($output, $values, (string) $idVisit, $ip)) {
                    continue;
                }

                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln(
                        sprintf('Updating visit with idvisit = %s and ip = %s.', (string) $idVisit, $ip)
                    );
                }

                $this->updater->updateVisits($values, $idVisit);
                $this->updater->updateConversions($values, $idVisit);
            }

        } while (count($logs) == $segmentLimit);

        $output->writeln(sprintf('Completed in %d seconds.', time() - $this->start));

        return 0;
    }

    /**
     * @param InputInterface $input
     * @param string $name
     * @param int $index
     * @return string
     */
    protected function getDateArgument(InputInterface $input, $name, $index)
    {
        $option = explode(',', $input->getArgument($name));

        if (!isset($option[$index])) {
            return false;
        }

        return date('Y-m-d', strtotime($option[$index]));
    }

    /**
     * @param InputInterface $input
     * @return int
     */
    protected function getPercentStep(InputInterface $input)
    {
        $percentStep = $input->getArgument(self::PERCENT_STEP_ARGUMENT);

        if (!is_numeric($percentStep)) {
            return self::PERCENT_STEP_ARGUMENT_DEFAULT;
        }

        // Percent step should be between maximum percent value and minimum percent value (1-100)
        if ($percentStep > 99 || $percentStep < 1) {
            return 100;
        }

        return $percentStep;
    }

    /**
     * Parse row into proper values. (only if provider found difference)
     *
     * @param array $row
     * @param string $ip
     * @return array
     */
    protected function parseRowIntoValues(array $row, $ip)
    {
        $values = array();

        $location = $this->locationFetcher->getLocation(
            array(
                'ip' => $ip
            )
        );

        foreach ($this->logVisitFieldsToUpdate as $column => $locationKey) {
            if (!empty($location[$locationKey])) {
                if ($locationKey === LocationProvider::COUNTRY_CODE_KEY) {
                    if (strtolower($location[$locationKey]) != strtolower($row[$column])) {
                        $values[$column] = strtolower($location[$locationKey]);
                    }
                } else {
                    if ($location[$locationKey] != $row[$column]) {
                        $values[$column] = $location[$locationKey];
                    }
                }
            }
        }
        return $values;
    }

    /**
     * Print information about progress.
     * @param OutputInterface $output
     */
    protected function trackProgress(OutputInterface $output)
    {
        $percent = ceil($this->processed / $this->amountOfVisits * 100);

        if (!in_array($percent, $this->percentConfirmed, true) && $percent % $this->percentStep === 0) {
            $output->writeln(
                sprintf('%d%% processed. [in %d seconds]', $percent, time() - $this->start)
            );

            $this->percentConfirmed[] = $percent;
        }
    }

    /**
     * Validate if row contains required columns.
     * @param OutputInterface $output
     * @param array $row
     * @return bool
     */
    protected function isRowComplete(OutputInterface $output, array $row)
    {
        if (empty($row['idvisit'])) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln('Empty idvisit field. Skipping...');
            }

            return false;
        }

        if (empty($row['location_ip'])) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(
                    sprintf('Empty location_ip field for idvisit = %s. Skipping...', (string) $row['idvisit'])
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Check if given visit should be re attributed.
     * @param OutputInterface $output
     * @param array $values
     * @param string $idVisit
     * @param string $ip
     * @return bool
     */
    protected function shouldSkipAttribution(OutputInterface $output, array $values, $idVisit, $ip)
    {
        if (empty($values)) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(
                    sprintf('Visit with idvisit = %s and ip = %s is attributed. Skipping...', $idVisit, $ip)
                );
            }

            return true;
        }

        return false;
    }
} 
