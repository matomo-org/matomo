<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\DataTable\Row;
use Piwik\Metrics;

/**
 * TODO
 *
 * TODO: note that this will be filled out in another issue
 */
abstract class Metric
{
    /**
     * The sub-namespace name in a plugin where Report components are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Metrics';

    /**
     * TODO
     *
     * @return Metric[]
     */
    public static function getAll()
    {
        $components = Manager::getInstance()->findMultipleComponents(self::COMPONENT_SUBNAMESPACE, __CLASS__);

        $result = array();
        foreach ($components as $componentClass) {
            /** @var Metric $component */
            $component = new $componentClass();

            $name = $component->getName();
            $result[$name] = $component;
        }
        return $result;
    }

    /**
     * TODO
     */
    abstract public function getName();

    /**
     * TODO
     */
    abstract public function getTranslatedName();

    /**
     * TODO
     */
    public function format($value)
    {
        return $value;
    }

    /**
     * TODO
     */
    public function getColumn(Row $row, $columnName, $mappingIdToName = null)
    {
        if (empty($mappingIdToName)) {
            $mappingIdToName = Metrics::getMappingFromNameToId();
        }

        $value = $row->getColumn($columnName);
        if ($value === false
            && isset($mappingIdToName[$columnName])
        ) {
            $value = $row->getColumn($mappingIdToName[$columnName]);
        }

        return $value;
    }
}