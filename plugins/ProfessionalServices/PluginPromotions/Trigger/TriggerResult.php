<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

/**
 * Outcome of evaluating a single promotion trigger.
 *
 * Context values are the display values the promotion copy needs, eg. the number of
 * segments or the goal name. Report based triggers additionally record the reporting
 * period they looked at so a cached result can be traced back to it.
 */
class TriggerResult
{
    private bool $triggered;

    /**
     * @var array<string, mixed>
     */
    private array $context;

    private ?string $periodStart;

    private ?string $periodEnd;

    private bool $provisional;

    /**
     * @param array<string, mixed> $context
     */
    private function __construct(
        bool $triggered,
        array $context,
        ?string $periodStart,
        ?string $periodEnd,
        bool $provisional = false
    ) {
        $this->triggered = $triggered;
        $this->context = $context;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
        $this->provisional = $provisional;
    }

    public static function notTriggered(?string $periodStart = null, ?string $periodEnd = null): self
    {
        return new self(false, [], $periodStart, $periodEnd);
    }

    /**
     * The trigger could not tell yet, because the reports it reads have not finished being
     * archived. Not triggered, but for a reason that will stop being true on its own.
     *
     * Kept apart from {@see notTriggered()} because {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache}
     * remembers an answer for the rest of the day. "This website's data does not qualify"
     * is worth remembering that long; "the archive is not ready" is not, and remembering it
     * kept a promotion hidden until midnight even though archiving had finished minutes
     * later.
     */
    public static function notYetKnown(?string $periodStart = null, ?string $periodEnd = null): self
    {
        return new self(false, [], $periodStart, $periodEnd, true);
    }

    /**
     * Whether this answer is too early to be worth remembering for the day.
     */
    public function isProvisional(): bool
    {
        return $this->provisional;
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function triggered(array $context, ?string $periodStart = null, ?string $periodEnd = null): self
    {
        return new self(true, $context, $periodStart, $periodEnd);
    }

    public function isTriggered(): bool
    {
        return $this->triggered;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getPeriodStart(): ?string
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): ?string
    {
        return $this->periodEnd;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'triggered' => $this->triggered,
            'context' => $this->context,
            'periodStart' => $this->periodStart,
            'periodEnd' => $this->periodEnd,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            !empty($data['triggered']),
            isset($data['context']) && is_array($data['context']) ? $data['context'] : [],
            isset($data['periodStart']) ? (string) $data['periodStart'] : null,
            isset($data['periodEnd']) ? (string) $data['periodEnd'] : null
        );
    }
}
