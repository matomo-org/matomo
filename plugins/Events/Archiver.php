<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Actions
 */
namespace Piwik\Plugins\Events;

use Piwik\DataTable;

/**
 * Processing reports for Events
 *
 * @package Events
 */
class Archiver //extends \Piwik\Plugin\Archiver
{
    const EVENTS_CATEGORY_RECORD_NAME = 'Events_category';
    const EVENTS_ACTION_RECORD_NAME = 'Events_action';
    const EVENTS_NAME_RECORD_NAME = 'Events_name';

}
