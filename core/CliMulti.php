<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik;

use Piwik\CliMulti\CliPhp;
use Piwik\CliMulti\Output;
use Piwik\CliMulti\Process;

/**
 * Class CliMulti.
 */
class CliMulti {

    /**
     * If set to true or false it will overwrite whether async is supported or not.
     *
     * @var null|bool
     */
    public $supportsAsync = null;

    /**
     * @var \Piwik\CliMulti\Process[]
     */
    private $processes = array();

    /**
     * If set it will issue at most concurrentProcessesLimit requests
     * @var int
     */
    private $concurrentProcessesLimit = null;

    /**
     * @var \Piwik\CliMulti\Output[]
     */
    private $outputs = array();

    private $acceptInvalidSSLCertificate = false;

    /**
     * The request URLs for each running process.
     *
     * @var string[]
     */
    private $requestUrlsForProcesses = array();

    public function __construct()
    {
        $this->supportsAsync = $this->supportsAsync();
    }

    /**
     * It will request all given URLs in parallel (async) using the CLI and wait until all requests are finished.
     * If multi cli is not supported (eg windows) it will initiate an HTTP request instead (not async).
     *
     * @param string[]  $piwikUrls   An array of urls, for instance:
     *
     *                               `array('http://www.example.com/piwik?module=API...')`
     *
     *                               If you plan on scheduling more requests in the `$onRequestsFinishedCallback`
     *                               callback, the index of each item in this array must be globally unique. That is
     *                               to say, if an ID of `0` was used before,
     *
     *                               **Make sure query parameter values are properly encoded in the URLs.**
     * @param callback  $afterPollCallback Callback executed after each polling check. Accepts an array of responses.
     *                                     Can be used to schedule more requests.
     * @return array The response of each URL in the same order as the URLs. The array can contain null values in case
     *               there was a problem with a request, for instance if the process died unexpected.
     */
    public function request(array $piwikUrls, $afterPollCallback = null)
    {
        $chunks = array($piwikUrls);
        if ($this->concurrentProcessesLimit) {
            $chunks = array_chunk($piwikUrls, $this->concurrentProcessesLimit, $preserveKeys = true);
        }

        $results = array();
        foreach($chunks as $urlsChunk) {
            $results = array_merge($results, $this->requestUrls($urlsChunk, $afterPollCallback));
        }

        return $results;
    }

    /**
     * Ok, this sounds weird. Why should we care about ssl certificates when we are in CLI mode? It is needed for
     * our simple fallback mode for Windows where we initiate HTTP requests instead of CLI.
     * @param $acceptInvalidSSLCertificate
     */
    public function setAcceptInvalidSSLCertificate($acceptInvalidSSLCertificate)
    {
        $this->acceptInvalidSSLCertificate = $acceptInvalidSSLCertificate;
    }

    /**
     * @param $limit int Maximum count of requests to issue in parallel
     */
    public function setConcurrentProcessesLimit($limit)
    {
        $this->concurrentProcessesLimit = $limit;
    }

    /**
     * Returns the number of unused processes based on the configured maximum concurrent process
     * count.
     *
     * @return int
     */
    public function getUnusedProcessCount()
    {
        return max($this->concurrentProcessesLimit - count($this->processes), 0);
    }

    /**
     * Starts a set of URLs concurrently. This can bypass the concurrent process limit.
     *
     * The index of the `$piwikUrls` elements must be unique for the entire CliMulti run.
     *
     * @param string[] $piwikUrls
     */
    public function start($piwikUrls)
    {
        foreach ($piwikUrls as $id => $url) {
            $this->requestUrlsForProcesses[$id] = $url;

            $cmdId  = $this->generateCommandId($url) . count($this->requestUrlsForProcesses);
            $this->executeUrlCommand($cmdId, $url, $id);
        }
    }

    private function executeUrlCommand($cmdId, $url, $urlId)
    {
        $output = new Output($cmdId);

        if ($this->supportsAsync) {
            $this->executeAsyncCli($url, $output, $cmdId, $urlId);
        } else {
            $this->executeNotAsyncHttp($url, $output);
        }

        $this->outputs[$urlId] = $output;
    }

    private function buildCommand($hostname, $query, $outputFile)
    {
        $bin = $this->findPhpBinary();

        return sprintf('%s %s/console climulti:request --piwik-domain=%s %s > %s 2>&1 &',
                       $bin, PIWIK_INCLUDE_PATH, escapeshellarg($hostname), escapeshellarg($query), $outputFile);
    }

    private function getResponse()
    {
        $response = array();

        foreach ($this->outputs as $output) {
            $response[] = $output->get();
        }

        return $response;
    }

    private function getRunningProcessCount()
    {
        return count($this->processes);
    }

