<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Categories;

use Piwik\Category\Category;
use Piwik\Piwik;

class ReferrersCategory extends Category
{
    protected $id = 'Referrers_Referrers';
    protected $order = 15;
    protected $icon = 'icon-reporting-referer';

    public function getDisplayName()
    {
        return Piwik::translate('Referrers_Acquisition');
    }
}
