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
use Piwik\Columns\Dimension;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Ecommerce\Columns\EcommerceType;
use Piwik\Plugins\Ecommerce\Columns\ProductViewCategory;
use Piwik\Plugins\Ecommerce\Columns\ProductViewCategory2;
use Piwik\Plugins\Ecommerce\Columns\ProductViewCategory3;
use Piwik\Plugins\Ecommerce\Columns\ProductViewCategory4;
use Piwik\Plugins\Ecommerce\Columns\ProductViewCategory5;
use Piwik\Plugins\Ecommerce\Columns\ProductViewName;
use Piwik\Plugins\Ecommerce\Columns\ProductViewSku;
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

    /**
     * @var Dimension[]
     */
    protected $actionMapping;

    /**
     * @var string
     */
    private $dimension;

    /**
     * @var string
     */
    private $recordName;

    /**
     * @var Dimension[]
     */
    private $dimensionsToAggregate;

    public function __construct(Dimension $dimension, string $recordName, array $otherDimensionsToAggregate = [])
    {
        $general = Config::getInstance()->General;
        $productReportsMaximumRows = $general['datatable_archiving_maximum_rows_products'];

        parent::__construct($productReportsMaximumRows, $productReportsMaximumRows, Metrics::INDEX_ECOMMERCE_ITEM_REVENUE);

        $this->dimension = $dimension;
        $this->recordName = $recordName;
        $this->dimensionsToAggregate = array_merge([$dimension], $otherDimensionsToAggregate);

        $this->actionMapping = [
            self::SKU_FIELD      => new ProductViewSku(),
            self::NAME_FIELD     => new ProductViewName(),
            self::CATEGORY_FIELD => new ProductViewCategory(),
            self::CATEGORY2_FIELD => new ProductViewCategory2(),
            self::CATEGORY3_FIELD => new ProductViewCategory3(),
            self::CATEGORY4_FIELD => new ProductViewCategory4(),
            self::CATEGORY5_FIELD => new ProductViewCategory5(),
        ];
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

        // try to query ecommerce items only, if ecommerce is actually used
        // otherwise we simply insert empty records
        if ($this->usesEcommerce($this->getSiteId($archiveProcessor))) {
            foreach ($this->dimensionsToAggregate as $dimension) {
                $query = $this->queryEcommerceItems($archiveProcessor, $dimension);
                $this->aggregateFromEcommerceItems($itemReports, $query);

                $query = $this->queryItemViewsForDimension($archiveProcessor, $dimension);
                $this->aggregateFromEcommerceViews($itemReports, $query);
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

    protected function queryEcommerceItems(ArchiveProcessor $archiveProcessor, Dimension $dimension)
    {
        $query = $archiveProcessor->newLogQuery('log_conversion_item');
        $query->addDimension($dimension, 'label'); // TODO: should default to label in the row output if one dimension total? maybe we just require a name, it's less magic
        $query->addDimension(new EcommerceType(), 'ecommerceType');
        $query->addEcommerceItemMetrics();
        $query->addWhere('log_conversion_item.deleted = 0');
        $query->addRowTransform(function (array $row) use ($dimension): ?array {
            $label = $this->getRowLabel($row, $dimension);
            if ($label === null) {
                return null;
            }

            $row['label'] = $label;

            if ($row['ecommerceType'] == GoalManager::IDGOAL_CART) {
                // abandoned carts are the number of visits with an abandoned cart
                $row[Metrics::INDEX_ECOMMERCE_ORDERS] = $row[Metrics::INDEX_NB_VISITS];
            }

            unset($row[Metrics::INDEX_NB_VISITS]);

            $this->roundColumnValues($row);

            return $row;
        });
        return $query;
    }

    /**
     * @param DataTable[] $itemReports
     */
    protected function aggregateFromEcommerceItems(array $itemReports, ArchiveProcessor\LogAggregationQuery $query): void
    {
        foreach ($query->execute() as $row) {
            $ecommerceType = $row['ecommerceType'];
            unset($row['ecommerceType']);

            $itemReports[$ecommerceType]->aggregateSimpleArrayWithLabel($row);
        }
    }

    protected function queryItemViewsForDimension(ArchiveProcessor $archiveProcessor, Dimension $dimension): ArchiveProcessor\LogAggregationQuery
    {
        $query = $archiveProcessor->newLogQuery('log_link_visit_action');

        $actionDimension = $this->actionMapping[$dimension->getColumnName()];
        $query->addDimension($actionDimension, 'label');
        $query->addActionMetrics();
        $query->addMetricSql('avg_price_viewed', 'AVG(log_link_visit_action.product_price)');
        $query->addWhere("log_link_visit_action.{$actionDimension->getColumnName()} is not null");
        $query->addRowTransform(function (array $row) use ($dimension): ?array {
            $label = $this->getRowLabel($row, $dimension);
            if ($label === null) {
                return null; // ignore empty additional categories
            }

            $row['label'] = $label;

            if (array_key_exists('avg_price_viewed', $row)) {
                $row['avg_price_viewed'] = round($row['avg_price_viewed'] ?: 0, GoalManager::REVENUE_PRECISION);
            }

            return $row;
        });
        return $query;
    }

    /**
     * @param DataTable $itemReports
     * @param ArchiveProcessor\LogAggregationQuery $query
     * @return void
     */
    protected function aggregateFromEcommerceViews(array $itemReports, ArchiveProcessor\LogAggregationQuery $query): void
    {
        foreach ($query->execute() as $row) {
            // add views to all types
            foreach ($itemReports as $table) {
                $table->aggregateSimpleArrayWithLabel($row);
            }
        }
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
            if (isset($row[$column])
                && $row[$column] == round($row[$column])
            ) {
                $row[$column] = round($row[$column]);
            }
        }
    }

    protected function getRowLabel(array &$row, Dimension $dimension): ?string
    {
        $label = $row['label'];
        if (empty($label)) {
            // An empty additional category -> skip this iteration
            if ($dimension !== $this->dimension) {
                return null;
            }
            $label = "Value not defined";
        }
        return $label;
    }
}
