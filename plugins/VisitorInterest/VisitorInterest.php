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
    function postLoad()
    {
        Piwik::addAction('Template.footerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'footerVisitsFrequency'));
    }

   public static function footerVisitsFrequency(&$out)
    {
        $out .= FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
    }
}
