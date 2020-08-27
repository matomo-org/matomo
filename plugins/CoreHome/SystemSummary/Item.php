<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\SystemSummary;

/**
 * This class can be used to add a new entry / item to the system summary widget.
 *
 * @api
 */
class Item
{
    private $key;
    private $label;
    private $value;
    private $urlParams;
    private $icon;
    private $order;

    /**
     * Item constructor.
     * @param string $key  The key or ID for this item. The entry in the widget will have this class so it is possible
     *                     to style it individually and other plugins can use this key to for example remove this item
     *                     from the list of system summary items.
     * @param string $label  The label that will be displayed for this item. The label may already include the value such as "5 segments"
     * @param string|null $value Optional label. If given, the value will be displayed after the label separated by a colon, eg: "Segments: 5"
     * @param array|null $urlParams  Optional URL to make the item clickable. Accepts an array of URL parameters that need to be modfified.
     * @param string $icon  Optional icon css class, eg "icon-user".
     * @param int $order Optional sort order. The lower the value, the higher up the entry will be shown
     */
    public function __construct($key, $label, $value = null, $urlParams = null, $icon = '', $order = 99)
    {
        $this->key = $key;
        $this->label = $label;
        $this->value = $value;
        $this->urlParams = $urlParams;
        $this->icon = $icon;
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array|null
     */
    public function getUrlParams()
    {
        return $this->urlParams;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

}
