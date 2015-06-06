<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

/**
 * Base class for Piwik application entry points.
 */
class Application
{
    /**
     * @var Environment
     */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->environment->init();
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}