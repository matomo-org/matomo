<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Widgets;

use Piwik\API\Request;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\SegmentEditor\Services\StoredSegmentService;
use Piwik\Version;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

class GetSystemSummary extends Widget
{
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
        $config->setCategoryId('About Piwik');
        $config->setName('CoreHome_SystemSummaryWidget');
        $config->setOrder(15);
        $config->setIsEnabled(Piwik::hasUserSuperUserAccess());
    }

    public function render()
    {
        $users = Request::processRequest('UsersManager.getUsers', array('filter_limit' => '-1'));
        $websites = Request::processRequest('SitesManager.getAllSites', array('filter_limit' => '-1'));

        return $this->renderTemplate('getSystemSummary', array(
            'numWebsites' => count($websites),
            'numUsers' => count($users),
            'numSegments' => $this->getNumSegments(),
            'numPlugins' => $this->getNumPlugins(),
            'piwikVersion' => Version::VERSION,
            'mySqlVersion' => $this->getMySqlVersion(),
            'phpVersion' => phpversion()
        ));
    }

    private function getNumSegments()
    {
        $segments = $this->storedSegmentService->getAllSegmentsAndIgnoreVisibility();
        return count($segments);
    }

    private function getMySqlVersion()
    {
        $db = Db::get();
        return $db->getServerVersion();
    }

    private function getNumPlugins()
    {
        return count($this->pluginManager->getActivatedPlugins());
    }
}