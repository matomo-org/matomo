<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Category\CategoryList;
use Piwik\Piwik;
use Piwik\Plugins\CoreHome\CoreHome;
use Piwik\Report\ReportWidgetConfig;
use Piwik\Category\Category;
use Piwik\Category\Subcategory;
use Piwik\Widget\WidgetContainerConfig;
use Piwik\Widget\WidgetConfig;
use Piwik\Widget\WidgetsList;

class WidgetMetadata
{
    public function getPagesMetadata(CategoryList $categoryList, WidgetsList $widgetsList)
    {
        $this->createMissingCategoriesAndSubcategories($categoryList, $widgetsList->getWidgetConfigs());

        return $this->buildPagesMetadata($categoryList, $widgetsList);
    }

    public function getWidgetMetadata(CategoryList $categoryList, WidgetsList $widgetsList)
    {
        $this->createMissingCategoriesAndSubcategories($categoryList, $widgetsList->getWidgetConfigs());

        $flat = array();

        foreach ($widgetsList->getWidgetConfigs() as $widgetConfig) {
            $metadataOverrides = [];

            /** @var WidgetConfig[] $widgets */
            $widgets = array($widgetConfig);
            if ($widgetConfig instanceof WidgetContainerConfig) {
                // so far we go only one level down, in theory these widgetConfigs could have again containers containing configs
                $widgets = array_merge($widgets, $widgetConfig->getWidgetConfigs());
            }

            foreach ($widgets as $widget) {
                // make sure to include only widgetizable widgets
                if (!$widget->isWidgetizeable() || !$widget->getName()) {
                    continue;
                }

                // widgets in containers with ByDimension layout have a special, unrecognized category/subcategory
                // (eg, "Sales by Referrer Type"). we change it to the container's category/subcategory so the widget
                // will appear in the dashboard manager.
                if ($widgetConfig instanceof WidgetContainerConfig
                    && $widgetConfig->getLayout() == CoreHome::WIDGET_CONTAINER_LAYOUT_BY_DIMENSION
                ) {
                    $metadataOverrides = [
                        'category' => $widgetConfig->getCategoryId(),
                        'subcategory' => $widgetConfig->getSubcategoryId(),
                        'name' => Piwik::translate($widget->getCategoryId()) . ': '
                            . Piwik::translate($widget->getName()),
                    ];
                }

                $flat[] = $this->buildWidgetMetadata($widget, $categoryList, $metadataOverrides);
            }
        }

        usort($flat, array($this, 'sortWidgets'));

        return $flat;
    }

    /**
     * @param WidgetConfig $widget
     * @param CategoryList|null $categoryList If null, no category information will be added to the widgets in first
     *                                        level (they will be added to nested widgets as potentially needed eg for
     *                                        widgets in ByDimensionView where they are needed to build the left menu)
     * @param array $metadataOverrides Overrides for data in `$widget`. Currently only 'name', 'category', 'subcategory'
     *                                 are recognized.
     * @return array
     */
    public function buildWidgetMetadata(WidgetConfig $widget, $categoryList = null, array $metadataOverrides = [])
    {
        $widgetName = !empty($metadataOverrides['name']) ? $metadataOverrides['name'] : $widget->getName();

        $item = array(
            'name' => Piwik::translate($widgetName),
        );

        if (isset($categoryList)) {
            $widgetCategory = !empty($metadataOverrides['category'])
                ? $metadataOverrides['category'] : $widget->getCategoryId();
            $widgetSubcategory = !empty($metadataOverrides['subcategory'])
                ? $metadataOverrides['subcategory'] : $widget->getSubcategoryId();

            $category    = $categoryList->getCategory($widgetCategory);
            $subcategory = $category ? $category->getSubcategory($widgetSubcategory) : null;

            $item['category']    = $this->buildCategoryMetadata($category);
            $item['subcategory'] = $this->buildSubcategoryMetadata($subcategory);
        }

        $item['module']      = $widget->getModule();
        $item['action']      = $widget->getAction();
        $item['order']       = $widget->getOrder();
        $item['parameters']  = $widget->getParameters();
        $item['uniqueId']    = $widget->getUniqueId();
        $item['isWide']      = $widget->isWide();

        $middleware = $widget->getMiddlewareParameters();

        if (!empty($middleware)) {
            $item['middlewareParameters'] = $middleware;
        }

        if ($widget instanceof ReportWidgetConfig) {
            $item['viewDataTable'] = $widget->getViewDataTable();
            $item['isReport'] = true;
        }

        if ($widget instanceof WidgetContainerConfig) {
            $item['layout'] = $widget->getLayout();
            $item['isContainer'] = true;

            // we do not want to create categories to the initial categoryList. Otherwise we'd maybe display more pages
            // etc.
            $subCategoryList = new CategoryList();
            $this->createMissingCategoriesAndSubcategories($subCategoryList, $widget->getWidgetConfigs());

            $children = array();
            foreach ($widget->getWidgetConfigs() as $widgetConfig) {
                $children[] = $this->buildWidgetMetadata($widgetConfig, $subCategoryList);
            }
            $item['widgets'] = $children;
        }

        return $item;
    }

