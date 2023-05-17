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
use Piwik\Tracker\GoalManager;

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
        $this->addOptionalValueOption('dates', null, 'Calculate for conversions in this date range. Eg, 2012-01-01,2013-01-01', null);
        $this->addOptionalValueOption('last-n', null, 'Calculate just the last n conversions', 0);
        $this->addOptionalValueOption('idsite', null,
            'Calculate for conversions belonging to the site with this ID. Comma separated list of website id. Eg, 1, 2, 3, etc. By default conversions from all sites are calculated.', null);
        $this->addOptionalValueOption('idgoal', null,'Calculate conversions for this goal. A comma separated list of goal ids can be used only if a single site is specified. Eg, 1, 2, 3, etc. By default conversions for all goals are calculated.', null);
        $this->addOptionalValueOption('force-recalc', null, 'Recalculate for conversions which already have a pages before value', 0);
    }

    protected function doExecute(): int
    {
        $dates = $this->getInput()->getOption('dates');
        $lastN = $this->getInput()->getOption('last-n');
        $forceRecalc = $this->getInput()->getOption('force-recalc');
        $idSite = $this->getSitesToCalculate();
        $idGoal = $this->getGoalsToCalculate();

        if (!$lastN && !$dates) {
            throw new \InvalidArgumentException("No date range or last N option supplied. Calculating pages before for all conversions by default is not allowed, you must specify a date range using the --dates option or a last N count using the --last-n option");
        }

        if ($lastN && $dates) {
            throw new \InvalidArgumentException("The last N option cannot be used with a date range, please choose just one of these options");
        }

        if (!is_numeric($lastN)) {
            throw new \InvalidArgumentException("The last N option must be a number");
        }

        $from = null;
        $to = null;
        if (!empty($dates)) {
            [$from, $to] = $this->getDateRangeToCalculate($dates);
        }

        $output  = $this->getOutput();

        $output->writeln(sprintf(
            "<info>Preparing to calculate the pages before metric for %s conversions belonging to %s %sfor %s.</info>",
            $lastN ? "the last ".$lastN : 'all',
            $idSite ? "website $idSite" : "ALL websites",
                    !empty($dates) ? "between " . $from . " and " . $to . " " : '',
                    $idGoal ? "goal id $idGoal" : "ALL goals"
        ));

        $timer = new Timer();

        try {
            $conversionsCalculated = $this->pagesBeforeCalculator->calculateFor($from, $to, $lastN, $idSite, $idGoal, $forceRecalc, function () use ($output) {
                $output->write('.');
            });
        } catch (\Exception $ex) {
            $output->writeln("");
            throw $ex;
        }

        $this->writeSuccessMessage(["Successfully calculated the pages before metric for $conversionsCalculated conversions. <comment>" . $timer . "</comment>"]);

        return self::SUCCESS;
    }

    /**
     * Static method to calculate conversion for today and yesterday, for all sites and goals.
     * Called by the migration updater
     *
     * @return void
     */
    public static function calculateYesterdayAndToday(): void
    {
        $pagesBeforeCalculator = StaticContainer::get('Piwik\Plugins\Goals\PagesBeforeCalculator');
        $pagesBeforeCalculator->calculateFor(Date::factory('yesterday')->getDatetime(),
                                             Date::factory('today')->getEndOfDay()->getDatetime());
    }

    /**
     * Validate dates parameter
     *
     * @param string $dates
     * @return Date[]
     */
    private function getDateRangeToCalculate(string $dates): ?array
    {
        $parts = explode(',', $dates);
        $parts = array_map('trim', $parts);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Invalid date range supplied: $dates");
        }

        [$start, $end] = $parts;

        try {
            /** @var Date[] $dateObjects */
            $dateObjects = [Date::factory($start), Date::factory($end)->getEndOfDay()];
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
     * @return string|null
     */
    private function getSitesToCalculate(): ?string
    {
        $idSite = $this->getInput()->getOption('idsite');

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
     * @return string|null
     */
    private function getGoalsToCalculate(): ?string
    {
        $idGoal = $this->getInput()->getOption('idgoal');

        if(is_null($idGoal)) {
            return null;
        }

        // Only allow the goals parameter to be used if a single site is specified
        $idSite = $this->getInput()->getOption('idsite');
        if (!is_numeric($idSite) || strpos($idSite, ',') !== false) {
            throw new \InvalidArgumentException("The goals parameter can only be used when a single website is specified using the idsite parameter", $code = 0);
        }

        $goals = explode(',',  $idGoal);
        $goalsModel = new GoalsModel();

        foreach ($goals as $id) {
            // validate the goal id
            if (!$goalsModel->doesGoalExist($id, $idSite) && $id != GoalManager::IDGOAL_ORDER) {
                throw new \InvalidArgumentException("Invalid goal ID: $id", $code = 0);
            }
        }

        return $idGoal;
    }

}
