<?php

declare(strict_types=1);

namespace Piwik\Policy\Retention;

/**
 * This is the sources where the setting value for the retention period.
 * This is used to actually do the retrieving for each source type (ENUM???)
 */
final class RetentionSources
{
    public function config(): ?int
    {
        // TODO: read from config/defaults
        return null;
    }

    public function site(?int $idSite): ?int
    {
        // TODO: read from site-specific setting; return null if not set.
        return null;
    }
}
