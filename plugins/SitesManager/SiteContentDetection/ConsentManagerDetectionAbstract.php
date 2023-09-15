<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

abstract class ConsentManagerDetectionAbstract extends SiteContentDetectionAbstract
{
    final public static function getContentType(): string
    {
        return self::TYPE_CONSENT_MANAGER;
    }

    /**
     * Returns if the consent manager was already connected to Matomo
     *
     * @param string|null $data
     * @param array|null $headers
     * @return bool
     */
    abstract public function checkIsConnected(?string $data = null, ?array $headers = null): bool;
}
