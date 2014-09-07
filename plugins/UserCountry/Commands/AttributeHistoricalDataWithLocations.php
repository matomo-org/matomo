<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Commands;

use PDORow;
use Piwik\IP;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UserCountry\LocationFetcher;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\Repository\Mysql\LogsRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\Plugins\UserCountry\Repository\LogsRepository as LogsRepositoryInterface;

class AttributeHistoricalDataWithLocations extends ConsoleCommand
{
    const FROM_ARGUMENT = 'from';

    const TO_ARGUMENT = 'to';

    const PERCENT_STEP_OPTION = 'percent-step';

    const PERCENT_STEP_OPTION_SHORT = 'ps';

    /**
     * @var LogsRepositoryInterface
     */
    protected $repository;

    /**
     * @var LocationFetcher
     */
    protected $locationFetcher;

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
            self::FROM_ARGUMENT,
            InputArgument::REQUIRED,
            'Attribute visits from this date.'
        );
        $this->addArgument(
            self::TO_ARGUMENT,
            InputArgument::REQUIRED,
            'Attribute visits to this date.'
        );
        $this->addOption(
            self::PERCENT_STEP_OPTION,
            self::PERCENT_STEP_OPTION_SHORT,
            InputOption::VALUE_OPTIONAL,
            'How often command should write about current state.',
            5
        );

        $this->repository = new LogsRepository();
        $this->locationFetcher = new LocationFetcher();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $this->getDateArgument($input, self::FROM_ARGUMENT);
        $to = $this->getDateArgument($input, self::TO_ARGUMENT);
        $percentStep = $this->getPercentStep($input);

        $logsCursor = $this->repository->getVisitsWithDatesLimit(
            $from, $to,
            array_keys($this->logVisitFieldsToUpdate)
        );
        $amountOfVisits = $this->repository->countVisitsWithDatesLimit($from, $to);

        $output->writeln(
            sprintf('Re-attribution for date range: %s to %s. %d visits to process.',
                $from, $to, $amountOfVisits
            )
        );

        $start = time();
        $processed = 0;

        $percentConfirmed = array();

        /**
         * @var PDORow $row
         */
        while ($row = $logsCursor->fetch()) {
            if (empty($row->idvisit)) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln('Empty idvisit field. Skipping...');
                }

                continue;
            }

            if (empty($row->location_ip)) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln(
                        sprintf('Empty location_ip field for idvisit = %s. Skipping...', (string) $row->idvisit)
                    );
                }

                continue;
            }

            $ip = IP::N2P($row->location_ip);
            $location = $this->locationFetcher->getLocation(array('ip' => $ip));

            $columnsToSet = array();
            $bind = array();
            foreach ($this->logVisitFieldsToUpdate as $column => $locationKey) {
                if (!empty($location[$locationKey]) && $location[$locationKey] != $row->{$column}) {
                    $columnsToSet[] = $column;
                    $bind[] = $location[$locationKey];
                }
            }

            ++$processed;
            $percent = ceil($processed / $amountOfVisits * 100);

            if (!in_array($percent, $percentConfirmed, true) && $percent % $percentStep === 0) {
                $output->writeln(
                    sprintf('%d%% processed. [in %d seconds]', $percent, time() - $start)
                );

                $percentConfirmed[] = $percent;
            }

            if (empty($columnsToSet)) {
                if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                    $output->writeln(
                        sprintf('Visit with idvisit = %s and ip = %s is attributed. Skipping...', (string) $row->idvisit, $ip)
                    );
                }

                continue;
            }

            $bind[] = $row->idvisit;

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $output->writeln(
                    sprintf('Updating visit with idvisit = %s and ip = %s.', (string) $row->idvisit, $ip)
                );
            }

            $this->repository->updateVisits($columnsToSet, $bind);
            $this->repository->updateConversions($columnsToSet, $bind);
        }

        $output->writeln(sprintf('Completed in %d seconds.', time() - $start));
    }

    /**
     * @param InputInterface $input
     * @param string $name
     * @return string
     */
    protected function getDateArgument(InputInterface $input, $name)
    {
        return date('Y-m-d', strtotime($input->getArgument($name)));
    }

    /**
     * @param InputInterface $input
     * @return int
     */
    protected function getPercentStep(InputInterface $input)
    {
        $percentStep = $input->getOption(self::PERCENT_STEP_OPTION);

        if (!is_numeric($percentStep)) {
            return 5;
        }

        if ($percentStep > 99) {
            return 100;
        }

        if ($percentStep < 1) {
            return 100;
        }

        return $percentStep;
    }
} 
