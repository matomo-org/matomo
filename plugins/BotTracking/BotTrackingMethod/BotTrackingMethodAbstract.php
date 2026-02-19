<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

abstract class BotTrackingMethodAbstract
{
    abstract public static function getName(): string;

    abstract public static function getSiteContentDetectionId(): ?string;

    abstract public static function getPriority(): int;

    abstract public static function renderInstructionsTab(): string;

    public static function getLink(): ?string
    {
        return null;
    }

    public static function getIcon(): ?string
    {
        return null;
    }

    public static function getId(): string
    {
        $classParts = explode('\\', static::class);
        return end($classParts);
    }

    public static function isOthers(): bool
    {
        return false;
    }
}
