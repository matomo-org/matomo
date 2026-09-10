<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\Marketplace\Environment;

/**
 * Triggers on an instance with enough registered users for signing them in one by one to
 * be a chore worth solving centrally.
 *
 * Evaluated against the instance's current state rather than a report, so it is not
 * cached: a user added at 10:00 counts straight away.
 */
class ManyUsersTrigger implements PromotionTrigger
{
    public const NAME = 'many_users';

    public const MINIMUM_USERS = 20;

    private Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        // The same count Matomo already reports to the Marketplace, which excludes the
        // anonymous pseudo user.
        $numUsers = $this->environment->getNumUsers();

        if ($numUsers < self::MINIMUM_USERS) {
            return TriggerResult::notTriggered();
        }

        return TriggerResult::triggered(['count' => $numUsers]);
    }
}
