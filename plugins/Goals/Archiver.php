<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals;

use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;

class Archiver extends \Piwik\Plugin\Archiver
{
    public const VISITS_UNTIL_RECORD_NAME = 'visits_until_conv';
    public const DAYS_UNTIL_CONV_RECORD_NAME = 'days_until_conv';
    public const ITEMS_SKU_RECORD_NAME = 'Goals_ItemsSku';
    public const ITEMS_NAME_RECORD_NAME = 'Goals_ItemsName';
    public const ITEMS_CATEGORY_RECORD_NAME = 'Goals_ItemsCategory';
    public const SKU_FIELD = 'idaction_sku';
    public const NAME_FIELD = 'idaction_name';
    public const CATEGORY_FIELD = 'idaction_category';
    public const CATEGORY2_FIELD = 'idaction_category2';
    public const CATEGORY3_FIELD = 'idaction_category3';
    public const CATEGORY4_FIELD = 'idaction_category4';
    public const CATEGORY5_FIELD = 'idaction_category5';
    public const NO_LABEL = ':';
    public const LOG_CONVERSION_TABLE = 'log_conversion';
    public const VISITS_COUNT_FIELD = 'visitor_count_visits';
    public const SECONDS_SINCE_FIRST_VISIT_FIELD = 'visitor_seconds_since_first';

    public function getDependentSegmentsToArchive(): array
    {
        $hasConversions = $this->getProcessor()->getNumberOfVisitsConverted() > 0;
        if (!$hasConversions) {
            return [];
        }

        return [
            VisitFrequencyAPI::NEW_VISITOR_SEGMENT,
            VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT,
        ];
    }

    /**
     * This array stores the ranges to use when displaying the 'visits to conversion' report
     */
    public static $visitCountRanges = [
        [1, 1],
        [2, 2],
        [3, 3],
        [4, 4],
        [5, 5],
        [6, 6],
        [7, 7],
        [8, 8],
        [9, 14],
        [15, 25],
        [26, 50],
        [51, 100],
        [100],
    ];

    /**
     * This array stores the ranges to use when displaying the 'days to conversion' report
     */
    public static $daysToConvRanges = [
        [0, 0],
        [1, 1],
        [2, 2],
        [3, 3],
        [4, 4],
        [5, 5],
        [6, 6],
        [7, 7],
        [8, 14],
        [15, 30],
        [31, 60],
        [61, 120],
        [121, 364],
        [364],
    ];

    protected $dimensionRecord = [
        self::SKU_FIELD      => self::ITEMS_SKU_RECORD_NAME,
        self::NAME_FIELD     => self::ITEMS_NAME_RECORD_NAME,
        self::CATEGORY_FIELD => self::ITEMS_CATEGORY_RECORD_NAME
    ];
    protected $actionMapping = [
        self::SKU_FIELD      => 'idaction_product_sku',
        self::NAME_FIELD     => 'idaction_product_name',
        self::CATEGORY_FIELD => 'idaction_product_cat',
        self::CATEGORY2_FIELD => 'idaction_product_cat2',
        self::CATEGORY3_FIELD => 'idaction_product_cat3',
        self::CATEGORY4_FIELD => 'idaction_product_cat4',
        self::CATEGORY5_FIELD => 'idaction_product_cat5',
    ];

    /**
     * @param string $recordName 'nb_conversions'
     * @param int|bool $idGoal idGoal to return the metrics for, or false to return overall
     * @return string Archive record name
     */
    public static function getRecordName($recordName, $idGoal = false)
    {
        $idGoalStr = '';
        if ($idGoal !== false) {
            $idGoalStr = $idGoal . "_";
        }
        return 'Goal_' . $idGoalStr . $recordName;
    }

    public static function getItemRecordNameAbandonedCart($recordName)
    {
        return $recordName . '_Cart';
    }
}
