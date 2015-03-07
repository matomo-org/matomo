<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TestRunner\TravisYml\Generator;

use Piwik\Plugins\TestRunner\TravisYml\Generator;

class CoreTravisYmlGenerator extends Generator
{
    protected function configureView()
    {
        parent::configureView();

        $this->view->setGenerationMode('core');
    }

    public function getTravisYmlOutputPath()
    {
        return PIWIK_INCLUDE_PATH . '/.travis.yml';
    }
}