<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\AIProviders\Model\Configuration;

class Menu extends \Piwik\Plugin\Menu
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        parent::__construct();

        $this->configuration = $configuration;
    }

    public function configureAdminMenu(MenuAdmin $menu): void
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        /**
         * In a managed environment the provider is
         * forced from configuration and there is nothing to configure, so the
         * settings page is hidden entirely.
         */
        if ($this->configuration->isManaged()) {
            return;
        }

        $menu->addSystemItem('AIProviders_MenuTitle', $this->urlForAction('index'), 36);
    }
}
