<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Widgets;

use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Input\PurchaseType;
use Piwik\Plugins\Marketplace\Input\Sort;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetPremiumFeatures extends Widget
{
    private Client $marketplaceApiClient;

    public function __construct(Client $marketplaceApiClient)
    {
        $this->marketplaceApiClient = $marketplaceApiClient;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Marketplace_Marketplace');
        $config->setSubcategoryId('Marketplace_PaidPlugins');
        $config->setName('Marketplace_PaidPlugins');
        $config->setOrder(20);
        $config->setIsEnabled(!Piwik::isUserIsAnonymous());
    }

    public function render()
    {
        Piwik::checkUserIsNotAnonymous();
        $template = 'getPremiumFeatures';

        $plugins = $this->marketplaceApiClient->searchForPlugins('', '', Sort::METHOD_LAST_UPDATED, PurchaseType::TYPE_PAID);

        //sort array by bundle first
        usort($plugins, function ($item1, $item2) {
            // a plugin without the flag at all sorts as not a bundle rather than raising a warning
            return !empty($item1['isBundle']) < !empty($item2['isBundle']) ? 1 : -1;
        });

        if (empty($plugins)) {
            $plugins = array();
        } else {
            $plugins = array_splice($plugins, 0, 20);
        }

        return $this->renderTemplate($template, array(
            'plugins' => $this->keepRenderedFields($plugins),
        ));
    }

    /**
     * Reduces each plugin to the fields this widget renders.
     *
     * The Marketplace returns every version of every plugin, each carrying its own rendered readme
     * and FAQ HTML. All of it would be json_encoded into an attribute in the widget's HTML, where it
     * cannot be cached: against plugins.matomo.org that is 470 KB for twenty plugins instead of 5 KB.
     *
     * @param array<int, array<string, mixed>> $plugins
     * @return array<int, array<string, mixed>>
     */
    private function keepRenderedFields(array $plugins): array
    {
        $fields = array_flip(['name', 'displayName', 'description', 'isBundle', 'specialOffer']);

        return array_map(function ($plugin) use ($fields) {
            return array_intersect_key($plugin, $fields);
        }, $plugins);
    }
}
