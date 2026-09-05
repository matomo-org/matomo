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

    /**
     * @param array<string, mixed> $context
     */
    private function __construct(bool $triggered, array $context, ?string $periodStart, ?string $periodEnd)
    {
        $this->triggered = $triggered;
        $this->context = $context;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public static function notTriggered(?string $periodStart = null, ?string $periodEnd = null): self
    {
        return new self(false, [], $periodStart, $periodEnd);
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
