<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Goals\Archiver;
use Piwik\Tracker\GoalManager;

class ProductRecord extends Base
{
    const SKU_FIELD = 'idaction_sku';
    const NAME_FIELD = 'idaction_name';
    const CATEGORY_FIELD = 'idaction_category';
    const CATEGORY2_FIELD = 'idaction_category2';
    const CATEGORY3_FIELD = 'idaction_category3';
    const CATEGORY4_FIELD = 'idaction_category4';
    const CATEGORY5_FIELD = 'idaction_category5';

    const ITEMS_SKU_RECORD_NAME = 'Goals_ItemsSku';
    const ITEMS_NAME_RECORD_NAME = 'Goals_ItemsName';
    const ITEMS_CATEGORY_RECORD_NAME = 'Goals_ItemsCategory';

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
     * @var string
     */
    private $dimension;

    /**
     * @var string
     */
    private $recordName;

    /**
     * @var string[]
     */
    private $dimensionsToAggregate;

    public function __construct($dimension, $recordName, $otherDimensionsToAggregate = [])
    {
        $general = Config::getInstance()->General;
        $productReportsMaximumRows = $general['datatable_archiving_maximum_rows_products'];

        parent::__construct($productReportsMaximumRows, $productReportsMaximumRows, Metrics::INDEX_ECOMMERCE_ITEM_REVENUE);

        $this->dimension = $dimension;
        $this->recordName = $recordName;
        $this->dimensionsToAggregate = array_merge([$dimension], $otherDimensionsToAggregate);
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        return Manager::getInstance()->isPluginActivated('Ecommerce');
    }

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        $abandonedCartRecordName = Archiver::getItemRecordNameAbandonedCart($this->recordName);

        return [
            Record::make(Record::TYPE_BLOB, $this->recordName),
            Record::make(Record::TYPE_BLOB, $abandonedCartRecordName),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $itemReports = [];
        foreach ($this->getEcommerceIdGoals() as $ecommerceType) {
            $itemReports[$ecommerceType] = new DataTable();
        }

        $logAggregator = $archiveProcessor->getLogAggregator();

        // try to query ecommerce items only, if ecommerce is actually used
        // otherwise we simply insert empty records
        if ($this->usesEcommerce($this->getSiteId($archiveProcessor))) {
            foreach ($this->dimensionsToAggregate as $dimension) {
                $query = $logAggregator->queryEcommerceItems($dimension);
                if ($query !== false) {
                    $this->aggregateFromEcommerceItems($itemReports, $query, $dimension);
                }

                $query = $this->queryItemViewsForDimension($logAggregator, $dimension);
                if ($query !== false) {
                    $this->aggregateFromEcommerceViews($itemReports, $query, $dimension);
                }
            }
        }

        $records = [];
        foreach ($itemReports as $ecommerceType => $table) {
            $recordName = $this->recordName;
            if ($ecommerceType == GoalManager::IDGOAL_CART) {
                $recordName = Archiver::getItemRecordNameAbandonedCart($recordName);
            }
            $records[$recordName] = $table;
        }
        return $records;
    }

    protected function aggregateFromEcommerceItems(array $itemReports, $query, string $dimension): void
    {
        while ($row = $query->fetch()) {
            $ecommerceType = $row['ecommerceType'];

            $label = $this->cleanupRowGetLabel($row, $dimension);
            if ($label === null) {
                continue;
            }

            $this->roundColumnValues($row);

            $table = $itemReports[$ecommerceType];

            $tableRow = new Row([Row::COLUMNS => ['label' => $label] + $row]);
            $existingRow = $table->getRowFromLabel($label);
            if (!empty($existingRow)) {
                $existingRow->sumRow($tableRow);
            } else {
                $table->addRow($tableRow);
            }
        }
    }

    protected function aggregateFromEcommerceViews(array $itemReports, $query, string $dimension): void
    {
        while ($row = $query->fetch()) {
            $label = $this->getRowLabel($row, $dimension);
            if ($label === false) {
                continue; // ignore empty additional categories
            }

            unset($row['label']);

            if (array_key_exists('avg_price_viewed', $row)) {
                $row['avg_price_viewed'] = round($row['avg_price_viewed'] ?: 0, GoalManager::REVENUE_PRECISION);
            }

            // add views to all types
            foreach ($itemReports as $table) {
                $tableRow = new Row([Row::COLUMNS => ['label' => $label] + $row]);
                $existingRow = $table->getRowFromLabel($label);
                if (!empty($existingRow)) {
                    $existingRow->sumRow($tableRow);
                } else {
                    $table->addRow($tableRow);
                }
            }
        }
    }

    protected function queryItemViewsForDimension(LogAggregator $logAggregator, string $dimension)
    {
        $column = $this->actionMapping[$dimension];
        $where  = "log_link_visit_action.$column is not null";

        return $logAggregator->queryActionsByDimension(
            ['label' => 'log_action1.name'],
            $where,
            ['AVG(log_link_visit_action.product_price) AS `avg_price_viewed`'],
            false,
            null,
            [$column]
        );
    }

    protected function roundColumnValues(array &$row): void
    {
        $columnsToRound = array(
            Metrics::INDEX_ECOMMERCE_ITEM_REVENUE,
            Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY,
            Metrics::INDEX_ECOMMERCE_ITEM_PRICE,
            Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED,
        );
        foreach ($columnsToRound as $column) {
            if (
                isset($row[$column])
                && $row[$column] == round($row[$column])
            ) {
                $row[$column] = round($row[$column]);
            }
        }
    }

    protected function getRowLabel(array &$row, string $dimension): ?string
    {
        $label = $row['label'];
        if (empty($label)) {
            // An empty additional category -> skip this iteration
            if ($dimension != $this->dimension) {
                return null;
            }
            $label = "Value not defined";
        }
        return $label;
    }

    protected function cleanupRowGetLabel(array &$row, string $dimension): ?string
    {
        $label = $this->getRowLabel($row, $dimension);

        if (isset($row['ecommerceType']) && $row['ecommerceType'] == GoalManager::IDGOAL_CART) {
            // abandoned carts are the number of visits with an abandoned cart
            $row[Metrics::INDEX_ECOMMERCE_ORDERS] = $row[Metrics::INDEX_NB_VISITS];
        }

        unset($row[Metrics::INDEX_NB_VISITS]);
        unset($row['label']);
        unset($row['labelIdAction']);
        unset($row['ecommerceType']);

        return $label;
    }
}
