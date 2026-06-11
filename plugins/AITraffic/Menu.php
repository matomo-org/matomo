<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AITraffic;

use Piwik\Category\CategoryList;
use Piwik\Menu\MenuAi;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Request;

class Menu extends \Piwik\Plugin\Menu
{
    private const CATEGORY_ID = 'General_AIAssistants';

    /**
     * @var ReportsProvider
     */
    private $reportsProvider;

    public function __construct(?ReportsProvider $reportsProvider = null)
    {
        parent::__construct();

        $this->reportsProvider = $reportsProvider ?: new ReportsProvider();
    }

    public function configureAiInsightsMenu(MenuAi $menu)
    {
        $idSite = Request::fromRequest()->getIntegerParameter('idSite', 0);
        if (!$idSite || !Piwik::isUserHasViewAccess($idSite)) {
            return;
        }

        $subcategories = $this->getSubcategories();
        if (empty($subcategories)) {
            return;
        }

        $firstSubcategory = (string) array_key_first($subcategories);
        $firstUrl = $this->urlForSubcategory($firstSubcategory);
        if (empty($firstUrl)) {
            return;
        }

        $menu->registerMenuIcon('AITraffic_AITraffic', $this->getCategoryIcon());
        $menu->addItem('AITraffic_AITraffic', null, $firstUrl, 10);

        foreach ($subcategories as $subcategory => $order) {
            $subcategory = (string) $subcategory;
            $url = $subcategory === $firstSubcategory ? $firstUrl : $this->urlForSubcategory($subcategory);
            if (empty($url)) {
                continue;
            }
            $menu->addItem('AITraffic_AITraffic', $subcategory, $url, $order);
        }
    }

    /**
     * Returns the subcategory IDs that have at least one report in the AI Assistants category,
     * each mapped to the order of its lowest-ordered report. Ordering by report order (rather
     * than report discovery order) keeps the menu in the intended display order.
     *
     * @return array<string, int> map of subcategory ID => order, sorted ascending by order
     */
    private function getSubcategories(): array
    {
        $subcategories = [];

        foreach ($this->reportsProvider->getAllReports() as $report) {
            if ($report->getCategoryId() !== self::CATEGORY_ID) {
                continue;
            }
            $subcategory = $report->getSubcategoryId();
            if (empty($subcategory)) {
                continue;
            }
            $order = (int) $report->getOrder();
            if (!isset($subcategories[$subcategory]) || $order < $subcategories[$subcategory]) {
                $subcategories[$subcategory] = $order;
            }
        }

        asort($subcategories);

        return $subcategories;
    }

    /**
     * @param string $subcategory
     * @return array|null
     */
    private function urlForSubcategory($subcategory)
    {
        $params = [
            'category' => self::CATEGORY_ID,
            'subcategory' => $subcategory,
        ];

        try {
            return $this->urlForActionWithDefaultUserParams('index', $params);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCategoryIcon()
    {
        $category = CategoryList::get()->getCategory(self::CATEGORY_ID);
        return $category ? $category->getIcon() : 'icon-ai-assistants';
    }
}
