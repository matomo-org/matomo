<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Menu;

/**
 * Contains menu entries for the AI Insights menu.
 * Plugins can implement the `configureAiInsightsMenu()` method of the `Menu` plugin class to add, rename or remove
 * categories and submenu items. If your plugin does not have a `Menu` class yet you can create one using
 * `./console generate:menu`.
 *
 * @method static \Piwik\Menu\MenuAi getInstance()
 */
class MenuAi extends MenuAbstract
{
    /**
     * Cached result of {@link getDefaultUrl()}. The sentinel `false` means "not resolved yet",
     * which is distinct from a resolved value of `null` (no usable URL in the menu).
     *
     * @var array|string|null|false
     */
    private $defaultUrl = false;

    /**
     * Triggers the AI Insights menu hook and returns the menu.
     */
    public function getMenu(): array
    {
        if ($this->menu === null) {
            foreach ($this->getAllMenus() as $menu) {
                $menu->configureAiInsightsMenu($this);
            }
        }

        $menu = parent::getMenu();
        if (empty($menu)) {
            $this->menu = [];
            return [];
        }

        return $menu;
    }

    /**
     * Returns the first usable submenu URL in the AI Insights menu.
     *
     * The result is memoized because it is requested on every page load (the top menu uses it
     * to decide whether to show the "AI Insights" entry) as well as by every AI Insights page,
     * and walking the built menu more than once per request is wasteful.
     *
     * @return array|string|null
     */
    public function getDefaultUrl()
    {
        if ($this->defaultUrl !== false) {
            return $this->defaultUrl;
        }

        $this->defaultUrl = null;

        foreach ($this->getMenu() as $menuItem) {
            if (!is_array($menuItem)) {
                continue;
            }

            foreach ($menuItem as $subMenuName => $subMenuItem) {
                if (
                    strpos((string) $subMenuName, '_') === 0
                    || !is_array($subMenuItem)
                    || !isset($subMenuItem['_url'])
                    || !$this->isUsableUrl($subMenuItem['_url'])
                ) {
                    continue;
                }

                $this->defaultUrl = $subMenuItem['_url'];
                return $this->defaultUrl;
            }
        }

        return $this->defaultUrl;
    }

    /**
     * @param mixed $url
     */
    private function isUsableUrl($url): bool
    {
        if (is_array($url)) {
            return !empty($url);
        }

        return is_string($url) && $url !== '';
    }
}
