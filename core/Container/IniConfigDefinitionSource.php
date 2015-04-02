<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Definition\Exception\DefinitionException;
use DI\Definition\Source\ChainableDefinitionSource;
use DI\Definition\ValueDefinition;
use Piwik\Config\IniFileChain;

/**
 * Import the old INI config into PHP-DI.
 */
class IniConfigDefinitionSource extends ChainableDefinitionSource
{
    /**
     * @var IniFileChain
     */
    private $config;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param IniFileChain $config
     * @param string $prefix Prefix for the container entries.
     */
    public function __construct(IniFileChain $config, $prefix = 'ini.')
    {
        $this->config = $config;
        $this->prefix = $prefix;
    }

    protected function findDefinition($name)
    {
        if (strpos($name, $this->prefix) !== 0) {
            return null;
        }

        list($sectionName, $configKey) = $this->parseEntryName($name);

        $section = $this->getSection($sectionName);

        if ($configKey === null) {
            return new ValueDefinition($name, $section);
        }

        if (! array_key_exists($configKey, $section)) {
            return null;
        }

        return new ValueDefinition($name, $section[$configKey]);
    }

    private function parseEntryName($name)
    {
        $parts = explode('.', $name, 3);

        array_shift($parts);

        if (! isset($parts[1])) {
            $parts[1] = null;
        }

        return $parts;
    }

    private function getSection($sectionName)
    {
        $section = $this->config->get($sectionName);

        if (!is_array($section)) {
            throw new DefinitionException(sprintf(
                'IniFileChain did not return an array for the config section %s',
                $section
            ));
        }

        return $section;
    }
}
