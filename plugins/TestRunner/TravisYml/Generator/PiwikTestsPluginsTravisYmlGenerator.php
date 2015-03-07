<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\TravisYml\Generator;

use Exception;
use Piwik\Plugins\TestRunner\TravisYml\Generator;

class PiwikTestsPluginsTravisYmlGenerator extends Generator
{
    /**
     * @var string
     */
    private $repoPath;

    /**
     * @param string $repoPath
     * @param string[] $options
     */
    public function __construct($repoPath, $options)
    {
        parent::__construct($options);

        $this->repoPath = $repoPath;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->setGenerationMode('piwik-tests-plugins');
        $this->view->setTravisShScriptLocation("./travis.sh");
        $this->view->setPathToCustomTravisStepsFiles($this->getTestsRepoPath() . "/travis");
        $this->view->setTravisShCwd("\$TRAVIS_BUILD_DIR");
    }

    public function getTravisYmlOutputPath()
    {
        return $this->repoPath . '/.travis.yml';
    }

    private function getTestsRepoPath()
    {
        return dirname($this->getTravisYmlOutputPath());
    }

    protected function getOptionsForSelfReferentialCommand()
    {
        $options = parent::getOptionsForSelfReferentialCommand();
        $options['piwik-tests-plugins'] = '..'; // make sure --piwik-tests-plugins is used correctly when executed in travis-ci
        return $options;
    }
}