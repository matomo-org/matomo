<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin\Widgets;

use Piwik\Common;
use Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

class GetNewPlugins extends Widget
{
    /**
     * @var MarketplaceApiClient
     */
    private $marketplaceApiClient;

    public function __construct(MarketplaceApiClient $marketplaceApiClient)
    {
        $this->marketplaceApiClient = $marketplaceApiClient;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Piwik');
        // TODO it actually shows "recently updated plugins currently". Need a new sort filter in the Marketplace
        // TODO when decided whether to show new plugins or recently updated plugins add translation key
        // we want to show new plugins likely (when changed Marketplace to support actually "newest" plugins)
        $config->setName('Latest Marketplace Updates');
        $config->setOrder(19);
    }

    public function render()
    {
        $isAdminPage = Common::getRequestVar('isAdminPage', 0, 'int');

        if (!empty($isAdminPage)) {
            $template = 'getNewPluginsAdmin';
        } else {
            $template = 'getNewPlugins';
        }

        $plugins = $this->marketplaceApiClient->searchForPlugins('', '', 'newest');

        return $this->renderTemplate($template, array(
            'plugins' => array_splice($plugins, 0, 3)
        ));
    }

}