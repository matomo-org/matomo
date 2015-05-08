<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CliMulti;

use Piwik\Application\Environment;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log;
use Piwik\Option;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Tests\Framework\Mock\TestConfig;
use Piwik\Url;
use Piwik\UrlHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RequestCommand
 */
class RequestCommand extends ConsoleCommand
{
    /**
     * @var Environment
     */
    private $environment;

    protected function configure()
    {
        $this->setName('climulti:request');
        $this->setDescription('Parses and executes the given query. See Piwik\CliMulti. Intended only for system usage.');
        $this->addArgument('url-query', InputArgument::REQUIRED, 'Piwik URL query string, for instance: "module=API&method=API.getPiwikVersion&token_auth=123456789"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->recreateContainerWithWebEnvironment();

        $this->initHostAndQueryString($input);

        if ($this->isTestModeEnabled()) {
            Config::setSingletonInstance(new TestConfig());
            $indexFile = '/tests/PHPUnit/proxy/';

            $this->resetDatabase();
        } else {
            $indexFile = '/';
        }

        $indexFile .= 'index.php';

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

    /**
     * We will be simulating an HTTP request here (by including index.php).
     *
     * To avoid weird side-effects (e.g. the logging output messing up the HTTP response on the CLI output)
     * we need to recreate the container with the default environment instead of the CLI environment.
     */
    private function recreateContainerWithWebEnvironment()
    {
        StaticContainer::clearContainer();
        Log::unsetInstance();

        $this->environment = new Environment(null);
        $this->environment->init();
    }

    private function resetDatabase()
    {
        Option::clearCache();
        Db::destroyDatabaseObject();
    }
}
