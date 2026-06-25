<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Category;

use Piwik\Piwik;

/**
 * Base type for category. lets you change the name for a categoryId and specify a different order
 * so the category appears eg at a different order in the reporting menu.
 *
 * This class is for now not exposed as public API until needed. Categories of plugins will be automatically
 * displayed in the menu at the very right after all core categories.
 */
class Category
{
    /**
     * Identifier of the default reporting menu group (the main "Analytics" reporting menu). Categories
     * that do not declare an explicit group belong to this group and are shown in the main reporting menu.
     */
    public const DEFAULT_GROUP = '';

    /**
     * The id of the category as specified eg in {@link Piwik\Widget\WidgetConfig::setCategoryId()`} or
     * {@link Piwik\Report\getCategoryId()}. The id is used as the name in the menu and will be visible in the
     * URL.
     *
     * @var string Should be a translation key, eg 'General_Vists'
     */
    protected $id = '';

    /**
     * The reporting menu groups (top-level menu sections) this category is shown in. An empty list means
     * the category is shown in the default Analytics reporting menu only. A category may belong to more
     * than one group, e.g. to be shown both in Analytics and in a dedicated section such as "AI Insights"
     * during a transition. Group ids should be translation keys so they can be used as the section label.
     *
     * @var string[]
     */
    protected $groups = array();

    /**
     * @var Subcategory[]
     */
    protected $subcategories = array();

    /**
     * The order of the category. The lower the value the further left the category will appear in the menu.
     * @var int
     */
    protected $order = 99;

    /**
     * The icon for this category, eg 'icon-user'
     * @var string
     */
    protected $icon = '';

    /**
     * Optional widget spec to replace the category in the reporting menu, e.g. Marketplace.RichMenuButton
     *
     * @var string
     */
    protected $widget = '';

    /**
     * @param int $order
     * @return static
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the reporting menu groups this category should be shown in.
     *
     * @param string[] $groups
     * @return static
     */
    public function setGroups(array $groups)
    {
        $this->groups = array_values(array_unique(array_map('strval', $groups)));
        return $this;
    }

    /**
     * Returns the reporting menu groups this category is shown in. Falls back to the default Analytics
     * group when no explicit group was set, so every category always belongs to at least one group.
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->groups ?: array(self::DEFAULT_GROUP);
    }

    public function getDisplayName()
    {
        return Piwik::translate($this->getId());
    }

    public function addSubcategory(Subcategory $subcategory)
    {
        $subcategoryId = $subcategory->getId();

        if ($this->hasSubcategory($subcategoryId)) {
            throw new \Exception(sprintf('Subcategory %s already exists for category %s', $subcategoryId, $this->getId()));
        }

        $this->subcategories[$subcategoryId] = $subcategory;
    }

    public function hasSubcategory($subcategoryId)
    {
        return isset($this->subcategories[$subcategoryId]);
    }

    public function getSubcategory($subcategoryId)
    {
        if ($this->hasSubcategory($subcategoryId)) {
            return $this->subcategories[$subcategoryId];
        }
    }

    /**
     * @return Subcategory[]
     */
    public function getSubcategories()
    {
        return array_values($this->subcategories);
    }

    public function hasSubCategories()
    {
        return !empty($this->subcategories);
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setWidget(string $widget): self
    {
        $this->widget = $widget;
        return $this;
    }

    public function getWidget(): string
    {
        return $this->widget;
    }

    /**
     * Get the help text (if any) for this category.
     * @return null|string
     */
    public function getHelp()
    {
        return null;
    }
}
