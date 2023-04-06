<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Widgets;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CoreHome\SystemSummary\Item;
use Piwik\Plugins\SegmentEditor\Services\StoredSegmentService;
use Piwik\Version;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetSystemSummary extends Widget
{
    const TEST_MYSQL_VERSION = 'mysql-version-redacted';
    const TEST_PHP_VERSION = 'php-version-redacted';
    /**
     * @var StoredSegmentService
     */
    private $storedSegmentService;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(StoredSegmentService $storedSegmentService, Plugin\Manager $pluginManager)
    {
        $this->storedSegmentService = $storedSegmentService;
        $this->pluginManager = $pluginManager;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('CoreHome_SystemSummaryWidget');
        $config->setOrder(15);
        $config->setIsEnabled(Piwik::hasUserSuperUserAccess());
    }

    public function render()
    {
        $mysqlVersion = $this->getMySqlVersion();
        $phpVersion = $this->getPHPVersion();

        $systemSummary = array();

        /**
         * Triggered to add system summary items that are shown in the System Summary widget.
         *
         * **Example**
         *
         *     public function addSystemSummaryItem(&$systemSummary)
         *     {
         *         $numUsers = 5;
         *         $systemSummary[] = new SystemSummary\Item($key = 'users', Piwik::translate('General_NUsers', $numUsers), $value = null, array('module' => 'UsersManager', 'action' => 'index'), $icon = 'icon-user');
         *     }
         *
         * @param Item[] &$systemSummary An array containing system summary items.
         */
        Piwik::postEvent('System.addSystemSummaryItems', array(&$systemSummary));

        $systemSummary[] = new Item($key = 'piwik-version', Piwik::translate('CoreHome_SystemSummaryPiwikVersion'), Version::VERSION, $url = null, $icon = '', $order = 21);
        $systemSummary[] = new Item($key = 'mysql-version', Piwik::translate('CoreHome_SystemSummaryMysqlVersion'), $mysqlVersion, $url = null, $icon = '', $order = 22);
        $systemSummary[] = new Item($key = 'php-version', Piwik::translate('CoreHome_SystemSummaryPhpVersion'), $phpVersion, $url = null, $icon = '', $order = 23);

        $systemSummary = array_filter($systemSummary);
        usort($systemSummary, function ($itemA, $itemB) {
            if ($itemA->getOrder() == $itemB->getOrder()) {
                return 0;
            }
            if ($itemA->getOrder() > $itemB->getOrder()) {
                return 1;
            }
            return -1;
        });

        /**
         * Triggered to filter system summary items that are shown in the System Summary widget. A plugin might also
         * sort the system summary items differently.
         *
         * **Example**
         *
         *     public function filterSystemSummaryItems(&$systemSummary)
         *     {
         *         foreach ($systemSummaryItems as $index => $item) {
         *             if ($item && $item->getKey() === 'users') {
         *                 $systemSummaryItems[$index] = null;
         *             }
         *         }
         *     }
         *
         * @param Item[] &$systemSummary An array containing system summary items.
         */
        Piwik::postEvent('System.filterSystemSummaryItems', array(&$systemSummary));

        $systemSummary = array_filter($systemSummary);

        return $this->renderTemplate('getSystemSummary', array(
            'items' => $systemSummary
        ));
    }

    private function getMySqlVersion()
    {
        if (defined('PIWIK_TEST_MODE')) {
            return self::TEST_MYSQL_VERSION;
        }

        $db = Db::get();
        return $db->getServerVersion();

    }

    private function getPHPVersion()
    {
        if (defined('PIWIK_TEST_MODE')) {
            return self::TEST_PHP_VERSION;
        }

        return phpversion();
    }

}
