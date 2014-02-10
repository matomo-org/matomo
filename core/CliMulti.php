<?php
/**
 * Created by PhpStorm.
 * User: thomassteur
 * Date: 10.02.14
 * Time: 14:57
 */
namespace Piwik;

use Piwik\CliMulti\Output;

class CliMulti {

    /**
     * @var \Piwik\Lock[]
     */
    private $pids = array();

    /**
     * @var \Piwik\CliMulti\Output[]
     */
    private $outputs = array();

    public function request($piwikUrls)
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
            $appendix = $this->supportsAsync() ? ' &' : '';

            shell_exec($command . $appendix);

            $this->pids[]    = new Lock($pid);
            $this->outputs[] = new Output($output);
        }
    }

    private function buildCommand($aUrl, $additionalParams = array())
    {
        $url   = @parse_url($aUrl);
        $query = $url['query'];

        if (!empty($additionalParams)) {
            $query .= '&' . http_build_query($additionalParams);
        }

        $command = 'php ' . PIWIK_INCLUDE_PATH . '/core/CliMulti/run.php -- ' . escapeshellarg($query);

        return $command;
    }

    private function getResponse($urls)
    {
        $response = array();

        foreach ($this->outputs as $index => $output) {
            $url            = $urls[$index];
            $response[$url] = $output->get();
        }

        return $response;
    }

    private function isFinished()
    {
        foreach ($this->pids as $index => $pid) {
            if ($pid->isLocked() && !$this->outputs[$index]->exists()) {
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
            $pid->removeLock();
        }

        foreach ($this->outputs as $output) {
            $output->destroy();
        }
    }

}