    private function getFinishedOutputs()
    {
        $results = array();

        foreach ($this->processes as $id => $process) {
            $hasStarted = $process->hasStarted();

            if (!$hasStarted && 8 <= $process->getSecondsSinceCreation()) {
                // if process was created more than 8 seconds ago but still not started there must be something wrong.
                // ==> declare the process as finished
                $process->finishProcess();
                continue;

            } elseif (!$hasStarted) {
                continue;
            }

            if ($process->isRunning()) {
                continue;
            }

            $pid = $process->getPid();
            foreach ($this->outputs as $output) {
                if ($output->getOutputId() === $pid && $output->isAbnormal()) {
                    $process->finishProcess();
                    return true;
                }
            }

            if ($process->hasFinished()) {
                $results[$id] = $this->outputs[$id]->get();

                // prevent from checking this process over and over again
                unset($this->processes[$id]);
            }
        }

        return $results;
    }

    private function generateCommandId($command)
    {
        return substr(Common::hash($command . microtime(true) . rand(0, 99999)), 0, 100);
    }

    /**
     * What is missing under windows? Detection whether a process is still running in Process::isProcessStillRunning
     * and how to send a process into background in start()
     */
    public function supportsAsync()
    {
        return Process::isSupported() && !Common::isPhpCgiType() && $this->findPhpBinary();
    }

    private function findPhpBinary()
    {
        $cliPhp = new CliPhp();
        return $cliPhp->findPhpBinary();
    }

    private function cleanup()
    {
        foreach ($this->processes as $pid) {
            $pid->finishProcess();
        }

        foreach ($this->outputs as $output) {
            $output->destroy();
        }

        $this->processes = array();
        $this->outputs   = array();
    }

    /**
     * Remove files older than one week. They should be cleaned up automatically after each request but for whatever
     * reason there can be always some files left.
     */
    public static function cleanupNotRemovedFiles()
    {
        $timeOneWeekAgo = strtotime('-1 week');

        $files = _glob(self::getTmpPath() . '/*');
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                $timeLastModified = filemtime($file);

                if ($timeLastModified !== FALSE && $timeOneWeekAgo > $timeLastModified) {
                    unlink($file);
                }
            }
        }
    }

    public static function getTmpPath()
    {
        $dir = PIWIK_INCLUDE_PATH . '/tmp/climulti';
        return SettingsPiwik::rewriteTmpPathWithInstanceId($dir);
    }

    private function executeAsyncCli($url, Output $output, $cmdId, $urlId)
    {
        $this->processes[$urlId] = new Process($cmdId);

        $url      = $this->appendTestmodeParamToUrlIfNeeded($url);
        $query    = UrlHelper::getQueryFromUrl($url, array('pid' => $cmdId));
        $hostname = UrlHelper::getHostFromUrl($url);
        if (empty($hostname)) {
            $hostname = Url::getCurrentHost();
        }

        $command  = $this->buildCommand($hostname, $query, $output->getPathToFile());

        Log::debug($command);
        shell_exec($command);
    }

    private function executeNotAsyncHttp($url, Output $output)
    {
        if (@parse_url($url, PHP_URL_HOST) == '') {
            $url = SettingsPiwik::getPiwikUrl() . $url;
        }

        try {
            Log::debug("Execute HTTP API request: "  . $url);
            $response = Http::sendHttpRequestBy('curl', $url, $timeout = 0, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $this->acceptInvalidSSLCertificate);
            $output->write($response);
        } catch (\Exception $e) {
            $message = "Got invalid response from API request: $url. ";

            if (isset($response) && empty($response)) {
                $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
            } else {
                $message .= "Response was '" . $e->getMessage() . "'";
            }

            $output->write($message);

            Log::debug($e);
        }
    }

    private function appendTestmodeParamToUrlIfNeeded($url)
    {
        $isTestMode = $url && false !== strpos($url, 'tests/PHPUnit/proxy');

        if ($isTestMode && false === strpos($url, '?')) {
            $url .= "?testmode=1";
        } elseif ($isTestMode) {
            $url .= "&testmode=1";
        }

        return $url;
    }

    /**
     * @param array $piwikUrls
     * @return array
     */
    private function requestUrls(array $piwikUrls, $afterPollCallback)
    {
        $this->start($piwikUrls);

        do {
            usleep(100000); // 100 * 1000 = 100ms

            $finishedRequests = $this->getFinishedOutputs();
            if (!empty($afterPollCallback)) {
                $afterPollCallback($finishedRequests);
            }
        } while ($this->getRunningProcessCount() > 0);

        $results = $this->getResponse($piwikUrls);
        $this->cleanup();

        self::cleanupNotRemovedFiles();

        return $results;
    }
}
