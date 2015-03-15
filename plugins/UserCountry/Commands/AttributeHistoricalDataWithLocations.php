<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Commands;

use Piwik\Network\IPUtils;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\DataAccess\RawLogDao;
use Piwik\Timer;
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
    const SEGMENT_LIMIT_OPTION = 'segment-limit';
    const SEGMENT_LIMIT_OPTION_DEFAULT = 1000;

    /**
     * @var RawLogDao
     */
    protected $dao;

    /**
     * @var VisitorGeolocator
     */
    protected $visitorGeolocator;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @var int
     */
    private $amountOfVisits;

    /**
     * @var Timer
     */
    private $timer;

    /**
     * @var int
     */
    private $percentStep;

    /**
     * @var int
     */
    private $processedPercent = 0;

    public function __construct(RawLogDao $dao = null)
    {
        parent::__construct();

        $this->dao = $dao ?: new RawLogDao();
    }

    protected function configure()
    {
        $this->setName('usercountry:attribute');

        $this->addArgument(self::DATES_RANGE_ARGUMENT, InputArgument::REQUIRED, 'Attribute visits in this date range. Eg, 2012-01-01,2013-01-01');
        $this->addArgument(self::PERCENT_STEP_ARGUMENT, InputArgument::OPTIONAL,
            'How often to display the command progress. A status update will be printed after N percent of visits are processed, '
            . 'where N is the value of this option.', self::PERCENT_STEP_ARGUMENT_DEFAULT);
        $this->addArgument(self::PROVIDER_ARGUMENT, InputArgument::OPTIONAL, 'Provider id which should be used to attribute visits. If empty then'
            . ' Piwik will use the currently configured provider. If no provider is configured, the default provider is used.');
        $this->addOption(self::SEGMENT_LIMIT_OPTION, null, InputOption::VALUE_OPTIONAL, 'Number of visits to process at a time.', self::SEGMENT_LIMIT_OPTION_DEFAULT);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($from, $to) = $this->getDateRangeToAttribute($input);

        $this->visitorGeolocator = $this->createGeolocator($input);

        $this->percentStep = $this->getPercentStep($input);
        $this->amountOfVisits = $this->dao->countVisitsWithDatesLimit($from, $to);

        $output->writeln(
            sprintf('Re-attribution for date range: %s to %s. %d visits to process with provider "%s".',
                $from, $to, $this->amountOfVisits, $this->visitorGeolocator->getProvider()->getId())
        );

        $this->timer = new Timer();

        $this->processSpecifiedLogsInChunks($output, $from, $to, $input->getOption(self::SEGMENT_LIMIT_OPTION));

        $output->writeln("Completed. <comment>" . $this->timer->__toString() . "</comment>");

        return 0;
    }

    protected function processSpecifiedLogsInChunks(OutputInterface $output, $from, $to, $segmentLimit)
    {
        $visitFieldsToSelect = array_merge(array('idvisit', 'location_ip'), array_keys(VisitorGeolocator::$logVisitFieldsToUpdate));

        $lastId = 0;
        do {
            $logs = $this->dao->getVisitsWithDatesLimit($from, $to, $visitFieldsToSelect, $lastId, $segmentLimit);
            if (!empty($logs)) {
                $lastId = $logs[count($logs) - 1]['idvisit'];

                $this->reattributeVisitLogs($output, $logs);
            }
        } while (count($logs) == $segmentLimit);
    }

    protected function reattributeVisitLogs(OutputInterface $output, $logRows)
    {
        foreach ($logRows as $row) {
            if (!$this->isRowComplete($output, $row)) {
                continue;
            }

            $updatedValues = $this->visitorGeolocator->attributeExistingVisit($row);

            $this->onVisitProcessed($output);

            $idVisit = $row['idvisit'];
            if (empty($updatedValues)) {
                $this->writeIfVerbose($output, 'Nothing to update for idvisit = ' . $idVisit . '. Existing location info is same as geolocated.');
            } else {
                $this->writeIfVerbose($output, 'Updating visit with idvisit = ' . $idVisit . '.');
            }
        }
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
     * Print information about progress.
     * @param OutputInterface $output
     */
    protected function onVisitProcessed(OutputInterface $output)
    {
        ++$this->processed;

        $percent = ceil($this->processed / $this->amountOfVisits * 100);

        if ($percent > $this->processedPercent
            && $percent % $this->percentStep === 0
        ) {
            $output->writeln(sprintf('%d%% processed. <comment>%s</comment>', $percent, $this->timer->__toString()));

            $this->processedPercent = $percent;
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
            $this->writeIfVerbose($output, 'Empty idvisit field. Skipping...');

            return false;
        }

        if (empty($row['location_ip'])) {
            $this->writeIfVerbose($output, sprintf('Empty location_ip field for idvisit = %s. Skipping...', (string) $row['idvisit']));

            return false;
        }

        return true;
    }

    private function getDateRangeToAttribute(InputInterface $input)
    {
        $dateRangeString = $input->getArgument(self::DATES_RANGE_ARGUMENT);

        $dates = explode(',', $dateRangeString);
        $dates = array_map(array('Piwik\Date', 'factory'), $dates);

        if (count($dates) != 2) {
            throw new \InvalidArgumentException('Invalid date range supplied: ' . $dateRangeString);
        }

        return $dates;
    }

    private function createGeolocator(InputInterface $input)
    {
        $providerId = $input->getArgument(self::PROVIDER_ARGUMENT);
        return new VisitorGeolocator(LocationProvider::getProviderById($providerId) ?: null);
    }

    private function writeIfVerbose(OutputInterface $output, $message)
    {
        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln($message);
        }
    }
}