<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\API;

use InvalidArgumentException;

class APIVersion
{
    /**
     * @var int
     */
    private $majorVersion;

    /**
     * @var ?int
     */
    private $minorVersion;

    /**
     * @var ?int
     */
    private $patchVersion;

    public function __construct(int $majorVersion, ?int $minorVersion, ?int $patchVersion)
    {
        $this->majorVersion = $majorVersion;
        $this->minorVersion = $minorVersion;
        $this->patchVersion = $patchVersion;
    }

    public static function createFromVersionString(string $versionString): APIVersion
    {
        return new self(...self::extractVersions($versionString));
    }

    public function getMajorVersion(): int
    {
        return $this->majorVersion;
    }

    public function getMinorVersion(): ?int
    {
        return $this->minorVersion;
    }

    public function getPatchVersion(): ?int
    {
        return $this->patchVersion;
    }

    public function getClassString(string $pluginName): string // TODO - there's an annotation for strings representing classes
    {
        // So the idea is that consumers can specify a major version, which should always be backwards compatible,
        // and they get the latest copy of that major version.
        //
        // We still allow specifying at a more granular level.
        //
        // I was hoping to automatically figure out the version level, but I think it's cleaner and safer for
        // the plugin to maintain those links - e.g. \Plugin\API\2\API just inherits from \Plugin\API\2.3.4
        // manually.
        $version = 'V_' . strval($this->majorVersion);
        if (null !== $this->minorVersion) {
            $version .= '_' . strval($this->minorVersion);
            if (null !== $this->patchVersion) {
                $version .= '_' . strval($this->patchVersion);
            }
        }
        return sprintf('\Piwik\Plugins\%s\API\%s\API', $pluginName, $version);
    }

    private static function extractVersions(string $versionString): array
    {
        // Validate input: must be 1 to 3 dot-separated numbers
        if (!preg_match('/^\d+(\.\d+){0,2}$/', $versionString)) {
            throw new InvalidArgumentException("Invalid version string: $versionString");
        }

        // Split into parts
        $parts = explode('.', $versionString);
        assert(isset($parts[0]));

        // Assign major, minor, patch with null defaults
        $majorVersion = (int) $parts[0];
        $minorVersion = isset($parts[1]) ? (int) $parts[1] : null;
        $patchVersion = isset($parts[2]) ? (int) $parts[2] : null;

        return [$majorVersion, $minorVersion, $patchVersion];
    }
}
