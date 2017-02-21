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
        $userLogins = Request::processRequest('UsersManager.getUsersLogin', array('filter_limit' => '-1'));
        $websites = Request::processRequest('SitesManager.getAllSites', array('filter_limit' => '-1'));

        $numUsers = count($userLogins);
        if (in_array('anonymous', $userLogins)) {
            $numUsers--;
        }

        return $this->renderTemplate('getSystemSummary', array(
            'numWebsites' => count($websites),
            'numUsers' => $numUsers,
            'numSegments' => $this->getNumSegments(),
            'numPlugins' => $this->getNumActivatedPlugins(),
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

    private function getNumActivatedPlugins()
    {
        return $this->pluginManager->getNumberOfActivatedPluginsExcludingAlwaysActivated();
    }
}