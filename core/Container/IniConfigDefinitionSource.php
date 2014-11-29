<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Definition\Exception\DefinitionException;
use DI\Definition\MergeableDefinition;
use DI\Definition\Source\ChainableDefinitionSource;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\ValueDefinition;
use Piwik\Config;

/**
 * Import the old INI config into PHP-DI.
 */
class IniConfigDefinitionSource implements DefinitionSource, ChainableDefinitionSource
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var DefinitionSource
     */
    private $chainedSource;

    /**
     * @param Config $config
     * @param string $prefix Prefix for the container entries.
     */
    public function __construct(Config $config, $prefix = 'old_config.')
    {
        $this->config = $config;
        $this->prefix = $prefix;
    }

    public function getDefinition($name, MergeableDefinition $parentDefinition = null)
    {
        // INI only contains values, so no definition merging here
        if ($parentDefinition) {
            return $this->notFound($name, $parentDefinition);
        }

        if (strpos($name, $this->prefix) !== 0) {
            return $this->notFound($name, $parentDefinition);
        }

        list($sectionName, $configKey) = $this->parseEntryName($name);

        $section = $this->getSection($sectionName);

        if ($configKey === null) {
            return new ValueDefinition($name, $section);
        }

        if (! array_key_exists($configKey, $section)) {
            return $this->notFound($name, $parentDefinition);
        }

        return new ValueDefinition($name, $section[$configKey]);
    }

    public function chain(DefinitionSource $source)
    {
        $this->chainedSource = $source;
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
        $section = $this->config->$sectionName;

        if (!is_array($section)) {
            throw new DefinitionException(sprintf(
                'Piwik\Config did not return an array for the config section %s',
                $section
            ));
        }

        return $section;
    }

    private function notFound($name, $parentDefinition)
    {
        if ($this->chainedSource) {
            return $this->chainedSource->getDefinition($name, $parentDefinition);
        }

        return null;
    }
}
