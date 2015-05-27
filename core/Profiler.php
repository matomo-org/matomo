<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use XHProfRuns_Default;

/**
 * Class Profiler helps with measuring memory, and profiling the database.
 * To enable set in your config.ini.php
 *   [Debug]
 *   enable_sql_profiler = 1
 *
 *   [log]
 *   log_writers[] = file
 *   log_level=debug
 *
 */
class Profiler
{
    /**
     * Whether xhprof has been setup or not.
     *
     * @var bool
     */
    private static $isXhprofSetup = false;

    /**
     * Returns memory usage
     *
     * @return string
     */
    public static function getMemoryUsage()
    {
        $memory = false;
        if (function_exists('xdebug_memory_usage')) {
            $memory = xdebug_memory_usage();
        } elseif (function_exists('memory_get_usage')) {
            $memory = memory_get_usage();
        }
        if ($memory === false) {
            return "Memory usage function not found.";
        }
        $usage = number_format(round($memory / 1024 / 1024, 2), 2);
        return "$usage Mb";
    }

    /**
     * Outputs SQL Profiling reports from Zend
     *
     * @throws \Exception
     */
    public static function displayDbProfileReport()
    {
        $profiler = Db::get()->getProfiler();

        if (!$profiler->getEnabled()) {
            // To display the profiler you should enable enable_sql_profiler on your config/config.ini.php file
            return;
        }

        $infoIndexedByQuery = array();
        foreach ($profiler->getQueryProfiles() as $query) {
            if (isset($infoIndexedByQuery[$query->getQuery()])) {
                $existing = $infoIndexedByQuery[$query->getQuery()];
            } else {
                $existing = array('count' => 0, 'sumTimeMs' => 0);
            }
            $new = array('count'     => $existing['count'] + 1,
                         'sumTimeMs' => $existing['count'] + $query->getElapsedSecs() * 1000);
            $infoIndexedByQuery[$query->getQuery()] = $new;
        }

        uasort($infoIndexedByQuery, 'self::sortTimeDesc');

        $str = '<hr /><strong>SQL Profiler</strong><hr /><strong>Summary</strong><br/>';
        $totalTime = $profiler->getTotalElapsedSecs();
        $queryCount = $profiler->getTotalNumQueries();
        $longestTime = 0;
        $longestQuery = null;
        foreach ($profiler->getQueryProfiles() as $query) {
            if ($query->getElapsedSecs() > $longestTime) {
                $longestTime = $query->getElapsedSecs();
                $longestQuery = $query->getQuery();
            }
        }
        $str .= 'Executed ' . $queryCount . ' queries in ' . round($totalTime, 3) . ' seconds';
        $str .= '(Average query length: ' . round($totalTime / $queryCount, 3) . ' seconds)';
        $str .= '<br />Queries per second: ' . round($queryCount / $totalTime, 1);
        $str .= '<br />Longest query length: ' . round($longestTime, 3) . " seconds (<code>$longestQuery</code>)";
        Log::debug($str);
        self::getSqlProfilingQueryBreakdownOutput($infoIndexedByQuery);
    }

    private static function maxSumMsFirst($a, $b)
    {
        return $a['sum_time_ms'] < $b['sum_time_ms'];
    }

    private static function sortTimeDesc($a, $b)
    {
        return $a['sumTimeMs'] < $b['sumTimeMs'];
    }

    /**
     * Print profiling report for the tracker
     *
     * @param \Piwik\Db $db Tracker database object (or null)
     */
    public static function displayDbTrackerProfile($db = null)
    {
        if (is_null($db)) {
            $db = Tracker::getDatabase();
        }
        $tableName = Common::prefixTable('log_profiling');

        $all = $db->fetchAll('SELECT * FROM ' . $tableName);
        if ($all === false) {
            return;
        }
        uasort($all, 'self::maxSumMsFirst');

        $infoIndexedByQuery = array();
        foreach ($all as $infoQuery) {
            $query = $infoQuery['query'];
            $count = $infoQuery['count'];
            $sum_time_ms = $infoQuery['sum_time_ms'];
            $infoIndexedByQuery[$query] = array('count' => $count, 'sumTimeMs' => $sum_time_ms);
        }
        self::getSqlProfilingQueryBreakdownOutput($infoIndexedByQuery);
    }

    /**
     * Print number of queries and elapsed time
     */
    public static function printQueryCount()
    {
        $totalTime = self::getDbElapsedSecs();
        $queryCount = Profiler::getQueryCount();
        if ($queryCount > 0) {
            Log::debug(sprintf("Total queries = %d (total sql time = %.2fs)", $queryCount, $totalTime));
        }
    }

    /**
     * Get total elapsed time (in seconds)
     *
     * @return int  elapsed time
     */
    public static function getDbElapsedSecs()
    {
        $profiler = Db::get()->getProfiler();
        return $profiler->getTotalElapsedSecs();
    }

    /**
     * Get total number of queries
     *
     * @return int  number of queries
     */
    public static function getQueryCount()
    {
        $profiler = Db::get()->getProfiler();
        return $profiler->getTotalNumQueries();
    }

    /**
     * Log a breakdown by query
     *
     * @param array $infoIndexedByQuery
     */
    private static function getSqlProfilingQueryBreakdownOutput($infoIndexedByQuery)
    {
        $output = '<hr /><strong>Breakdown by query</strong><br/>';
        foreach ($infoIndexedByQuery as $query => $queryInfo) {
            $timeMs = round($queryInfo['sumTimeMs'], 1);
            $count = $queryInfo['count'];
            $avgTimeString = '';
            if ($count > 1) {
                $avgTimeMs = $timeMs / $count;
                $avgTimeString = " (average = <b>" . round($avgTimeMs, 1) . "ms</b>)";
            }
            $query = preg_replace('/([\t\n\r ]+)/', ' ', $query);
            $output .= "Executed <b>$count</b> time" . ($count == 1 ? '' : 's') . " in <b>" . $timeMs . "ms</b> $avgTimeString <pre>\t$query</pre>";
        }
        Log::debug($output);
    }

