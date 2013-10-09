<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

// All classes and functions below are defined in the global namespace
namespace {

    use Piwik\DataTable;
    use Piwik\Menu\Admin;
    use Piwik\Menu\Main;
    use Piwik\Menu\Top;

    /**
     * Returns translated string or given message if translation is not found.
     *
     * @param string $string Translation string index
     * @param array|string|int $args sprintf arguments
     * @return string
     * @api
     */
    function Piwik_Translate($string, $args = array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }

        if (strpos($string, "_") !== false) {
            list($plugin, $key) = explode("_", $string, 2);
            if (isset($GLOBALS['Piwik_translations'][$plugin]) && isset($GLOBALS['Piwik_translations'][$plugin][$key])) {
                $string = $GLOBALS['Piwik_translations'][$plugin][$key];
            }
        }
        if (count($args) == 0) {
            return $string;
        }
        return vsprintf($string, $args);
    }

    /**
     * Returns translated string or given message if translation is not found.
     * This function does not throw any exception. Use it to translate exceptions.
     *
     * @param string $message Translation string index
     * @param array $args sprintf arguments
     * @return string
     * @api
     */
    function Piwik_TranslateException($message, $args = array())
    {
        try {
            return Piwik_Translate($message, $args);
        } catch (Exception $e) {
            return $message;
        }
    }

    /**
     * Returns the AdminMenu
     *
     * @return Array
     */
    function Piwik_GetAdminMenu()
    {
        return Admin::getInstance()->get();
    }

    /**
     * Adds a new AdminMenu entry.
     *
     * @param string $adminMenuName
     * @param string $url
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @api
     */
    function Piwik_AddAdminMenu($adminMenuName, $url, $displayedForCurrentUser = true, $order = 10)
    {
        Admin::getInstance()->add('General_Settings', $adminMenuName, $url, $displayedForCurrentUser, $order);
    }

    /**
     * Adds a new AdminMenu entry with a submenu.
     *
     * @param string $adminMenuName
     * @param string $adminSubMenuName
     * @param string $url
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @api
     */
    function Piwik_AddAdminSubMenu($adminMenuName, $adminSubMenuName, $url, $displayedForCurrentUser = true, $order = 10)
    {
        Admin::getInstance()->add($adminMenuName, $adminSubMenuName, $url, $displayedForCurrentUser, $order);
    }

    /**
     * Renames an AdminMenu entry.
     *
     * @param string $adminMenuOriginal
     * @param string $adminMenuRenamed
     */
    function Piwik_RenameAdminMenuEntry($adminMenuOriginal, $adminMenuRenamed)
    {
        Admin::getInstance()->rename($adminMenuOriginal, null, $adminMenuRenamed, null);
    }


    /**
     * Returns the MainMenu as array.
     *
     * @return array
     */
    function Piwik_GetMenu()
    {
        return Main::getInstance()->get();
    }

    /**
     * Adds a new entry to the MainMenu.
     *
     * @param string $mainMenuName
     * @param string $subMenuName
     * @param string $url
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @api
     */
    function Piwik_AddMenu($mainMenuName, $subMenuName, $url, $displayedForCurrentUser = true, $order = 10)
    {
        Main::getInstance()->add($mainMenuName, $subMenuName, $url, $displayedForCurrentUser, $order);
    }

    /**
     * Renames a menu entry.
     *
     * @param string $mainMenuOriginal
     * @param string $subMenuOriginal
     * @param string $mainMenuRenamed
     * @param string $subMenuRenamed
     */
    function Piwik_RenameMenuEntry($mainMenuOriginal, $subMenuOriginal,
                                   $mainMenuRenamed, $subMenuRenamed)
    {
        Main::getInstance()->rename($mainMenuOriginal, $subMenuOriginal, $mainMenuRenamed, $subMenuRenamed);
    }

    /**
     * Edits the URL of a menu entry.
     *
     * @param string $mainMenuToEdit
     * @param string $subMenuToEdit
     * @param string $newUrl
     */
    function Piwik_EditMenuUrl($mainMenuToEdit, $subMenuToEdit, $newUrl)
    {
        Main::getInstance()->editUrl($mainMenuToEdit, $subMenuToEdit, $newUrl);
    }

    /**
     * Returns the TopMenu as an array.
     *
     * @return array
     */
    function Piwik_GetTopMenu()
    {
        return Top::getInstance()->get();
    }

    /**
     * Adds a new entry to the TopMenu.
     *
     * @param string $topMenuName
     * @param string $data
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @param bool $isHTML
     * @param bool|string $tooltip Tooltip to display.
     * @api
     */
    function Piwik_AddTopMenu($topMenuName, $data, $displayedForCurrentUser = true, $order = 10, $isHTML = false,
                              $tooltip = false)
    {
        if ($isHTML) {
            Top::getInstance()->addHtml($topMenuName, $data, $displayedForCurrentUser, $order, $tooltip);
        } else {
            Top::getInstance()->add($topMenuName, null, $data, $displayedForCurrentUser, $order, $tooltip);
        }
    }

    /**
     * Renames a entry of the TopMenu
     *
     * @param string $topMenuOriginal
     * @param string $topMenuRenamed
     */
    function Piwik_RenameTopMenuEntry($topMenuOriginal, $topMenuRenamed)
    {
        Top::getInstance()->rename($topMenuOriginal, null, $topMenuRenamed, null);
    }

    // Bridge between pre Piwik2 serialized format and namespaced classes
    // Do not need to define these classes in tracker or archive
    if (class_exists('\\Piwik\\DataTable\\Row\\DataTableSummaryRow')) {
        class Piwik_DataTable_Row_DataTableSummary extends \Piwik\DataTable\Row\DataTableSummaryRow
        {
        }

        class Piwik_DataTable_Row extends \Piwik\DataTable\Row
        {
        }
    }

}
