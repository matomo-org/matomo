<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\ViewDataTable;

use Piwik\Common;
use Piwik\Piwik;

/**
 * ViewDataTable Manager.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class Manager
{

    /**
     * Returns the viewDataTable IDs of a visualization's class lineage.
     *
     * @see self::getVisualizationClassLineage
     *
     * @param string $klass The visualization class.
     *
     * @return array
     */
    public static function getIdsWithInheritance($klass)
    {
        $klasses = Common::getClassLineage($klass);

        $result = array();
        foreach ($klasses as $klass) {
            if ('Piwik\\Plugin\\ViewDataTable' != $klass
                && 'Piwik\\Plugin\\Visualization' != $klass) {
                $result[] = $klass::getViewDataTableId();
            }
        }

        return $result;
    }

    /**
     * Returns all registered visualization classes. Uses the 'Visualization.getAvailable'
     * event to retrieve visualizations.
     *
     * @return array Array mapping visualization IDs with their associated visualization classes.
     * @throws \Exception If a visualization class does not exist or if a duplicate visualization ID
     *                   is found.
     * @return array
     */
    public static function getAvailableViewDataTables()
    {
        /** @var string[] $visualizations */
        $visualizations = array();

        /**
         * This event is used to gather all available DataTable visualizations. Callbacks should add visualization
         * class names to the incoming array.
         */
        Piwik::postEvent('ViewDataTable.addViewDataTable', array(&$visualizations));

        $result = array();

        foreach ($visualizations as $viz) {
            if (!class_exists($viz)) {
                throw new \Exception("Invalid visualization class '$viz' found in Visualization.getAvailableVisualizations.");
            }

            if (!is_subclass_of($viz, '\\Piwik\\Plugin\\ViewDataTable')) {
                throw new \Exception("ViewDataTable class '$viz' does not extend Plugin/ViewDataTable");
            }

            $vizId = $viz::getViewDataTableId();

            if (isset($result[$vizId])) {
                throw new \Exception("ViewDataTable ID '$vizId' is already in use!");
            }

            $result[$vizId] = $viz;
        }

        return $result;
    }

    /**
     * Returns all available visualizations that are not part of the CoreVisualizations plugin.
     *
     * @return array Array mapping visualization IDs with their associated visualization classes.
     */
    public static function getNonCoreViewDataTables()
    {
        $result = array();

        foreach (static::getAvailableViewDataTables() as $vizId => $vizClass) {
            if (false === strpos($vizClass, 'Piwik\\Plugins\\CoreVisualizations')
                && false === strpos($vizClass, 'Piwik\\Plugins\\Goals\\Visualizations\\Goals')) {
                $result[$vizId] = $vizClass;
            }
        }

        return $result;
    }
}