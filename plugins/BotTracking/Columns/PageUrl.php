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

class PageUrl extends Dimension
{
    protected $nameSingular = 'BotTracking_PageUrl';
    protected $type = self::TYPE_URL;
}
