<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\ProfileSummary;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;

/**
 * Class Summary
 *
 * Displays some general details in the profile summary
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
        $view->goals       = Request::processRequest('Goals.getGoals', ['idSite' => $idSite, 'filter_limit' => '-1'], $default = []);
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