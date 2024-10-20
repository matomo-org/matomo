<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Archiver\Request;
use Piwik\CliMulti\CliPhp;
use Piwik\CliMulti\Output;
use Piwik\CliMulti\OutputInterface;
use Piwik\CliMulti\Process;
use Piwik\CliMulti\ProcessSymfony;
use Piwik\CliMulti\StaticOutput;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugins\CoreConsole\FeatureFlags\CliMultiProcessSymfony;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Throwable;

/**
 * Class CliMulti.
 */
class CliMulti
{
    public const BASE_WAIT_TIME = 250000; // 250 * 1000 = 250ms

    /**
     * If set to true or false it will overwrite whether async is supported or not.
     *
     * @var null|bool
     */
    public $supportsAsync = null;

    /**
     * If set to true or false it will overwrite whether async using Symfony\Process is supported or not.
     *
     * Will only be checked if the default $supportsAsync is true.
     *
     * @var null|bool
     */
    public $supportsAsyncSymfony = null;

    /**
     * @var Process[]|ProcessSymfony[]
     */
    private $processes = [];

    /**
     * If set it will issue at most concurrentProcessesLimit requests
     * @var int
     */
    private $concurrentProcessesLimit = null;

    /**
     * @var OutputInterface[]
     */
    private $outputs = [];

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

    private $phpCliOptions = '';

    /**
     * @var callable
     */
    private $onProcessFinish = null;

    /**
     * @var Timer[]
     */
    protected $timers = [];

    protected $isTimingRequests = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->supportsAsync = $this->supportsAsync();
        $this->supportsAsyncSymfony = $this->supportsAsyncSymfony();

        $this->logger = $logger ?: new NullLogger();
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
        if ($this->isTimingRequests) {
            foreach ($piwikUrls as $url) {
                $this->timers[] = new Timer();
            }
        }

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
     * Forwards the given configuration options to the PHP cli command.
     * @param string $phpCliOptions  eg "-d memory_limit=8G -c=path/to/php.ini"
     */
    public function setPhpCliConfigurationOptions($phpCliOptions)
    {
        $this->phpCliOptions = (string) $phpCliOptions;
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
        $numUrls = count($piwikUrls);
        foreach ($piwikUrls as $index => $url) {
            $shouldStart = null;
            if ($url instanceof Request) {
                $shouldStart = $url->start();
            }

            $cmdId = $this->generateCommandId($url) . $index;

            if ($shouldStart === Request::ABORT) {
                // output is needed to ensure same order of url to response
                $output = new StaticOutput($cmdId);
                $output->write(serialize(array('aborted' => '1')));
                $this->outputs[] = $output;
            } else {
                $this->executeUrlCommand($cmdId, $url, $numUrls);
            }
        }
    }

    private function executeUrlCommand($cmdId, $url, $numUrls)
    {
        if ($this->supportsAsync) {
            if ($this->supportsAsyncSymfony) {
                $output = new StaticOutput($cmdId);
                $this->executeAsyncCliSymfony($url, $cmdId);
            } elseif ($numUrls === 1) {
                $output = new StaticOutput($cmdId);
                $this->executeSyncCli($url, $output);
            } else {
                $output = new Output($cmdId);
                $this->executeAsyncCli($url, $output, $cmdId);
            }
        } else {
            $output = new StaticOutput($cmdId);
            $this->executeNotAsyncHttp($url, $output);
        }

        $this->outputs[] = $output;
    }

    private function buildCommand($hostname, $query, $outputFileIfAsync, $doEsacpeArg = true)
    {
        $bin = $this->findPhpBinary();
        $superuserCommand = $this->runAsSuperUser ? "--superuser" : "";

        $append = '2>&1';
        if ($outputFileIfAsync) {
            $append = sprintf(' > %s 2>&1 &', $outputFileIfAsync);
        }

        if ($doEsacpeArg) {
            $hostname = escapeshellarg($hostname);
            $query = escapeshellarg($query);
        }

        return sprintf(
            '%s %s %s/console climulti:request -q --matomo-domain=%s %s %s %s',
            $bin,
            $this->phpCliOptions,
            PIWIK_INCLUDE_PATH,
            $hostname,
            $superuserCommand,
            $query,
            $append
        );
    }

