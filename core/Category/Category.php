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
 * Categories of plugins will be automatically displayed in the menu at the very right after all core categories.
 * To define a category, place a subclass within the `Categories` folder of your plugin.
 *
 * @api
 */
class Category
{
    /**
     * Identifier of the default reporting menu group (the main "Analytics" reporting menu). Categories
     * that do not declare an explicit group belong to this group and are shown in the main reporting menu.
     */
    public const DEFAULT_GROUP = '';

    /**
     * The id of the category as specified eg in {@link Piwik\Widget\WidgetConfig::setCategoryId()} or
     * {@link Piwik\Plugin\Report::getCategoryId()}. The id is used as the name in the menu and will be visible in the
     * URL. Should be a translation key, eg 'General_Visits'.
     *
     * @var string
     * @api
     */
    protected $id = '';

    /**
     * The reporting menu groups (top-level menu sections) this category is shown in. An empty list means
     * the category is shown in the default Analytics reporting menu only. A category may belong to more
     * than one group, e.g. to be shown both in Analytics and in a dedicated section such as "AI Insights"
     * during a transition. Group ids should be translation keys so they can be used as the section label.
     *
     * @var string[]
     * @api
     */
    protected $groups = array();

    /**
     * Subset of {@link $groups} for which this category does not require tracked data, i.e. that should
     * not trigger the "site has no data" tracker-setup screen.
     *
     * @var string[]
     */
    protected $groupsWithoutTrackingRequirement = array();

    /**
     * The subcategories added to this category, indexed by subcategory id.
     *
     * @var Subcategory[]
     */
    protected $subcategories = array();

    /**
     * The order of the category. The lower the value the further left the category will appear in the menu.
     * @var int
     * @api
     */
    protected $order = 99;

    /**
     * The icon for this category, eg 'icon-user'
     * @var string
     * @api
     */
    protected $icon = '';

    /**
     * Optional widget spec to replace the category in the reporting menu, e.g. Marketplace.RichMenuButton
     *
     * @var string
     * @api
     */
    protected $widget = '';

    /**
     * Sets (overwrites) the order of the category, see {@link $order}.
     *
     * @param int $order The order; the lower the value, the further left the category appears.
     * @return static This category, for method chaining.
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;
        return $this;
    }

    /**
     * Returns the order of the category.
     *
     * @return int The order of the category.
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets (overwrites) the id of the category, see {@link $id}.
     *
     * @param string $id A translation key, eg 'General_Visits'.
     * @return static This category, for method chaining.
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the id of the category, a translation key such as `'General_Visits'`.
     *
     * @return string The category id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the reporting menu groups this category should be shown in.
     *
     * @param string[] $groups The group ids; duplicates are removed.
     * @return static This category, for method chaining.
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
     * @return string[] The group ids.
     */
    public function getGroups(): array
    {
        return $this->groups ?: array(self::DEFAULT_GROUP);
    }

    /**
     * Sets the reporting menu groups for which this category does not require tracked data.
     *
     * @param string[] $groups The group ids; duplicates are removed.
     * @return static This category, for method chaining.
     *
     * @internal
     */
    public function setGroupsWithoutTrackingRequirement(array $groups)
    {
        $this->groupsWithoutTrackingRequirement = array_values(array_unique(array_map('strval', $groups)));
        return $this;
    }

    /**
     * Returns the reporting menu groups for which this category does not require tracked data.
     *
     * @return string[] The group ids.
     *
     * @internal
     */
    public function getGroupsWithoutTrackingRequirement(): array
    {
        return $this->groupsWithoutTrackingRequirement;
    }

    /**
     * Returns the translated name of the category.
     *
     * @return string The translated category name.
     */
    public function getDisplayName()
    {
        return Piwik::translate($this->getId());
    }

    /**
     * Adds a subcategory to this category. Throws if a subcategory with the same id was already added.
     *
     * @param Subcategory $subcategory The subcategory to add.
     *
     * @internal
     */
    public function addSubcategory(Subcategory $subcategory)
    {
        $subcategoryId = $subcategory->getId();

        if ($this->hasSubcategory($subcategoryId)) {
            throw new \Exception(sprintf('Subcategory %s already exists for category %s', $subcategoryId, $this->getId()));
        }

        $this->subcategories[$subcategoryId] = $subcategory;
    }

    /**
     * Returns whether a subcategory with the given id exists.
     *
     * @param string $subcategoryId The subcategory id.
     * @return bool True if the subcategory exists, false otherwise.
     *
     * @internal
     */
    public function hasSubcategory($subcategoryId)
    {
        return isset($this->subcategories[$subcategoryId]);
    }

    /**
     * Returns the subcategory with the given id, or nothing if there is none.
     *
     * @param string $subcategoryId The subcategory id.
     * @return Subcategory|void The subcategory, if it exists.
     *
     * @internal
     */
    public function getSubcategory($subcategoryId)
    {
        if ($this->hasSubcategory($subcategoryId)) {
            return $this->subcategories[$subcategoryId];
        }
    }

    /**
     * Returns all subcategories of this category.
     *
     * @return Subcategory[] The subcategories, in the order they were added.
     *
     * @internal
     */
    public function getSubcategories()
    {
        return array_values($this->subcategories);
    }

    /**
     * Returns whether this category has any subcategories.
     *
     * @return bool True if at least one subcategory was added, false otherwise.
     *
     * @internal
     */
    public function hasSubCategories()
    {
        return !empty($this->subcategories);
    }

    /**
     * Sets (overwrites) the icon of the category, see {@link $icon}.
     *
     * @param string $icon The CSS class of the icon, eg 'icon-user'.
     * @return static This category, for method chaining.
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Returns the icon of the category.
     *
     * @return string The icon class.
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets (overwrites) the widget of the category, see {@link $widget}.
     *
     * @param string $widget The widget spec, eg 'Marketplace.RichMenuButton'.
     * @return static This category, for method chaining.
     */
    public function setWidget(string $widget): self
    {
        $this->widget = $widget;
        return $this;
    }

    /**
     * Returns the widget of the category.
     *
     * @return string The widget spec, or an empty string if none is set.
     */
    public function getWidget(): string
    {
        return $this->widget;
    }

    /**
     * Get the help text (if any) for this category.
     *
     * @return null|string The help text, or null if there is none.
     */
    public function getHelp()
    {
        return null;
    }
}
