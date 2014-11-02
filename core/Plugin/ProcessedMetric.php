<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\DataTable\Row;

/**
 * TODO
 */
abstract class ProcessedMetric
{
    /**
     * The sub-namespace name in a plugin where Report components are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Metrics';

    /**
     * TODO
     *
     * @return ProcessedMetric[]
     */
    public static function getAll()
    {
        $components = Manager::getInstance()->findMultipleComponents(self::COMPONENT_SUBNAMESPACE, __CLASS__);

        $result = array();
        foreach ($components as $componentClass) {
            /** @var ProcessedMetric $component */
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
    abstract public function format($value);

    /**
     * TODO
     */
    abstract public function compute(Row $row);
}