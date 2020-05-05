<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Commands;

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
    const FORCE_OPTION = 'force';

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
        $this->setDescription("Re-attribute existing raw data (visits & conversions) with geolocated location data, using the specified or configured location provider.");
        $this->addArgument(self::DATES_RANGE_ARGUMENT, InputArgument::REQUIRED, 'Attribute visits in this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOption(self::PERCENT_STEP_ARGUMENT, null, InputArgument::OPTIONAL,
            'How often to display the command progress. A status update will be printed after N percent of visits are processed, '
            . 'where N is the value of this option.', self::PERCENT_STEP_ARGUMENT_DEFAULT);
        $this->addOption(self::PROVIDER_ARGUMENT, null, InputOption::VALUE_REQUIRED, 'Provider id which should be used to attribute visits. If empty then'
            . ' Piwik will use the currently configured provider. If no provider is configured, the default provider is used.');
        $this->addOption(self::SEGMENT_LIMIT_OPTION, null, InputOption::VALUE_OPTIONAL, 'Number of visits to process at a time.', self::SEGMENT_LIMIT_OPTION_DEFAULT);
        $this->addOption(self::FORCE_OPTION, null, InputOption::VALUE_NONE, "Force geolocation, even if the requested provider does not appear to work. It is not "
                                                                          . "recommended to use this option.");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($from, $to) = $this->getDateRangeToAttribute($input);

        $this->visitorGeolocator = $this->createGeolocator($output, $input);

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
        $self = $this;
        $this->visitorGeolocator->reattributeVisitLogs($from, $to, $idSite = null, $segmentLimit, function () use ($output, $self) {
            $self->onVisitProcessed($output);
        });
    }

    /**
     * @param InputInterface $input
     * @return int
     */
    protected function getPercentStep(InputInterface $input)
    {
        $percentStep = $input->getOption(self::PERCENT_STEP_ARGUMENT);

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
    public function onVisitProcessed(OutputInterface $output)
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

    private function createGeolocator(OutputInterface $output, InputInterface $input)
    {
        $providerId = $input->getOption(self::PROVIDER_ARGUMENT);
        $geolocator = new VisitorGeolocator(LocationProvider::getProviderById($providerId) ?: null);

        $usedProvider = $geolocator->getProvider();
        if (!$usedProvider->isAvailable()) {
            throw new \InvalidArgumentException("The provider '$providerId' is not currently available, please make sure it is configured correctly.");
        }

        $isWorkingOrErrorMessage = $usedProvider->isWorking();
        if ($isWorkingOrErrorMessage !== true) {
            $errorMessage = "The provider '$providerId' does not appear to be working correctly. Details: $isWorkingOrErrorMessage";

            $forceGeolocation = $input->getOption(self::FORCE_OPTION);
            if ($forceGeolocation) {
                $output->writeln("<error>$errorMessage</error>");
                $output->writeln("<comment>Ignoring location provider issue, geolocating anyway due to --force option.</comment>");
            } else {
                throw new \InvalidArgumentException($errorMessage);
            }
        }

        return $geolocator;
    }
}
