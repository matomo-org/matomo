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
    public const VERSION = '5.2.0-alpha';

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
        return (bool) preg_match('/^\d+\.\d+\.\d+(-((rc|b|beta)\d+|alpha)(\.\d{14})?)$/i', $version);
    }

    public function isPreviewVersion($version): bool
    {
        if ($this->isNonStableVersion($version)) {
            if (\preg_match('/\.(\d{14})$/', $version, $matches)) {
                $dt = DateTime::createFromFormat('YmdHis', $matches[1]);

                return false !== $dt && !\array_sum(array_map('intval', (array) $dt::getLastErrors()));
            }
        }

        return false;
    }

    public function nextPreviewVersion($version): string
    {
        if (!$this->isVersionNumber($version)) {
            return '';
        }

        $dt = date('Ymdhis');

        if ($this->isPreviewVersion($version)) {
            // already a preview, update dt and check it's newer
            $newVersion = substr($version, 0, -14) . $dt;
            if (version_compare($version, $newVersion, '<')) {
                return $newVersion;
            }
            return '';
        } elseif ($this->isStableVersion($version)) {
            // no suffix yet
            return $version . '-alpha.' . $dt;
        } else {
            // -b1, -rc1, -alpha
            return $version . '.' . $dt;
        }
    }
}
