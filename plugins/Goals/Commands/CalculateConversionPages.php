<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Goals\Model as GoalsModel;
use Piwik\Plugins\Goals\PagesBeforeCalculator;
use Piwik\Site;
use Piwik\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to calculate the pages viewed before conversions and populate the log_conversion.pages_before field
 */
class CalculateConversionPages extends ConsoleCommand
{

    /**
     * @var PagesBeforeCalculator
     */
    private $pagesBeforeCalculator;

    public function __construct(PagesBeforeCalculator $pagesBeforeCalculator = null)
    {
        parent::__construct();
        $this->pagesBeforeCalculator = $pagesBeforeCalculator ?: StaticContainer::get('Piwik\Plugins\Goals\PagesBeforeCalculator');
    }

    protected function configure()
    {
        $this->setName('core:calculate-conversion-pages');
        $this->setDescription('Calculate the pages before metric for historic conversions');
        $this->addOption('dates', null, InputOption::VALUE_REQUIRED, 'Caclulate for conversions in this date range. Eg, 2012-01-01,2013-01-01');
        $this->addOption('idsite', null, InputOption::VALUE_OPTIONAL,
            'Calculate for conversions belonging to the site with this ID. Comma separated list of website id. Eg, 1, 2, 3, etc. By default conversions from all sites are calculated.');
        $this->addOption('idgoal', null, InputOption::VALUE_OPTIONAL, 'Calculate conversions for this goal. A comma separated list of goal ids can be used only if a single site is specified. Eg, 1, 2, 3, etc. By default conversions for all goals are calculated.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        list($from, $to) = $this->getDateRangeToCalculate($input);
        $idSite = $this->getSitesToCalculate($input);
        $idGoal = $this->getGoalsToCalculate($input);

        $output->writeln( sprintf(
                "<info>Preparing to calculate the pages before metric for all conversions belonging to %s between $from and $to for %s.</info>",
                $idSite ? "website $idSite" : "ALL websites",
                        $idGoal ? "goal id $idGoal" : "ALL goals"
        ));

        $timer = new Timer();

        try {
            $conversionsCalculated = $this->pagesBeforeCalculator->calculateFor($from, $to, $idSite, $idGoal, function () use ($output) {
                $output->write('.');
            });
        } catch (\Exception $ex) {
            $output->writeln("");
            throw $ex;
        }

        $this->writeSuccessMessage($output, ["Successfully calculated the pages before metric for $conversionsCalculated conversions. <comment>" . $timer . "</comment>"]);

        return self::SUCCESS;
    }

    /**
     * Validate dates parameter
     *
     * @param InputInterface $input
     * @return Date[]
     */
    private function getDateRangeToCalculate(InputInterface $input): array
    {
        $dates = $input->getOption('dates');
        if (empty($dates)) {
            throw new \InvalidArgumentException("No date range supplied in --dates option. Calculating pages before for all conversions by default is not allowed, you must specify a date range.");
        }

        $parts = explode(',', $dates);
        $parts = array_map('trim', $parts);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates");
        }

        list($start, $end) = $parts;

        try {
            /** @var Date[] $dateObjects */
            $dateObjects = array(Date::factory($start), Date::factory($end)->getEndOfDay());
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates (" . $ex->getMessage() . ")", $code = 0, $ex);
        }

        if ($dateObjects[0]->getTimestamp() > $dateObjects[1]->getTimestamp()) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates (first date is older than the last date)");
        }

        $dateObjects = [$dateObjects[0]->getDatetime(), $dateObjects[1]->getDatetime()];

        return $dateObjects;
    }

    /**
     * Validate the sites parameter
     *
     * @param InputInterface $input
     *
     * @return string|null
     */
    private function getSitesToCalculate(InputInterface $input): ?string
    {
        $idSite = $input->getOption('idsite');

        if(is_null($idSite)) {
            return null;
        }

        $sites = explode(',',  $idSite);
        foreach ($sites as $id) {
            // validate the site ID
            try {
                new Site($id);
            } catch (\Exception $ex) {
                throw new \InvalidArgumentException("Invalid site ID: $id", $code = 0, $ex);
            }
        }

        return $idSite;
    }

    /**
     * Validate the goals parameter
     *
     * @param InputInterface $input
     *
     * @return string|null
     */
    private function getGoalsToCalculate(InputInterface $input): ?string
    {
        $idGoal = $input->getOption('idgoal');

        if(is_null($idGoal)) {
            return null;
        }

        // Only allow the goals parameter to be used if a single site is specified
        $idSite = $input->getOption('idsite');
        if (!is_numeric($idSite) || strpos($idSite, ',') !== false) {
            throw new \InvalidArgumentException("The goals parameter can only be used when a single website is specified using the idsite parameter", $code = 0);
        }

        $goals = explode(',',  $idGoal);
        $goalsModel = new GoalsModel();

        foreach ($goals as $id) {
            // validate the goal id
            if (!$goalsModel->doesGoalExist($id, $idSite)) {
                throw new \InvalidArgumentException("Invalid goal ID: $id", $code = 0);
            }
        }

        return $idGoal;
    }

}
