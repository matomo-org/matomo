<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik;

use Piwik\Archiver\Request;
use Piwik\CliMulti\CliPhp;
use Piwik\CliMulti\Output;
use Piwik\CliMulti\Process;
use Piwik\Container\StaticContainer;

/**
 * Class CliMulti.
 */
class CliMulti
{

    /**
     * If set to true or false it will overwrite whether async is supported or not.
     *
     * @var null|bool
     */
    public $supportsAsync = null;

    /**
     * @var Process[]
     */
    private $processes = array();

    /**
     * If set it will issue at most concurrentProcessesLimit requests
     * @var int
     */
    private $concurrentProcessesLimit = null;

    /**
     * @var Output[]
     */
    private $outputs = array();

    private $acceptInvalidSSLCertificate = false;

    /**
     * @var bool
     */
    private $runAsSuperUser = false;

    /**
     * Only used when doing synchronous curl requests.
     *
     * @var string
     */
    private $urlToPiwik = null;

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
     *                               **Make sure query parameter values are properly encoded in the URLs.**
     *
     * @return array The response of each URL in the same order as the URLs. The array can contain null values in case
     *               there was a problem with a request, for instance if the process died unexpected.
     */
    public function request(array $piwikUrls)
    {
        $chunks = array($piwikUrls);
        if ($this->concurrentProcessesLimit) {
            $chunks = array_chunk($piwikUrls, $this->concurrentProcessesLimit);
        }

        $results = array();
        foreach ($chunks as $urlsChunk) {
            $results = array_merge($results, $this->requestUrls($urlsChunk));
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

    public function runAsSuperUser($runAsSuperUser = true)
    {
        $this->runAsSuperUser = $runAsSuperUser;
    }

    private function start($piwikUrls)
    {
        foreach ($piwikUrls as $index => $url) {
            if ($url instanceof Request) {
                $url->start();
            }

            $cmdId = $this->generateCommandId($url) . $index;
            $this->executeUrlCommand($cmdId, $url);
        }
    }

    private function executeUrlCommand($cmdId, $url)
    {
        $output = new Output($cmdId);

        if ($this->supportsAsync) {
            $this->executeAsyncCli($url, $output, $cmdId);
        } else {
            $this->executeNotAsyncHttp($url, $output);
        }

        $this->outputs[] = $output;
    }

    private function buildCommand($hostname, $query, $outputFile)
    {
        $bin = $this->findPhpBinary();
        $superuserCommand = $this->runAsSuperUser ? "--superuser" : "";

        return sprintf('%s %s/console climulti:request -q --piwik-domain=%s %s %s > %s 2>&1 &',
                       $bin, PIWIK_INCLUDE_PATH, escapeshellarg($hostname), $superuserCommand, escapeshellarg($query), $outputFile);
    }

    private function getResponse()
    {
        $response = array();

        foreach ($this->outputs as $output) {
            $response[] = $output->get();
        }

        return $response;
    }

    private function hasFinished()
    {
        foreach ($this->processes as $index => $process) {
            $hasStarted = $process->hasStarted();

            if (!$hasStarted && 8 <= $process->getSecondsSinceCreation()) {
                // if process was created more than 8 seconds ago but still not started there must be something wrong.
                // ==> declare the process as finished
                $process->finishProcess();
                continue;
            } elseif (!$hasStarted) {
                return false;
            }

            if ($process->isRunning()) {
                return false;
            }

            $pid = $process->getPid();
            foreach ($this->outputs as $output) {
                if ($output->getOutputId() === $pid && $output->isAbnormal()) {
                    $process->finishProcess();
                    return true;
                }
            }

            if ($process->hasFinished()) {
                // prevent from checking this process over and over again
                unset($this->processes[$index]);
            }
        }

        return true;
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

                if ($timeLastModified !== false && $timeOneWeekAgo > $timeLastModified) {
                    unlink($file);
                }
            }
        }
    }

    public static function getTmpPath()
    {
        return StaticContainer::get('path.tmp') . '/climulti';
    }

    private function executeAsyncCli($url, Output $output, $cmdId)
    {
        $this->processes[] = new Process($cmdId);

        $url = $this->appendTestmodeParamToUrlIfNeeded($url);
        $query = UrlHelper::getQueryFromUrl($url, array('pid' => $cmdId));
        $hostname = Url::getHost($checkIfTrusted = false);
        $command = $this->buildCommand($hostname, $query, $output->getPathToFile());

        Log::debug($command);
        shell_exec($command);
    }

    private function executeNotAsyncHttp($url, Output $output)
    {
        $piwikUrl = $this->urlToPiwik ?: SettingsPiwik::getPiwikUrl();
        if (empty($piwikUrl)) {
            $piwikUrl = 'http://' . Url::getHost() . '/';
        }

        $url = $piwikUrl . $url;
        if (Config::getInstance()->General['force_ssl'] == 1) {
            $url = str_replace("http://", "https://", $url);
        }

        if ($this->runAsSuperUser) {
            $tokenAuths = self::getSuperUserTokenAuths();
            $tokenAuth = reset($tokenAuths);

            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }

            $url .= 'token_auth=' . $tokenAuth;
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
        $isTestMode = defined('PIWIK_TEST_MODE');

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
    private function requestUrls(array $piwikUrls)
    {
        $this->start($piwikUrls);

        do {
            usleep(100000); // 100 * 1000 = 100ms
        } while (!$this->hasFinished());

        $results = $this->getResponse($piwikUrls);
        $this->cleanup();

        self::cleanupNotRemovedFiles();

        return $results;
    }

    private static function getSuperUserTokenAuths()
    {
        $tokens = array();

        /**
         * Used to be in CronArchive, moved to CliMulti.
         *
         * @ignore
         */
        Piwik::postEvent('CronArchive.getTokenAuth', array(&$tokens));

        return $tokens;
    }

    public function setUrlToPiwik($urlToPiwik)
    {
        $this->urlToPiwik = $urlToPiwik;
    }
}
