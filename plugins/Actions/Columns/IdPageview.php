<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Plugin\Dimension\ActionDimension;

class IdPageview extends ActionDimension
{
    protected $columnName = 'idpageview';
    protected $columnType = 'CHAR(6) NULL DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'Actions_ColumnIdPageview';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action $action
     *
     * @return mixed|false
     * @api
     */
    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        return substr($request->getParam('pv_id'), 0, 6);
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // metrics for idpageview do not really make any sense
    }

}