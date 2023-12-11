<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\CliMulti;

use Piwik\Application\Environment;
use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log;
use Piwik\Option;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Url;
use Piwik\UrlHelper;

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
        $this->addRequiredArgument('url-query', 'Matomo URL query string, for instance: "module=API&method=API.getPiwikVersion&token_auth=123456789"');
        $this->addNoValueOption('superuser', null, 'If supplied, runs the code as superuser.');
    }

    protected function doExecute(): int
    {
        $this->recreateContainerWithWebEnvironment();

        $this->initHostAndQueryString();

        if ($this->isTestModeEnabled()) {
            $indexFile = '/tests/PHPUnit/proxy/';

            $this->resetDatabase();
        } else {
            $indexFile = '/';
        }

        $indexFile .= 'index.php';

        if (!empty($_GET['pid'])) {
            $process = new Process($_GET['pid']);

            if ($process->hasFinished()) {
                return self::SUCCESS;
            }

            $process->startProcess();
        }

        if ($this->getInput()->getOption('superuser')) {
            StaticContainer::addDefinitions(array(
                'observers.global' => \Piwik\DI::add(array(
                    array('Environment.bootstrapped', \Piwik\DI::value(function () {
                        Access::getInstance()->setSuperUserAccess(true);
                    }))
                )),
            ));
        }

        require_once PIWIK_INCLUDE_PATH . $indexFile;

        while (ob_get_level()) {
            echo ob_get_clean();
        }
        
        if (!empty($process)) {
            $process->finishProcess();
        }

        return self::SUCCESS;
    }

    private function isTestModeEnabled()
    {
        return !empty($_GET['testmode']);
    }

    protected function initHostAndQueryString()
    {
        $_GET = array();

        $hostname = $this->getInput()->getOption('matomo-domain');
        Url::setHost($hostname);

        $query = $this->getInput()->getArgument('url-query');
        $_SERVER['QUERY_STRING'] = $query;

        $query = UrlHelper::getArrayFromQueryString($query); // NOTE: this method can create the StaticContainer now
        foreach ($query as $name => $value) {
            $_GET[$name] = urldecode($value);
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
