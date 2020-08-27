<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin;

/**
 * Get categories and subcategories that are defined by plugins.
 */
class Categories
{
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /** @return \Piwik\Category\Category[] */
    public function getAllCategories()
    {
        $categories = $this->pluginManager->findMultipleComponents('Categories', '\\Piwik\\Category\\Category');

        $instances = array();
        foreach ($categories as $category) {
            $cat = StaticContainer::getContainer()->make($category);
            $instances[$cat->getId()] = $cat;
        }

        return $instances;
    }

    /** @return \Piwik\Category\Subcategory[] */
    public function getAllSubcategories()
    {
        $subcategories = array();

        /**
         * Triggered to add custom subcategories.
         *
         * **Example**
         *
         *     public function addSubcategories(&$subcategories)
         *     {
         *         $subcategory = new Subcategory();
         *         $subcategory->setId('General_Overview');
         *         $subcategory->setCategoryId('General_Visits');
         *         $subcategory->setOrder(5);
         *         $subcategories[] = $subcategory;
         *     }
         *
         * @param array &$subcategories An array containing a list of subcategories.
         */
        Piwik::postEvent('Category.addSubcategories', array(&$subcategories));

        $classes = $this->pluginManager->findMultipleComponents('Categories', '\\Piwik\\Category\\Subcategory');

        foreach ($classes as $subcategory) {
            $subcategories[] = StaticContainer::getContainer()->make($subcategory);
        }

        return $subcategories;
    }
}