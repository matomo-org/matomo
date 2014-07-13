<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CliMulti;

use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Url;
use Piwik\UrlHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RequestCommand
 */
class RequestCommand extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('climulti:request');
        $this->setDescription('Parses and executes the given query. See Piwik\CliMulti. Intended only for system usage.');
        $this->addArgument('url-query', null, InputOption::VALUE_REQUIRED, 'Piwik URL query string, for instance: "module=API&method=API.getPiwikVersion&token_auth=123456789"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initHostAndQueryString($input);

        if ($this->isTestModeEnabled()) {
            Config::getInstance()->setTestEnvironment();
            $indexFile = '/tests/PHPUnit/proxy/index.php';
        } else {
            $indexFile = '/index.php';
        }

        if (!empty($_GET['pid'])) {
            $process = new Process($_GET['pid']);

            if ($process->hasFinished()) {
                return;
            }

            $process->startProcess();
        }

        require_once PIWIK_INCLUDE_PATH . $indexFile;

        if (!empty($process)) {
            $process->finishProcess();
        }
    }

    private function isTestModeEnabled()
    {
        return !empty($_GET['testmode']);
    }

    /**
     * @param InputInterface $input
     */
    protected function initHostAndQueryString(InputInterface $input)
    {
        $_GET = array();

        $hostname = $input->getOption('piwik-domain');
        Url::setHost($hostname);

        $query = $input->getArgument('url-query');
        $query = UrlHelper::getArrayFromQueryString($query);
        foreach ($query as $name => $value) {
            $_GET[$name] = $value;
        }
    }

}