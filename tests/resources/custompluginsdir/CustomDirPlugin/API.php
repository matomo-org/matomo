<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDirPlugin;

class API extends \Piwik\Plugin\API
{
    public function getCustomAnswerToLive($truth = true)
    {
        if ($truth) {
            return 42;
        }

        return 24;
    }

}
