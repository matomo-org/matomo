<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

abstract class SiteContentDetectionAbstract
{
    public const TYPE_TRACKER = 1;
    public const TYPE_CMS = 2;
    public const TYPE_JS_FRAMEWORK = 3;
    public const TYPE_CONSENT_MANAGER = 4;
    public const TYPE_OTHER = 99;
    public function __construct()
    {
    }

    public static function getId(): string
    {
        $classParts = explode('\\', static::class);
        return end($classParts);
    }

    /**
     * Returns the Name of this detection (e.g. name of CMS, Framework, ...)
     *
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Returns the content type this detection provides
     * May be one of TYPE_TRACKER, TYPE_CMS, TYPE_JS_FRAMEWORK, TYPE_CONSENT_MANAGER
     *
     * @return string
     */
    abstract public static function getContentType(): string;

    /**
     * Returns the URL to the instruction FAQ on how to integrate Matomo (if applicable)
     *
     * @return string|null
     */
    public static function getInstructionUrl(): ?string
    {
        return null;
    }

    /**
     * Returns the priority the tab should be displayed with.
     *
     * @return int
     */
    public static function getPriority(): int
    {
        return 1000;
    }

    /**
     * Returns if the current detection succeeded for the provided site content or not.
     *
     * @param string|null $data
     * @param array|null $headers
     * @return bool
     */
    abstract public function detectSiteByContent(?string $data = null, ?array $headers = null): bool;

    /**
     * Returns whether the instruction tab should be shown. Default behavior is to show it if the detection was successful
     *
     * @param array $detections
     * @return bool
     */
    public function shouldShowInstructionTab(array $detections = []): bool
    {
        return isset($detections[static::getContentType()]) && in_array(static::getId(), $detections[static::getContentType()]);
    }

    /**
     * Returns the content that should be rendered into a new Tab on the no data page
     *
     * @param array $detections
     * @return string|null
     */
    public function renderInstructionsTab(array $detections = []): ?string
    {
        return null;
    }

    /**
     * Returns the content that should be displayed in the Others tab on the no data page
     *
     * @return string|null
     */
    public function renderOthersInstruction(): ?string
    {
        return null;
    }
}