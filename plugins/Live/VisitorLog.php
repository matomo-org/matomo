<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Visualization;
use Piwik\View;

/**
 * A special DataTable visualization for the Live.getLastVisitsDetails API method.
 *
 * @property VisitorLog\Config $config
 */
class VisitorLog extends Visualization
{
    const ID = 'Piwik\Plugins\Live\VisitorLog';
    const TEMPLATE_FILE = "@Live/_dataTableViz_visitorLog.twig";

    public static function getDefaultConfig()
    {
        return new VisitorLog\Config();
    }

    public function beforeLoadDataTable()
    {
        $this->requestConfig->addPropertiesThatShouldBeAvailableClientSide(array(
            'filter_limit',
            'filter_offset',
            'filter_sort_column',
            'filter_sort_order',
        ));

        if (!is_numeric($this->requestConfig->filter_limit)) {
            $this->requestConfig->filter_limit = 20;
        }

        $this->requestConfig->filter_sort_column = 'lastActionTimestamp';
        $this->requestConfig->disable_generic_filters = true;

        $offset = Common::getRequestVar('filter_offset', 0);
        $limit  = Common::getRequestVar('filter_limit', $this->requestConfig->filter_limit);

        $this->config->filters[] = array('Limit', array($offset, $limit));
    }

    /**
     * Configure visualization.
     */
    public function beforeRender()
    {
        $this->config->disable_row_actions = true;
        $this->config->datatable_js_type = 'VisitorLog';
        $this->config->enable_sort       = false;
        $this->config->show_search       = false;
        $this->config->show_exclude_low_population = false;
        $this->config->show_offset_information     = false;
        $this->config->show_all_views_icons        = false;
        $this->config->show_table_all_columns      = false;
        $this->config->show_export_as_rss_feed     = false;

        $this->config->documentation = Piwik::translate('Live_VisitorLogDocumentation', array('<br />', '<br />'));

        $filterEcommerce = Common::getRequestVar('filterEcommerce', 0, 'int');

        if (!is_array($this->config->custom_parameters)) {
            $this->config->custom_parameters = array();
        }

        // set a very high row count so that the next link in the footer of the data table is always shown
        $this->config->custom_parameters['totalRows'] = 10000000;
        $this->config->custom_parameters['smallWidth'] = (1 == Common::getRequestVar('small', 0, 'int'));
        $this->config->custom_parameters['filterEcommerce'] = $filterEcommerce;
        $this->config->custom_parameters['pageUrlNotDefined'] = Piwik::translate('General_NotDefined', Piwik::translate('Actions_ColumnPageURL'));

        $this->config->footer_icons = array(
            array(
                'class'   => 'tableAllColumnsSwitch',
                'buttons' => array(
                    array(
                        'id'    => static::ID,
                        'title' => Piwik::translate('Live_LinkVisitorLog'),
                        'icon'  => 'plugins/Morpheus/images/table.png'
                    )
                )
            )
        );

        // determine if each row has ecommerce activity or not
        if ($filterEcommerce) {
            $this->dataTable->filter(
                'ColumnCallbackAddMetadata',
                array(
                    'actionDetails',
                    'hasEcommerce',
                    function ($actionDetails) use ($filterEcommerce) {
                        foreach ($actionDetails as $action) {
                            $isEcommerceOrder = $action['type'] == 'ecommerceOrder'
                                       && $filterEcommerce == \Piwik\Plugins\Goals\Controller::ECOMMERCE_LOG_SHOW_ORDERS;
                            $isAbandonedCart = $action['type'] == 'ecommerceAbandonedCart'
                                       && $filterEcommerce == \Piwik\Plugins\Goals\Controller::ECOMMERCE_LOG_SHOW_ABANDONED_CARTS;
                            if ($isAbandonedCart || $isEcommerceOrder) {
                                return true;
                            }
                        }
                        return false;
                    }
                )
            );
        }
    }
}
