<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;

/**
 * @ignore
 */
class Group
{
    private $items;

    public function add($subTitleMenu, $url, $tooltip = false)
    {
        $this->items[] = array(
            'name' => $subTitleMenu,
            'url'  => $url,
            'tooltip' => $tooltip
        );;
    }

    public function getItems()
    {
        return $this->items;
    }
}
