<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetNewPlugins extends Widget
{
    /**
     * @var Client
     */
    private $marketplaceApiClient;

    public function __construct(Client $marketplaceApiClient)
    {
        $this->marketplaceApiClient = $marketplaceApiClient;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Marketplace_Marketplace');
        $config->setName('Marketplace_LatestMarketplaceUpdates');
        $config->setOrder(19);
        $config->setIsEnabled(!Piwik::isUserIsAnonymous());
    }

    public function render()
    {
        Piwik::checkUserIsNotAnonymous();

        $isAdminPage = Common::getRequestVar('isAdminPage', 0, 'int');

        if (!empty($isAdminPage)) {
            $template = 'getNewPluginsAdmin';
        } else {
            $template = 'getNewPlugins';
        }

        $plugins = $this->marketplaceApiClient->searchForPlugins('', '', Sort::METHOD_LAST_UPDATED, PurchaseType::TYPE_ALL);

        $plugins = array_filter($plugins, function ($plugin) {
            return empty($plugin['isBundle']);
        });

        return $this->renderTemplate($template, array(
            'plugins' => $this->keepRenderedFields(array_splice($plugins, 0, 3), $template),
        ));
    }

    /**
     * Reduces each plugin to the fields the given template renders.
     *
     * Everything else is version history and rendered readme HTML, which would be json_encoded into
     * an attribute in the widget's own HTML, where it cannot be cached.
     *
     * @param array<int, array<string, mixed>> $plugins
     * @return array<int, array<string, mixed>>
     */
    private function keepRenderedFields(array $plugins, string $template): array
    {
        $rendered = ['name', 'displayName', 'description'];

        if ($template === 'getNewPluginsAdmin') {
            // only the admin template shows a screenshot, and they are the largest field here
            $rendered[] = 'screenshots';
        }

        $fields = array_flip($rendered);

        return array_map(function ($plugin) use ($fields) {
            return array_intersect_key($plugin, $fields);
        }, $plugins);
    }
}
