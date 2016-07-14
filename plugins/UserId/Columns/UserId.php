<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;

/**
 * UserId dimension
 */
class UserId extends VisitDimension
{

    /**
     * The name of the dimension which will be visible for instance in the UI of a related report and in the mobile app.
     * @return string
     */
    public function getName()
    {
        return Piwik::translate('UserId_UserId');
    }

}