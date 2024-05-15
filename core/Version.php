<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use DateTime;

/**
 * Matomo version information.
 *
 * @api
 */
final class Version
{
    /**
     * The current Matomo version.
     * @var string
     */
    public const VERSION = '5.1.0-b4';

    public const MAJOR_VERSION = 5;

    public function isStableVersion($version): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    public function isVersionNumber($version): bool
    {
        return
            $this->isStableVersion($version) ||
            $this->isNonStableVersion($version) ||
            $this->isPreviewVersion($version);
    }

    private function isNonStableVersion($version): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+((-.{1,4}\d+(\.\d{14})?)|(-alpha\.\d{14}))$/i', $version);
    }

    public function isPreviewVersion($version): bool
    {
        if (\preg_match('/^\d+\.\d+\.\d+((-(rc|b|beta)\d+(\.\d{14})?)|(-alpha\.\d{14}))?$/i', $version)) {
            if (\preg_match('/\.(\d{14})$/', $version, $matches)) {
                $dt = DateTime::createFromFormat('YmdHis', $matches[1]);

                return false !== $dt && !\array_sum(array_map('intval', (array) $dt::getLastErrors()));
            }
        }

        return false;
    }
}
