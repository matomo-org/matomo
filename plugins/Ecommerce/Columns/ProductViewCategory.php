<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\DimensionSegmentFactory;
use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Manager;
use Piwik\Plugin\Segment;
use Piwik\Plugins\CustomVariables\Tracker\CustomVariablesRequestProcessor;
use Piwik\Segment\SegmentsList;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ProductViewCategory extends ActionDimension
{
    protected $type = self::TYPE_TEXT;
    protected $nameSingular = 'Ecommerce_ViewedProductCategory';
    protected $columnName = 'idaction_product_cat';
    protected $segmentName = 'productViewCategory';
    protected $columnType = 'INT(10) UNSIGNED NULL';
    protected $category = 'Goals_Ecommerce';
    protected $categoryNumber = 1;

    public function getName()
    {
        return parent::getName() . ' ' . $this->categoryNumber;
    }

    public function configureSegments(SegmentsList $segmentsList, DimensionSegmentFactory $dimensionSegmentFactory)
    {
        $individualProductCategorySegments = $this->getProductCategorySegments(ProductCategory::PRODUCT_CATEGORY_COUNT);

        // add individual productCategoryN segments for use as a union (these segments are not available through the UI/API)
        foreach ($individualProductCategorySegments as $i => $productCategoryName) {
            $productCategoryColumnName = 'idaction_product_cat';
            if ($i > 0) {
                $productCategoryColumnName .= $i + 1;
            }

            $segment = new Segment();
            $segment->setCategory($this->category);
            $segment->setType('dimension');
            $segment->setName(Piwik::translate('Ecommerce_ViewedProductCategory') . ' ' . ($i + 1));
            $segment->setSegment($productCategoryName);
            $segment->setSqlFilter('\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment');
            $segment->setSqlSegment('log_link_visit_action.' . $productCategoryColumnName);
            $segment->setIsInternal(true);
            $segment->setSuggestedValuesCallback(function ($idSite, $maxValuesToReturn, $table) {
                $values = [];
                foreach ($table->getRows() as $row) {
                    foreach ($row->getColumn('actionDetails') as $actionRow) {
                        if (isset($actionRow['productViewCategories'])) {
                            $values = array_merge($values, $actionRow['productViewCategories']);
                        }
                    }
                }
                return $values;
            });
            $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
        }

        // add a union of these individual columns as productCategory
        $segment = new Segment();
        $segment->setCategory($this->category);
        $segment->setType('dimension');
        $segment->setSegment('productViewCategory');
        $segment->setName(Piwik::translate('Ecommerce_ViewedProductCategory'));
        $segment->setUnionOfSegments($individualProductCategorySegments);
        $segmentsList->addSegment($dimensionSegmentFactory->createSegment($segment));
    }

    private function getProductCategorySegments($categoryCount)
    {
        $result = [];
        for ($i = 0; $i < $categoryCount; ++$i) {
            $segmentName = 'productViewCategory' . ($i + 1);
            $result[] = $segmentName;
        }
        return $result;
    }

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if ($request->hasParam('_pkc')) {
            $categories = Common::unsanitizeInputValue($request->getParam('_pkc'));
            $categories = $this->handleCategoryParam($categories);

            return $categories[$this->categoryNumber - 1] ?? false;
        }

        // fall back to custom variables (might happen if old logs are replayed)
        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $customVariables = CustomVariablesRequestProcessor::getCustomVariablesInPageScope($request);
            if (isset($customVariables['custom_var_k5']) && $customVariables['custom_var_k5'] === '_pkc') {
                $categories = $this->handleCategoryParam($customVariables['custom_var_v5'] ?? '');

                return $categories[$this->categoryNumber - 1] ?? false;
            }
        }

        return parent::onLookupAction($request, $action);
    }

    protected function handleCategoryParam($categories)
    {
        if (0 === strpos($categories, '["')) {
            $categories = array_values(array_filter((array) @\json_decode($categories, true)));
        } else {
            $categories = [$categories];
        }

        return $categories;
    }

    public function getActionId()
    {
        return Action::TYPE_ECOMMERCE_ITEM_CATEGORY;
    }
}