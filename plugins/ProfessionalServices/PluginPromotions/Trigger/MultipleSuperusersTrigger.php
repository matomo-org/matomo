<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\UsersManager\Model;

/**
 * Triggers on an instance with enough users, several of whom can change anything, for an
 * audit trail of who did what to be worth having.
 *
 * Both conditions matter: many users alone is a login problem, which is what
 * {@see ManyUsersTrigger} pitches; several administrators is what makes changes hard to
 * attribute.
 *
 * Evaluated against the instance's current state rather than a report, so it is not
 * cached.
 */
class MultipleSuperusersTrigger implements PromotionTrigger
{
    public const NAME = 'multiple_superusers';

    public const MINIMUM_USERS = 10;

    public const MINIMUM_SUPERUSERS = 3;

    private Environment $environment;

    private Model $userModel;

    public function __construct(Environment $environment, Model $userModel)
    {
        $this->environment = $environment;
        $this->userModel = $userModel;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        $numUsers = $this->environment->getNumUsers();

        if ($numUsers < self::MINIMUM_USERS) {
            return TriggerResult::notTriggered();
        }

        $numSuperusers = count($this->userModel->getUsersHavingSuperUserAccess());

        if ($numSuperusers < self::MINIMUM_SUPERUSERS) {
            return TriggerResult::notTriggered();
        }

        return TriggerResult::triggered(['count' => $numUsers, 'numSuperusers' => $numSuperusers]);
    }
}