    private function sortWidgets($widgetA, $widgetB) {
        $orderA = $widgetA['category']['order'];
        $orderB = $widgetB['category']['order'];

        if ($orderA === $orderB) {
            if (!empty($widgetA['subcategory']['order']) && !empty($widgetB['subcategory']['order'])) {

                $subOrderA = $widgetA['subcategory']['order'];
                $subOrderB = $widgetB['subcategory']['order'];

                if ($subOrderA === $subOrderB) {
                    return $this->compareWidgetIds($widgetA, $widgetB);
                }

                return $subOrderA > $subOrderB ? 1 : -1;
            } else if (empty($widgetA['subcategory']['order']) && empty($widgetB['subcategory']['order'])) {
                return $this->compareWidgetIds($widgetA, $widgetB);
            } elseif (!empty($widgetA['subcategory']['order'])) {

                return 1;
            }

            return -1;
        }

        return $orderA > $orderB ? 1 : -1;
    }

    private function compareWidgetIds($widgetA, $widgetB)
    {
        if ($widgetA['uniqueId'] == $widgetB['uniqueId']) {
            return 0;
        }

        return $widgetA['uniqueId'] > $widgetB['uniqueId'] ? 1 : -1;
    }

    /**
     * @param Category|null $category
     * @return array
     */
    private function buildCategoryMetadata($category)
    {
        if (!isset($category)) {
            return null;
        }

        return array(
            'id'    => (string) $category->getId(),
            'name'  => $category->getDisplayName(),
            'order' => $category->getOrder(),
            'icon' => $category->getIcon(),
            'help' => Piwik::translate($category->getHelp()),
        );
    }

    /**
     * @param Subcategory|null $subcategory
     * @return array
     */
    private function buildSubcategoryMetadata($subcategory)
    {
        if (!isset($subcategory)) {
            return null;
        }

        return array(
            'id'    => (string) $subcategory->getId(),
            'name'  => Piwik::translate($subcategory->getName()),
            'order' => $subcategory->getOrder(),
            'help' => Piwik::translate($subcategory->getHelp()),
        );
    }

    /**
     * @param CategoryList $categoryList
     * @param WidgetConfig[] $widgetConfigs
     */
    private function createMissingCategoriesAndSubcategories($categoryList, $widgetConfigs)
    {
        // move reports into categories/subcategories and create missing ones if needed
        foreach ($widgetConfigs as $widgetConfig) {
            $categoryId    = $widgetConfig->getCategoryId();
            $subcategoryId = $widgetConfig->getSubcategoryId();

            if (!$categoryId) {
                continue;
            }

            if ($widgetConfig instanceof WidgetContainerConfig && !$widgetConfig->getWidgetConfigs()) {
                // if a container does not contain any widgets, ignore it
                continue;
            }

            if (!$categoryList->hasCategory($categoryId)) {
                $categoryList->addCategory($this->createCategory($categoryId));
            }

            if (!$subcategoryId) {
                continue;
            }

            $category = $categoryList->getCategory($categoryId);

            if (!$category->hasSubcategory($subcategoryId)) {
                $category->addSubcategory($this->createSubcategory($categoryId, $subcategoryId));
            }
        }
    }

    private function createCategory($categoryId)
    {
        $category = new Category();
        $category->setId($categoryId);
        return $category;
    }

    private function createSubcategory($categoryId, $subcategoryId)
    {
        $subcategory = new Subcategory();
        $subcategory->setCategoryId($categoryId);
        $subcategory->setId($subcategoryId);
        return $subcategory;
    }

    /**
     * @param CategoryList $categoryList
     * @param WidgetsList $widgetsList
     * @return array
     */
    private function buildPagesMetadata(CategoryList $categoryList, WidgetsList $widgetsList)
    {
        $pages = array();

        $widgets = array();
        foreach ($widgetsList->getWidgetConfigs() as $config) {
            $pageId = $this->buildPageId($config->getCategoryId(), $config->getSubcategoryId());

            if (!isset($widgets[$pageId])) {
                $widgets[$pageId] = array();
            }

            $widgets[$pageId][] = $config;
        }

        foreach ($categoryList->getCategories() as $category) {
            foreach ($category->getSubcategories() as $subcategory) {
                $pageId = $this->buildPageId($category->getId(), $subcategory->getId());

                if (!empty($widgets[$pageId])) {
                    $pages[] = $this->buildPageMetadata($category, $subcategory, $widgets[$pageId]);
                }
            }
        }

        return $pages;
    }

    private function buildPageId($categoryId, $subcategoryId)
    {
        return $categoryId . '.' . $subcategoryId;
    }

    public function buildPageMetadata(Category $category, Subcategory $subcategory, $widgetConfigs)
    {
        $ca = array(
            'uniqueId' => $this->buildPageId($category->getId(), $subcategory->getId()),
            'category' => $this->buildCategoryMetadata($category),
            'subcategory' => $this->buildSubcategoryMetadata($subcategory),
            'widgets' => array()
        );

        foreach ($widgetConfigs as $config) {
            $ca['widgets'][] = $this->buildWidgetMetadata($config);
        }

        return $ca;
    }

}
