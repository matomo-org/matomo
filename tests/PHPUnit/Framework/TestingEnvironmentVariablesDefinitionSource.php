<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use DI\Definition\Source\DefinitionSource;
use DI\Definition\ValueDefinition;

/**
 * PHP DI definition source that accesses variables defined in TestingEnvironmentVariables.
 */
class TestingEnvironmentVariablesDefinitionSource implements DefinitionSource
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param TestingEnvironmentVariables $vars
     * @param string $prefix
     */
    public function __construct($prefix = 'test.vars.')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (strpos($name, $this->prefix) !== 0) {
            return null;
        }

        $variableName = $this->parseVariableName($name);

        $vars = new TestingEnvironmentVariables();
        return new ValueDefinition($name, $vars->$variableName);
    }

    private function parseVariableName($name)
    {
        $parts = explode('.', $name, 3);
        return @$parts[2];
    }
}