<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_Menu
 */
namespace Piwik\Menu;

use Piwik\Menu\MenuAbstract;

/**
 * @package Piwik_Menu
 */
class Main extends MenuAbstract
{
    static private $instance = null;

    /**
     * @return MenuAbstract
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Returns if the URL was found in the menu.
     *
     * @param string $url
     * @return boolean
     */
    public function isUrlFound($url)
    {
        $menu = Main::getInstance()->get();

        foreach ($menu as $subMenus) {
            foreach ($subMenus as $subMenuName => $menuUrl) {
                if (strpos($subMenuName, '_') !== 0 && $menuUrl['_url'] == $url) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Triggers the Menu.Reporting.addItems hook and returns the menu.
     *
     * @return Array
     */
    public function get()
    {
        // We trigger the Event only once!
        if (!$this->menu) {
            Piwik_PostEvent('Menu.Reporting.addItems');
        }
        return parent::get();
    }
}