    /**
     * Initializes Profiling via XHProf.
     * See: https://github.com/piwik/piwik/blob/master/tests/README.xhprof.md
     */
    public static function setupProfilerXHProf($mainRun = false, $setupDuringTracking = false)
    {
        if (!$setupDuringTracking
            && SettingsServer::isTrackerApiRequest()
        ) {
            // do not profile Tracker
            return;
        }

        if (self::$isXhprofSetup) {
            return;
        }

        $xhProfPath = PIWIK_INCLUDE_PATH . '/vendor/facebook/xhprof/extension/modules/xhprof.so';
        if (!file_exists($xhProfPath)) {
            throw new Exception("Cannot find xhprof, run 'composer install --dev' and build the extension.");
        }

        if (!function_exists('xhprof_enable')) {
            throw new Exception("Cannot find xhprof_enable, make sure to add 'extension=$xhProfPath' to your php.ini.");
        }

        $outputDir = ini_get("xhprof.output_dir");
        if (empty($outputDir)) {
            throw new Exception("The profiler output dir is not set. Add 'xhprof.output_dir=...' to your php.ini.");
        }
        if (!is_writable($outputDir)) {
            throw new Exception("The profiler output dir '" . ini_get("xhprof.output_dir") . "' should exist and be writable.");
        }

        if (!function_exists('xhprof_error')) {
            function xhprof_error($out)
            {
                echo substr($out, 0, 300) . '...';
            }
        }

        $currentGitBranch = SettingsPiwik::getCurrentGitBranch();
        $profilerNamespace = "piwik";
        if ($currentGitBranch != 'master') {
            $profilerNamespace .= "-" . $currentGitBranch;
        }

        if ($mainRun) {
            self::setProfilingRunIds(array());
        }

        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

        register_shutdown_function(function () use ($profilerNamespace, $mainRun) {
            $xhprofData = xhprof_disable();
            $xhprofRuns = new XHProfRuns_Default();
            $runId = $xhprofRuns->save_run($xhprofData, $profilerNamespace);

            if (empty($runId)) {
                die('could not write profiler run');
            }

            $runs = Profiler::getProfilingRunIds();
            array_unshift($runs, $runId);

            if ($mainRun) {
                Profiler::aggregateXhprofRuns($runs, $profilerNamespace, $saveTo = $runId);

                $baseUrlStored = SettingsPiwik::getPiwikUrl();

                $out = "\n\n";
                $baseUrl = "http://" . @$_SERVER['HTTP_HOST'] . "/" . @$_SERVER['REQUEST_URI'];
                if (strlen($baseUrlStored) > strlen($baseUrl)) {
                    $baseUrl = $baseUrlStored;
                }
                $baseUrl = $baseUrlStored . "vendor/facebook/xhprof/xhprof_html/?source=$profilerNamespace&run=$runId";

                $out .= "Profiler report is available at:\n";
                $out .= "<a href='$baseUrl'>$baseUrl</a>";
                $out .= "\n\n";

                if (Development::isEnabled()) {
                    $out .= "WARNING: Development mode is enabled. Many runtime optimizations are not applied in development mode. ";
                    $out .= "Unless you intend to profile Piwik in development mode, your profile may not be accurate.";
                    $out .= "\n\n";
                }

                echo $out;
            } else {
                Profiler::setProfilingRunIds($runs);
            }
        });

        self::$isXhprofSetup = true;
    }

    /**
     * Aggregates xhprof runs w/o normalizing (xhprof_aggregate_runs will always average data which
     * does not fit Piwik's use case).
     */
    public static function aggregateXhprofRuns($runIds, $profilerNamespace, $saveToRunId)
    {
        $xhprofRuns = new XHProfRuns_Default();

        $aggregatedData = array();

        foreach ($runIds as $runId) {
            $xhprofRunData = $xhprofRuns->get_run($runId, $profilerNamespace, $description);

            foreach ($xhprofRunData as $key => $data) {
                if (empty($aggregatedData[$key])) {
                    $aggregatedData[$key] = $data;
                } else {
                    // don't aggregate main() metrics since only the super run has the correct metrics for the entire run
                    if ($key == "main()") {
                        continue;
                    }

                    $aggregatedData[$key]["ct"] += $data["ct"]; // call count
                    $aggregatedData[$key]["wt"] += $data["wt"]; // incl. wall time
                    $aggregatedData[$key]["cpu"] += $data["cpu"]; // cpu time
                    $aggregatedData[$key]["mu"] += $data["mu"]; // memory usage
                    $aggregatedData[$key]["pmu"] = max($aggregatedData[$key]["pmu"], $data["pmu"]); // peak mem usage
                }
            }
        }

        $xhprofRuns->save_run($aggregatedData, $profilerNamespace, $saveToRunId);
    }

    public static function setProfilingRunIds($ids)
    {
        file_put_contents(self::getPathToXHProfRunIds(), json_encode($ids));
        @chmod(self::getPathToXHProfRunIds(), 0777);
    }

    public static function getProfilingRunIds()
    {
        $runIds = file_get_contents(self::getPathToXHProfRunIds());
        $array = json_decode($runIds, $assoc = true);
        if (!is_array($array)) {
            $array = array();
        }
        return $array;
    }

    /**
     * @return string
     */
    private static function getPathToXHProfRunIds()
    {
        return PIWIK_INCLUDE_PATH . '/tmp/cache/tests-xhprof-runs';
    }
}
