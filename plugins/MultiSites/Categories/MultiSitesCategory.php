<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites\Categories;

use Piwik\Category\Category;

class MultiSitesCategory extends Category
{
    protected $id = 'General_MultiSitesSummary';
    protected $order = 3;
}
