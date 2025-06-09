<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\Goals\Model as GoalsModel;
use Piwik\Plugins\SitesManager\Model as SitesModel;
use Piwik\Site;
use Piwik\Timer;
use Piwik\Tracker\GoalManager;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Command to calculate the pages viewed before conversions and populate the log_conversion.pages_before field
 */
class CalculateConversionPages extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:calculate-conversion-pages');
        $this->setDescription('Calculate the pages before metric for historic conversions');
        $this->addOptionalValueOption('dates', null, 'Calculate for conversions in this date range. Eg, 2012-01-01,2013-01-01', null);
        $this->addOptionalValueOption('last-n', null, 'Calculate just the last n conversions', 0);
        $this->addOptionalValueOption(
            'idsite',
            null,
            'Calculate for conversions belonging to the site with this ID. Comma separated list of website id. Eg, 1, 2, 3, etc. By default conversions from all sites are calculated.',
            null
        );
        $this->addOptionalValueOption('idgoal', null, 'Calculate conversions for this goal. A comma separated list of goal ids can be used only if a single site is specified. Eg, 1, 2, 3, etc. By default conversions for all goals are calculated.', null);
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
            $lastN ? "the last " . $lastN : 'all',
            $idSite ? "website $idSite" : "ALL websites",
            !empty($dates) ? "between " . $from . " and " . $to . " " : '',
            $idGoal ? "goal id $idGoal" : "ALL goals"
        ));

        $timer = new Timer();

        $queries = $this->getQueries($from, $to, $lastN, $idSite, $idGoal, $forceRecalc);

        $totalCalculated = 0;
        foreach ($queries as $query) {
            try {
                $result = Db::query($query['sql'], $query['bind']);
            } catch (\Exception $ex) {
                $output->writeln("Exception executing query " . $query['sql'] . " with parameters " . json_encode($query['bind']));
                throw $ex;
            }

            $calcCount = $result->rowCount();
            $totalCalculated += $calcCount;
            $output->write(".");
        }

        $this->writeSuccessMessage("Successfully calculated the pages before metric for $totalCalculated conversions. <comment>{$timer}</comment>");

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
        $migration = StaticContainer::get(MigrationFactory::class);

        $queries = self::getQueries(
            Date::factory('yesterday')->getDatetime(),
            Date::factory('today')->getEndOfDay()->getDatetime()
        );

        $migrations = [];
        foreach ($queries as $query) {
            $migrations[] = $migration->db->boundSql($query['sql'], $query['bind']);
        }

        $updater = StaticContainer::get(Updater::class);
        $updater->executeMigrations(__FILE__, $migrations);
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

        if (is_null($idSite)) {
            return null;
        }

        $sites = explode(',', $idSite);
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

        if (is_null($idGoal)) {
            return null;
        }

        // Only allow the goals parameter to be used if a single site is specified
        $idSite = $this->getInput()->getOption('idsite');
        if (!is_numeric($idSite) || strpos($idSite, ',') !== false) {
            throw new \InvalidArgumentException("The goals parameter can only be used when a single website is specified using the idsite parameter", $code = 0);
        }

        $goals = explode(',', $idGoal);
        $goalsModel = new GoalsModel();

        foreach ($goals as $id) {
            // validate the goal id
            if (!$goalsModel->doesGoalExist($id, $idSite) && $id != GoalManager::IDGOAL_ORDER) {
                throw new \InvalidArgumentException("Invalid goal ID: $id", $code = 0);
            }
        }

        return $idGoal;
    }

    /**
     * Generates the queries to calculate the 'pages before' metric for conversions within the specified date range,
     * belonging to the specified site (if any) and specific goals (only if a single site is specified).
     *
     * @param string|null   $startDatetime A datetime string. Visits that occur at this time or after are deleted. If not supplied,
     *                                     visits from the beginning of time are deleted.
     * @param string|null   $endDatetime A datetime string. Visits that occur before this time are deleted. If not supplied,
     *                                   visits from the end of time are deleted.
     * @param int|null      $lastN  Calculate the last N conversions, should not be used with a date range
     * @param string|null   $idSite The site for which to calculate, or list of comma separated sites
     * @param string|null   $idGoal The goal for which to calculate, or list of comma separated idgoals (only if single site)
     * @param bool          $forceRecalc If enabled then values will be recalculated for conversions that already have a
     *                                   'pages before' value. By default only conversions with a null value will be calculated.
     *
     * @return array An array of queries and bind arrays   [['sql' => QUERY1, 'bind' => [PARAM1 => VALUE], ...]
     */
    private static function getQueries(
        ?string $startDatetime,
        ?string $endDatetime,
        ?int $lastN = null,
        ?string $idSite = null,
        ?string $idGoal = null,
        ?bool $forceRecalc = false
    ): array {
        // Sites
        if ($idSite === null) {
            // All sites
            $sitesModel = new SitesModel();
            $sites = $sitesModel->getSitesId();
        } else {
            // Specific sites
            $sites = explode(',', $idSite);
        }

        if ($lastN) {
            // Since MySQL doesn't support multi-table updates with a LIMIT clause we will find the exact date time of
            // the lastN record and use that as a date range start with the current date time as the date range end
            /** @noinspection SqlResolve SqlUnused */
            $sql = "
                    SELECT MIN(s.t) FROM (
                    SELECT c.server_time AS t
                    FROM " . Common::prefixTable('log_conversion') . " c                                 
                    ";

            $where = '';
            if (!$forceRecalc) {
                $where .= " AND c.pageviews_before IS NULL";
            }

            $bind = [];
            if ($idGoal !== null) {
                $where .= ' AND c.idgoal = ? ';
                $bind[] = $idGoal;
            }
            if ($idSite !== null) {
                $where .= ' AND c.idsite = ? ';
                $bind[] = $idSite;
            }

            if ($where !== '') {
                $sql .= ' WHERE ' . ltrim($where, 'AND ');
            }

            $sql .= " ORDER BY c.server_time DESC LIMIT " . $lastN . ") AS s";

            $result = Db::fetchOne($sql, $bind);

            if (!$result) {
                return [];
            }

            $startDatetime = $result;
            $endDatetime = Date::factory('now')->getDatetime();
        }

        // When querying for visit actions that contributed to the conversion we can use a cut off 24hrs before the
        // start of the conversion date range as visits cannot last more than 24hrs, this limits the number of rows
        // addressed by the subquery
        $startDateTimeForActions = Date::factory($startDatetime)->subDay(1)->getDatetime();

        $queries = [];
        foreach ($sites as $site) {
            $timezone = Site::getTimezoneFor($site);

            if ($idGoal === null) {
                // All goals
                $gids = Db::fetchAll("SELECT idgoal FROM " . Common::prefixTable('goal') . "
                                        WHERE idsite = ? AND deleted = 0", [$site]);
                $goals = array_column($gids, 'idgoal');

                // Include ecommerce orders if enabled for the site
                if (Site::isEcommerceEnabledFor($site)) {
                    $goals[] = GoalManager::IDGOAL_ORDER;
                }
            } else {
                // Specific goals
                $goals = explode(',', $idGoal);
            }

            foreach ($goals as $goal) {
                $where = '';
                if (!$forceRecalc) {
                     $where .= " AND c.pageviews_before IS NULL";
                }

                $conversionsStartDate = Date::factory($startDatetime, $timezone)->getDateTime();
                $conversionsEndDate = Date::factory($endDatetime, $timezone)->getDateTime();

                $bind = [
                    $site,
                    Date::factory($startDateTimeForActions, $timezone)->getDateTime(),
                    $conversionsEndDate,
                    $site,
                    $goal,
                    $conversionsStartDate,
                    $conversionsEndDate,
                    $site,
                    $goal,
                    $conversionsStartDate,
                    $conversionsEndDate,
                ];

                $sql = "                                
                UPDATE " . Common::prefixTable('log_conversion') . " c
                LEFT JOIN (                
                    SELECT c.idvisit, c.idgoal, COUNT(a.idvisit) AS pagesbefore, c.idlink_va, c.server_time
                    FROM " . Common::prefixTable('log_conversion') . " c
                    LEFT JOIN (
                        SELECT va.idvisit, va.server_time
                        FROM " . Common::prefixTable('log_link_visit_action') . " va
                        INNER JOIN " . Common::prefixTable('log_action') . " a ON a.idaction = va.idaction_url
                        WHERE a.type = 1
                        AND va.idsite = ?
                        AND va.server_time >= ?
                        AND va.server_time <= ?
                        ORDER BY NULL
                    ) AS a ON a.idvisit = c.idvisit AND a.server_time <= c.server_time
                    WHERE c.idsite = ?
                      AND c.idgoal = ?
                      AND c.server_time >= ?
                      AND c.server_time <= ?
                      " . $where . "                      
                    GROUP BY a.idvisit
                    ORDER BY NULL
                ) AS s ON s.idvisit = c.idvisit AND s.server_time <= c.server_time                
                SET c.pageviews_before = s.pagesbefore                
                WHERE c.idsite = ? 
                  AND c.idgoal = ?       
                  AND c.server_time >= ?
                  AND c.server_time <= ?     
                " . $where;

                $queries[] = ['sql' => $sql, 'bind' => $bind];
            }
        }

        return $queries;
    }
}
