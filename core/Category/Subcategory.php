<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Category;

/**
 * Base type for subcategories.
 *
 * All widgets within a subcategory will be rendered in the Piwik reporting UI under the same page. By default
 * you do not have to specify any subcategory as they are created automatically. Only create a subcategory if you
 * want to change the name for a specific subcategoryId or if you want to specify a different order so the subcategory
 * appears eg at a different order in the reporting menu. It also affects the order of reports in
 * `API.getReportMetadata` and wherever we display any reports.
 *
 * To define a subcategory just place a subclass within the `Categories` folder of your plugin.
 *
 * Subcategories can also be added through the {@hook Subcategory.addSubcategories} event.
 *
 * @api since Piwik 3.0.0
 */
class Subcategory
{
    /**
     * The id of the subcategory, see eg {@link Piwik\Widget\WidgetConfig::setSubcategoryId()`} or
     * {@link Piwik\Report\getSubcategoryId()}. The id will be used in the Piwik reporting URL and as the name
     * in the Piwik reporting submenu. If you want to define a different URL and name, specify a {@link $name}.
     * For example you might want to have the actual GoalId (eg '4') in the URL but the actual goal name in the
     * submenu (eg 'Downloads'). In this case one should specify `$id=4;$name='Downloads'`.
     *
     * @var string eg 'General_Overview' or 'VisitTime_ByServerTimeWidgetName'.
     */
    protected $id = '';

    /**
     * The id of the category the subcategory belongs to, must be specified.
     * See {@link Piwik\Widget\WidgetConfig::setCategoryId()`} or {@link Piwik\Report\getCategoryId()}.
     *
     * @var string A translation key eg 'General_Visits' or 'Goals_Goals'
     */
    protected $categoryId = '';

    /**
     * The name that shall be used in the menu etc, defaults to the specified {@link $id}. See {@link $id}.
     * @var string
     */
    protected $name = '';

    /**
     * The order of the subcategory. The lower the value the earlier a widget or a report will be displayed.
     * @var int
     */
    protected $order = 99;

    /**
     * Sets (overwrites) the id of the subcategory see {@link $id}.
     *
     * @param string $id A translation key eg 'General_Overview'.
     * @return static
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the id of the subcategory.
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the specified categoryId see {@link $categoryId}.
     *
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Sets (overwrites) the categoryId see {@link $categoryId}.
     *
     * @param string $categoryId
     * @return static
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * Sets (overwrites) the name see {@link $name} and {@link $id}.
     *
     * @param string $name A translation key eg 'General_Overview'.
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the name of the subcategory.
     * @return string
     */
    public function getName()
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        return $this->id;
    }

    /**
     * Sets (overwrites) the order see {@link $order}.
     *
     * @param int $order
     * @return static
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;
        return $this;
    }

    /**
     * Get the order of the subcategory.
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Get the help text (if any) for this category.
     * @return null
     */
    public function getHelp()
    {
        return null;
    }
}
