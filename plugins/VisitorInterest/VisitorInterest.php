<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\FrontController;
use Piwik\Piwik;

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
