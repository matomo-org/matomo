<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\ProfileSummary;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Plugins\Goals\API as APIGoals;

/**
 * Class ProfileSummaryAbstract
 *
 * This class can be implemented in a plugin to provide a new profile summary
 *
 * @api
 */
class Summary extends ProfileSummaryAbstract
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Piwik::translate('General_Summary');
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $idSite            = Common::getRequestVar('idSite', null, 'int');
        $view              = new View('@Live/_profileSummary.twig');
        $view->goals       = APIGoals::getInstance()->getGoals($idSite);
        $view->visitorData = $this->profile;
        return $view->render();
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 0;
    }
}