    private function getResponse()
    {
        $response = array();

        foreach ($this->outputs as $output) {
            $content = $output->get();
            // Remove output that can be ignored in climulti . works around some worpdress setups where the hash bang may
            // be printed
            $search = '#!/usr/bin/env php';
            if (
                !empty($content)
                && is_string($content)
                && mb_substr(trim($content), 0, strlen($search)) === $search
            ) {
                $content = trim(mb_substr(trim($content), strlen($search)));
            }
            $response[] = $content;
        }

        return $response;
    }

    private function hasFinished(): bool
    {
        $hasFinished = true;

        foreach ($this->processes as $index => $process) {
            if ($process instanceof ProcessSymfony) {
                $processFinished = $this->hasFinishedProcessSymfony($process);
            } else {
                $processFinished = $this->hasFinishedProcess($process);
            }

            if (true === $processFinished) {
                // prevent from checking this process over and over again
                unset($this->processes[$index]);

                if ($this->isTimingRequests) {
                    $this->timers[$index]->finish();
                }

                if ($this->onProcessFinish) {
                    $pid = $process->getPid();
                    $onProcessFinish = $this->onProcessFinish;
                    $onProcessFinish($pid);
                }
            }

            $hasFinished = $hasFinished && $processFinished;
        }

        return $hasFinished;
    }

