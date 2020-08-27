<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin\Dimension;
use Piwik\Piwik;

/**
 * Provides metadata about dimensions for the LogDataPurger class.
 */
class DimensionMetadataProvider
{
    /**
     * Overrids for the result of the getActionReferenceColumnsByTable() method. Exists so Piwik
     * instances can be monkey patched, in case there are idaction columns that this class does not
     * naturally discover.
     *
     * @var array
     */
    private $actionReferenceColumnsOverride;

    public function __construct(array $actionReferenceColumnsOverride = array())
    {
        $this->actionReferenceColumnsOverride = $actionReferenceColumnsOverride;
    }

    /**
     * Returns a list of idaction column names organized by table name. Uses dimension metadata
     * to find idaction columns dynamically.
     *
     * Note: It is not currently possible to use the Piwik platform to add idaction columns to tables
     * other than log_link_visit_action (w/o doing something unsupported), so idaction columns in
     * other tables are hard coded.
     *
     * @return array[]
     */
    public function getActionReferenceColumnsByTable()
    {
        $result = array(
            'log_link_visit_action' => array('idaction_url',
                'idaction_url_ref',
                'idaction_name_ref'
            ),

            'log_conversion'        => array('idaction_url'),

            'log_visit'             => array('visit_exit_idaction_url',
                'visit_exit_idaction_name',
                'visit_entry_idaction_url',
                'visit_entry_idaction_name'),

            'log_conversion_item'   => array('idaction_sku',
                'idaction_name',
                'idaction_category',
                'idaction_category2',
                'idaction_category3',
                'idaction_category4',
                'idaction_category5')
        );

        $dimensionIdActionColumns = $this->getVisitActionTableActionReferences();
        $result['log_link_visit_action'] = array_unique(
            array_merge($result['log_link_visit_action'], $dimensionIdActionColumns));

        foreach ($this->actionReferenceColumnsOverride as $table => $columns) {
            if (empty($result[$table])) {
                $result[$table] = $columns;
            } else {
                $result[$table] = array_unique(array_merge($result[$table], $columns));
            }
        }

        /**
         * Triggered when detecting which log_action entries to keep. Any log tables that use the log_action
         * table to reference text via an ID should add their table info so no actions that are still in use
         * will be accidentally deleted.
         *
         * **Example**
         *
         *     Piwik::addAction('Db.getActionReferenceColumnsByTable', function(&$result) {
         *         $tableNameUnprefixed = 'log_example';
         *         $columnNameThatReferencesIdActionInLogActionTable = 'idaction_example';
         *         $result[$tableNameUnprefixed] = array($columnNameThatReferencesIdActionInLogActionTable);
         *     });
         * @param array $result
         */
        Piwik::postEvent('Db.getActionReferenceColumnsByTable', array(&$result));

        return $result;
    }

    private function getVisitActionTableActionReferences()
    {
        $idactionColumns = array();
        foreach (ActionDimension::getAllDimensions() as $actionDimension) {
            if ($this->isActionReference($actionDimension)) {
                $idactionColumns[] = $actionDimension->getColumnName();
            }
        }
        return $idactionColumns;
    }


    /**
     * Returns `true` if the column for this dimension is a reference to the `log_action` table (ie, an "idaction column"),
     * `false` if otherwise.
     *
     * @return bool
     */
    private function isActionReference(ActionDimension $dimension)
    {
        try {
            $dimension->getActionId();

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}
