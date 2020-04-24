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
use Piwik\Development;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Exception;

/**
 * This example dimension only defines a name and does not track any data. It's supposed to be only used in reports.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Columns\Dimension} for more information.
 */
class ActionType extends ActionDimension
{
    protected $columnName = 'type';
    protected $dbTableName = 'log_action';
    protected $segmentName = 'actionType';
    protected $type = self::TYPE_ENUM;
    protected $nameSingular = 'Actions_ActionType';
    protected $namePlural = 'Actions_ActionTypes';
    protected $category = 'General_Actions';

    public function __construct()
    {
        $this->acceptValues = 'A type of action, such as: pageviews, contents, sitesearches, events, outlinks, downloads';
    }

    public function getEnumColumnValues()
    {
        $availableTypes = [];
        /**
         * Triggered to determine the available action types
         *
         * Plugin can use this event to add their own action types, so they are available in segmentation
         * The array maps internal ids to readable action type names used in visitor details
         *
         * **Example**
         *
         * public function addActionTypes(&$availableTypes)
         * {
         *     $availableTypes[] = array(
         *         'id' => 76,
         *         'name' => 'media_play'
         *      );
         * }
         *
         * @param array $availableTypes
         */
        Piwik::postEvent('Actions.addActionTypes', [&$availableTypes]);

        $types = [];

        foreach ($availableTypes as $type) {
            if (empty($type['id']) || empty($type['name'])) {
                throw new Exception("Invalid action added with event `Actions.addActionTypes`: " . var_export($type, true));
            }
            if (Development::isEnabled() && array_key_exists($type['id'], $types)) {
                throw new Exception(sprintf("Action '%s' with id %s couldn't be added, as '%s' was already added for this id", $type['name'], $type['id'], $types[$type['id']]));
            }
            $types[$type['id']] = $type['name'];
        }

        return $types;
    }

    public function configureMetrics(MetricsList $metricsList, DimensionMetricFactory $dimensionMetricFactory)
    {
        // do not generate any metric for this
    }

}
