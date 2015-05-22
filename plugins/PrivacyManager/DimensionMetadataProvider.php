<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\PrivacyManager;

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
        return array(
            'log_link_visit_action' => array('idaction_url',
                'idaction_url_ref',
                'idaction_name',
                'idaction_name_ref',
                'idaction_event_category',
                'idaction_event_action'
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
    }
}