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
        $dumpPath = @$this->options['dump'];
        if (empty($dumpPath)) {
            throw new Exception("--dump option must be used when generating a .travis.yml for the piwik-tests-plugins repo."
                              . " Set it to the path to the repo's .travis.yml.");
        }

        return $dumpPath;
    }

    private function getTestsRepoPath()
    {
        return dirname($this->getTravisYmlOutputPath());
    }

    protected function getOptionsForSelfReferentialCommand()
    {
        $options = parent::getOptionsForSelfReferentialCommand();
        $options['dump'] = '../.travis.yml'; // make sure --dump is used correctly when executed in travis-ci
        return $options;
    }
}