<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class EventsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Events_Events';
    protected $order = 40;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Events_EventsSubcategoryHelp1') . '</p>'
            . '<p><a href="https://matomo.org/docs/event-tracking/?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=UserGuides" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Events_EventsSubcategoryHelp2') . '</a></p>';
    }
}
