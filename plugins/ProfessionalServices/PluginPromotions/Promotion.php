<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\PromotionTrigger;

/**
 * Definition of one contextual plugin promotion: which premium plugin it promotes, how
 * important it is relative to the others, and which trigger decides whether it applies.
 *
 * Several promotions may promote the same plugin through different triggers. Dismissal
 * cooldowns are keyed on the plugin name, so dismissing one of them suppresses all of
 * them, while the trigger name is kept separate for campaign attribution.
 */
class Promotion
{
    private int $priority;

    private string $pluginName;

    private string $productNameTranslationKey;

    private PromotionTrigger $trigger;

    private string $campaignContent;

    private string $translationKeyPrefix;

    private string $imageName;

    public function __construct(
        int $priority,
        string $pluginName,
        string $productNameTranslationKey,
        PromotionTrigger $trigger,
        string $campaignContent,
        string $translationKeyPrefix,
        string $imageName
    ) {
        $this->priority = $priority;
        $this->pluginName = $pluginName;
        $this->productNameTranslationKey = $productNameTranslationKey;
        $this->trigger = $trigger;
        $this->campaignContent = $campaignContent;
        $this->translationKeyPrefix = $translationKeyPrefix;
        $this->imageName = $imageName;
    }

    /**
     * Lower number means higher priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Name of the promoted plugin as it is known on the Marketplace, eg. `CustomReports`.
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }

    /**
     * Translation key for the product name shown to the user, eg. `Custom Reports`. Kept
     * here rather than read from the Marketplace so that rendering a dashboard never
     * depends on a network request.
     */
    public function getProductNameTranslationKey(): string
    {
        return $this->productNameTranslationKey;
    }

    public function getTrigger(): PromotionTrigger
    {
        return $this->trigger;
    }

    public function getTriggerName(): string
    {
        return $this->trigger->getName();
    }

    /**
     * Value sent as `mtm_content` on the outbound campaign link.
     */
    public function getCampaignContent(): string
    {
        return $this->campaignContent;
    }

    public function getTitleTranslationKey(): string
    {
        return $this->translationKeyPrefix . 'Title';
    }

    public function getTextTranslationKey(): string
    {
        return $this->translationKeyPrefix . 'Text';
    }

    public function getReasonTranslationKey(): string
    {
        return $this->translationKeyPrefix . 'Reason';
    }

    public function getImageName(): string
    {
        return $this->imageName;
    }
}
