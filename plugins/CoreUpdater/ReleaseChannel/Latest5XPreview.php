<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\ReleaseChannel;

use Piwik\Plugins\CoreUpdater\ReleaseChannel;

class Latest5XPreview extends ReleaseChannel
{
    public function getId()
    {
        return 'latest_5x_preview';
    }

    public function getName()
    {
        return 'latest_5x_preview';
    }

    public function doesPreferStable()
    {
        return false;
    }

    public function getOrder()
    {
        return 31;
    }

    public function isSelectableInSettings(): bool
    {
        return false;
    }
}
