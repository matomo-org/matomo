<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik;

use Piwik\CliMulti\Process;
use Piwik\CliMulti\Output;

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
     * @var \Piwik\CliMulti\Output[]
     */
    private $outputs = array();

    private $acceptInvalidSSLCertificate = false;

    public function __construct()
    {
        $this->supportsAsync = $this->supportsAsync();
    }

    /**
     * It will request all given URLs in parallel (async) using the CLI and wait until all requests are finished.
     * If multi cli is not supported (eg windows) it will initiate an HTTP request instead (not async).
     *
     * @param string[]  $piwikUrls   An array of urls, for instance:
     *                               array('http://www.example.com/piwik?module=API...')
     * @return array The response of each URL in the same order as the URLs. The array can contain null values in case
     *               there was a problem with a request, for instance if the process died unexpected.
     */
    public function request(array $piwikUrls)
    {
        $this->start($piwikUrls);

        do {
            usleep(100000); // 100 * 1000 = 100ms
        } while ($this->supportsAsync && !$this->isFinished());

        $results = $this->getResponse($piwikUrls);
        $this->cleanup();

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

    private function start($piwikUrls)
    {
        foreach ($piwikUrls as $index => $url) {
            $cmdId  = $this->generateCommandId($url) . $index;
            $output = new Output($cmdId);

            if ($this->supportsAsync) {
                $this->processes[] = new Process($cmdId);

                $query   = $this->getQueryFromUrl($url, array('pid' => $cmdId));
                $command = $this->buildCommand($query, $output->getPathToFile());
                shell_exec($command);
            } else {
                $response = Http::sendHttpRequestBy('curl', $url, $timeout = 0, $userAgent = null, $destinationPath = null, $file = null, $followDepth = 0, $acceptLanguage = false, $this->acceptInvalidSSLCertificate);
                $output->write($response);
            }

            $this->outputs[] = $output;
        }
    }

    private function getQueryFromUrl($aUrl, array $additionalParams)
    {
        $url   = @parse_url($aUrl);
        $query = '';

        if (!empty($url['query'])) {
            $query .= $url['query'];
        }

        if (!empty($additionalParams)) {
            if (!empty($query)) {
                $query .= '&';
            }

            $query .= http_build_query($additionalParams);
        }

        return $query;
    }

    private function buildCommand($query, $outputFile)
    {
        $appendix = $this->supportsAsync ? ' > ' . $outputFile . ' 2>&1 &' : '';

        return PIWIK_INCLUDE_PATH . '/console climulti:request ' . escapeshellarg($query) . $appendix;
    }

    private function getResponse()
    {
        $response = array();

        foreach ($this->outputs as $output) {
            $response[] = $output->get();
        }

        return $response;
    }

    private function isFinished()
    {
        foreach ($this->processes as $process) {
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
        }

        return true;
    }

    private function generateCommandId($command)
    {
        return md5($command . microtime(true) . rand(0, 99999));
    }

    /**
     * What is missing under windows? Detection whether a process is still running in Process::isProcessStillRunning
     * and how to send a process into background in start()
     */
    private function supportsAsync()
    {
        return !SettingsServer::isWindows() && Process::isSupported();
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

    public static function cleanupAllNotRemovedFiles()
    {
        foreach (_glob(PIWIK_INCLUDE_PATH . '/tmp/climulti/*') as $file) {
            $timeLastModified = filemtime($file);

            if (time() - $timeLastModified > 1000) {
                unlink($file);
            }
        }
    }
}
