<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace\Widgets;

use Piwik\Common;
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
        $config->setCategoryId('About Piwik');
        $paidOnly = Common::getRequestVar('paidOnly', 0, 'int');
        if ($paidOnly) {
            $config->setName('Latest Premium Features');
        } else {
            $config->setName('Latest Marketplace Updates');
        }
        $config->setOrder(19);
    }

    public function render()
    {
        $paidOnly = Common::getRequestVar('paidOnly', 0, 'int');
        $isAdminPage = Common::getRequestVar('isAdminPage', 0, 'int');

        if (!empty($paidOnly)) {
            $purchaseType = PurchaseType::TYPE_PAID;
        } else {
            $purchaseType = PurchaseType::TYPE_ALL;
        }

        if (!empty($isAdminPage)) {
            $template = 'getNewPluginsAdmin';
        } else {
            $template = 'getNewPlugins';
        }

        $plugins = $this->marketplaceApiClient->searchForPlugins('', '', Sort::METHOD_LAST_UPDATED, $purchaseType);

        return $this->renderTemplate($template, array(
            'plugins' => array_splice($plugins, 0, 3)
        ));
    }

}