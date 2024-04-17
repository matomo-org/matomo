<?php

/**
 * Matomo - free/libre analytics platform
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
        $value = new ValueDefinition($vars->$variableName);
        $value->setName($name);
        return $value;
    }

    public function getDefinitions(): array
    {
        $vars = new TestingEnvironmentVariables();
        $properties = $vars->getProperties();

        $result = [];
        foreach ($properties as $name => $property) {
            $value = new ValueDefinition($property);
            $value->setName($name);
            $result[] = $value;
        }

        return $result;
    }

    private function parseVariableName($name)
    {
        $parts = explode('.', $name, 3);
        return @$parts[2];
    }
}
