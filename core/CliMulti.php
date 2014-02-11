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

    /**
     * It will request all given URLs in parallel (async) using the CLI and wait until all requests are finished.
     *
     *
     * @param string[]  $piwikUrls   An array of urls, for instance:
     *                               array('/index.php?module=API', '/?module=API', 'http://www.example.com?module=API')
     * @return array The response of each URL in the same order as the URLs. The array can contain null values in case
     *               there was a problem with a request, for instance if the process died unexpected.
     */
    public function request(array $piwikUrls)
    {
        $this->start($piwikUrls);

        do {
            usleep(100 * 1000);
        } while (!$this->isFinished());

        $results = $this->getResponse($piwikUrls);
        $this->cleanup();

        return $results;
    }

    private function start($piwikUrls)
    {
        foreach ($piwikUrls as $index => $url) {
            $cmdId    = $this->generateCommandId($url);
            $pid      = $cmdId . $index . '_cli_multi_pid';
            $outputId = $cmdId . $index . '_cli_multi_output';

            $this->processes[] = new Process($pid);
            $this->outputs[]   = new Output($outputId);

            $query   = $this->getQueryFromUrl($url, array('outputId' => $outputId, 'pid' => $pid));
            $command = $this->buildCommand($query);

            shell_exec($command);

            if (!$this->supportsAsync()) {
                end($this->processes)->finishProcess();
            }
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

    private function buildCommand($query)
    {
        $appendix = $this->supportsAsync() ? ' > /dev/null 2>&1 &' : '';

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
        foreach ($this->processes as $index => $process) {
            if (!$process->hasStarted()) {
                return false;
            }

            if ($process->isRunning() && !$this->outputs[$index]->exists()) {
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
        if (is_bool($this->supportsAsync)) {
            return $this->supportsAsync;
        }

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

}
