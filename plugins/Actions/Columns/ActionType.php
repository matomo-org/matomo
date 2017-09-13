<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\DimensionMetricFactory;
use Piwik\Columns\MetricsList;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Exception;

/**
 * This example dimension only defines a name and does not track any data. It's supposed to be only used in reports.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Columns\Dimension} for more information.
 */
class ActionType extends ActionDimension
{
    private $types = array(
        Action::TYPE_PAGE_URL => 'pageviews',
        Action::TYPE_CONTENT => 'contents',
        Action::TYPE_SITE_SEARCH => 'sitesearches',
        Action::TYPE_EVENT => 'events',
        Action::TYPE_OUTLINK => 'outlinks',
        Action::TYPE_DOWNLOAD => 'downloads'
    );

    protected $columnName = 'type';
    protected $dbTableName = 'log_action';
    protected $segmentName = 'actionType';
    protected $type = self::TYPE_ENUM;
    protected $nameSingular = 'Actions_ActionType';
    protected $namePlural = 'Actions_ActionTypes';
    protected $category = 'General_Actions';

    public function __construct()
    {
        $this->acceptValues = sprintf('A type of action, such as: %s', implode(', ', $this->types));
    }

    public function getEnumColumnValues()
    {
        return $this->types;
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // do not genereate any metric for this
    }

}