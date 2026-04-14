<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

use Piwik\Attributes\Permission;

class API extends \Piwik\Plugin\API
{
    public static $wasMetadataOnlyMethodExecuted = false;

    public function getCustomAnswerToLive($truth = true)
    {
        if ($truth) {
            return 42;
        }

        return 24;
    }

    /**
     * @matomo-permission someView
     */
    #[Permission('someView')]
    public function getPermissionMetadataOnlyResult()
    {
        self::$wasMetadataOnlyMethodExecuted = true;

        return 'permission metadata proof';
    }
}
