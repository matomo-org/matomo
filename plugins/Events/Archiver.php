<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Events;

/**
 * Processing reports for Events

    EVENT
    - Category
    - Action
    - Name
    - Value

    METRICS (Events Overview report)
    - Total number of events
    - Unique events
    - Visits with events
    - Events/visit
    - Event value
    - Average event value AVG(custom_float)

    MAIN REPORTS:
    - Top Event Category (total events, unique events, event value, avg+min+max value)
    - Top Event Action   (total events, unique events, event value, avg+min+max value)
    - Top Event Name     (total events, unique events, event value, avg+min+max value)

    COMPOSED REPORTS
    - Top Category > Actions     X
    - Top Category > Names       X
    - Top Actions  > Categories  X
    - Top Actions  > Names       X
    - Top Names    > Actions     X
    - Top Names    > Categories  X

    UI
    - Overview at the top (graph + Sparklines)
    - Below show the left menu, defaults to Top Event Category

    Not MVP:
    - On hover on any row: Show % of total events
    - Add min value metric, max value metric in tooltip
    - List event scope Custom Variables Names > Custom variables values > Event Names > Event Actions
    - List event scope Custom Variables Value > Event Category > Event Names > Event Actions

    NOTES:
    - For a given Name, Category is often constant

 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const EVENTS_CATEGORY_ACTION_RECORD_NAME = 'Events_category_action';
    const EVENTS_CATEGORY_NAME_RECORD_NAME = 'Events_category_name';
    const EVENTS_ACTION_CATEGORY_RECORD_NAME = 'Events_action_category';
    const EVENTS_ACTION_NAME_RECORD_NAME = 'Events_action_name';
    const EVENTS_NAME_ACTION_RECORD_NAME = 'Events_name_action';
    const EVENTS_NAME_CATEGORY_RECORD_NAME = 'Events_name_category';
    const EVENT_NAME_NOT_SET = 'Piwik_EventNameNotSet';
}
