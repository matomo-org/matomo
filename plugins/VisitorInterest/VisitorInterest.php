<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\ArchiveProcessor;
use Piwik\FrontController;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

class VisitorInterest extends \Piwik\Plugin
{

    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Live.getAllVisitorDetails' => 'extendVisitorDetails',
        );
    }

    function postLoad()
    {
        Piwik::addAction('Template.footerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'footerVisitsFrequency'));
    }

   public static function footerVisitsFrequency(&$out)
    {
        $out .= FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $visitor['daysSinceLastVisit'] = $details['visitor_days_since_last'];
    }

}
