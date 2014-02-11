<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik;

use Piwik\CliMulti\Pid;
use Piwik\CliMulti\Output;

class CliMulti {

    /**
     * @var \Piwik\CliMulti\Pid[]
     */
    private $pids = array();

    /**
     * @var \Piwik\CliMulti\Output[]
     */
    private $outputs = array();

    public function request(array $piwikUrls)
    {
        $this->start($piwikUrls);

        while (!$this->isFinished()) {
            sleep(1);
        }

        $results = $this->getResponse($piwikUrls);
        $this->cleanup();

        return $results;
    }

    private function start($piwikUrls)
    {
        foreach ($piwikUrls as $index => $url) {
            $cmdId  = $this->generateCmdId($url);
            $pid    = $cmdId . $index . '_cli_multi_pid';
            $output = $cmdId . $index . '_cli_multi_output';

            $params   = array('output' => $output, 'pid' => $pid);
            $command  = $this->buildCommand($url, $params);
            $appendix = $this->supportsAsync() ? ' > /dev/null 2>&1 &' : '';

            shell_exec($command . $appendix);

            $this->pids[]    = new Pid($pid);
            $this->outputs[] = new Output($output);
        }
    }

    private function buildCommand($aUrl, $additionalParams = array())
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

        $command = 'php ' . PIWIK_INCLUDE_PATH . '/core/CliMulti/run.php -- ' . escapeshellarg($query);

        return $command;
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
        foreach ($this->pids as $index => $pid) {
            if (!$pid->hasStarted()) {
                return false;
            }

            if ($pid->isRunning() && !$this->outputs[$index]->exists()) {
                return false;
            }
        }

        return true;
    }

    private function generateCmdId($command)
    {
        return md5($command . microtime(true) . rand(0, 99999));
    }

    private function supportsAsync()
    {
        return !SettingsServer::isWindows();
    }

    private function cleanup()
    {
        foreach ($this->pids as $pid) {
            $pid->finishProcess();
        }

        foreach ($this->outputs as $output) {
            $output->destroy();
        }

        $this->pids    = array();
        $this->outputs = array();
    }

}
