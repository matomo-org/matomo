<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Columns;

use Piwik\Columns\Dimension;

/**
 * Neutral "URL" dimension used by the Broken Content report which mixes page and document URLs.
 * Unlike PageUrl (TYPE_PAGE_URL only) and DocumentUrl (TYPE_DOWNLOAD only), this dimension
 * carries no action-type filter so the column header fits a mixed page + document URL list.
 */
class ContentUrl extends Dimension
{
    protected $nameSingular = 'BotTracking_ContentUrl';
    protected $type = self::TYPE_URL;
}