    private function hasFinishedProcess(Process $process): bool
    {
        $hasStarted = $process->hasStarted();

        if (!$hasStarted && 8 <= $process->getSecondsSinceCreation()) {
            // if process was created more than 8 seconds ago but still not started there must be something wrong.
            // ==> declare the process as finished
            $process->finishProcess();
            return true;
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

        return $process->hasFinished();
    }

    private function hasFinishedProcessSymfony(ProcessSymfony $process): bool
    {
        $finished = $process->isStarted() && !$process->isRunning();

        if ($finished) {
            foreach ($this->outputs as $output) {
                if ($output->getOutputId() !== $process->getCommandId()) {
                    continue;
                }

                $output->write($process->getOutput());
                break;
            }
        }

        return $finished;
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
        $supportsAsync = Process::isSupported() && !Common::isPhpCgiType() && $this->findPhpBinary();

        /**
         * Triggered to allow plugins to force the usage of async cli multi execution or to disable it.
         *
         * **Example**
         *
         *     public function supportsAsync(&$supportsAsync)
         *     {
         *         $supportsAsync = false; // do not allow async climulti execution
         *     }
         *
         * @param bool &$supportsAsync Whether async is supported or not.
         */
        Piwik::postEvent('CliMulti.supportsAsync', array(&$supportsAsync));

        return $supportsAsync;
    }

    /**
     * Returns whether Symfony\Process instead of the default Piwik\Process for
     * async cli multi execution.
     *
     * Requirements:
     * - supportsAsync has to be true
     * - feature flag "CliMultiProcessSymfony" has to be active
     * - proc_open has to be available
     *
     * Several of the regular supportsAsync requirements may be obsolete after
     * a complete switch has been done.
     *
     * @return bool
     */
    public function supportsAsyncSymfony(): bool
    {
        // When updating from an older version the required symfony component
        // may not be available. We have to verify that outside of the
        // CliMulti\ProcessSymfony wrapper because it extends the component.
        if (
            !$this->supportsAsync
            || Process::isMethodDisabled('proc_open')
            || !class_exists(\Symfony\Component\Process\Process::class)
        ) {
            return false;
        }

        // The required DI configuration may not be loaded during the update process.
        // This can happen for an upgrade from a version that did not yet contain
        // the feature flag plugin.
        try {
            $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);

            return $featureFlagManager->isFeatureActive(CliMultiProcessSymfony::class);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function findPhpBinary()
    {
        $cliPhp = new CliPhp();
        return $cliPhp->findPhpBinary();
    }

    private function cleanup()
    {
        foreach ($this->processes as $process) {
            if ($process instanceof ProcessSymfony) {
                $process->stop(0);
            } else {
                $process->finishProcess();
            }
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
        $query = UrlHelper::getQueryFromUrl($url, ['pid' => $cmdId, 'runid' => Common::getProcessId()]);
        $hostname = Url::getHost($checkIfTrusted = false);
        $command = $this->buildCommand($hostname, $query, $output->getPathToFile());

        $this->logger->debug(
            'Running command: {command} [method = {method}]',
            [
                'command' => $command,
                'method' => 'asyncCli',
            ]
        );

        shell_exec($command);
    }

    private function executeAsyncCliSymfony(string $url, string $cmdId): void
    {
        $url = $this->appendTestmodeParamToUrlIfNeeded($url);
        $query = UrlHelper::getQueryFromUrl($url, array());
        $hostname = Url::getHost($checkIfTrusted = false);
        $command = $this->buildCommand($hostname, $query, '', true);

        $this->logger->debug(
            'Running command: {command} [method = {method}]',
            [
                'command' => $command,
                'method' => 'asyncCliSymfony',
            ]
        );

        // Prepending "exec" is required to send signals to the process
        // Not using array notation because $command can contain complex parameters
        if ('\\' !== DIRECTORY_SEPARATOR) {
            $command = 'exec ' . $command;
        }

        $process = ProcessSymfony::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->start();
        $process->setCommandId($cmdId);

        $this->processes[] = $process;
    }

    private function executeSyncCli($url, StaticOutput $output)
    {
        $url = $this->appendTestmodeParamToUrlIfNeeded($url);
        $query = UrlHelper::getQueryFromUrl($url, array());
        $hostname = Url::getHost($checkIfTrusted = false);
        $command = $this->buildCommand($hostname, $query, '', true);

        $this->logger->debug(
            'Running command: {command} [method = {method}]',
            [
                'command' => $command,
                'method' => 'syncCli',
            ]
        );

        $result = shell_exec($command);
        if ($result) {
            $result = trim($result);
        }
        $output->write($result);
    }

    private function executeNotAsyncHttp($url, StaticOutput $output)
    {
        $piwikUrl = $this->urlToPiwik ?: SettingsPiwik::getPiwikUrl();
        if (empty($piwikUrl)) {
            $piwikUrl = 'http://' . Url::getHost() . '/';
        }

        $url = $piwikUrl . $url;
        if (Config::getInstance()->General['force_ssl'] == 1) {
            $url = str_replace("http://", "https://", $url);
        }

        $requestBody = null;
        if ($this->runAsSuperUser) {
            $tokenAuth = self::getSuperUserTokenAuth();

            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }

            $requestBody = 'token_auth=' . $tokenAuth;
        }

        try {
            $this->logger->debug("Execute HTTP API request: "  . $url);
            $response = Http::sendHttpRequestBy('curl', $url, $timeout = 0, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $this->acceptInvalidSSLCertificate, false, false, 'POST', null, null, $requestBody, [], $forcePost = true);
            $output->write($response);
        } catch (\Exception $e) {
            $message = "Got invalid response from API request: $url. ";

            if (isset($response) && empty($response)) {
                $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
            } else {
                $message .= "Response was '" . $e->getMessage() . "'";
            }

            $output->write($message);

            $this->logger->debug($message, ['exception' => $e]);
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

        $startTime = time();
        do {
            $elapsed = time() - $startTime;
            $timeToWait = $this->getTimeToWaitBeforeNextCheck($elapsed);

            if (count($this->processes)) {
                usleep($timeToWait);
            }
        } while (!$this->hasFinished());

        $results = $this->getResponse();
        $this->cleanup();

        self::cleanupNotRemovedFiles();

        return $results;
    }

    private static function getSuperUserTokenAuth()
    {
        return Piwik::requestTemporarySystemAuthToken('CliMultiNonAsyncArchive', 36);
    }

    public function setUrlToPiwik($urlToPiwik)
    {
        $this->urlToPiwik = $urlToPiwik;
    }

    public function onProcessFinish(callable $callback)
    {
        $this->onProcessFinish = $callback;
    }

    // every minute that passes adds an extra 100ms to the wait time. so 5 minutes results in 500ms extra, 20mins results in 2s extra.
    private function getTimeToWaitBeforeNextCheck($elapsed)
    {
        $minutes = floor($elapsed / 60);
        return self::BASE_WAIT_TIME + $minutes * 100000; // 100 * 1000 = 100ms
    }

    public static function isCliMultiRequest()
    {
        return Common::getRequestVar('pid', false) !== false;
    }

    public function timeRequests()
    {
        $this->timers = [];
        $this->isTimingRequests = true;
    }

    public function getTimers()
    {
        return $this->timers;
    }
}
