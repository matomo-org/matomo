<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugin\Manager;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;

/**
 * Triggers when the current user can access at least five segments that apply to the
 * currently selected website.
 *
 * Evaluated against the current state rather than a report, so it is not cached: a
 * segment created at 10:00 should be reflected straight away.
 */
class SegmentsTrigger implements PromotionTrigger
{
    public const NAME = 'segments';

    public const MINIMUM_SEGMENTS = 5;

    private Manager $pluginManager;

    public function __construct(Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        if (!$this->pluginManager->isPluginActivated('SegmentEditor')) {
            return TriggerResult::notTriggered();
        }

        // getAll() already limits the result to segments that apply to this site and that
        // the current user may access, and drops segments relying on disabled plugins.
        $numSegments = count(SegmentEditorApi::getInstance()->getAll($idSite));

        if ($numSegments < self::MINIMUM_SEGMENTS) {
            return TriggerResult::notTriggered();
        }

        return TriggerResult::triggered(['count' => $numSegments]);
    }
}
