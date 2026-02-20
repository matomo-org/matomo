<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Feedback;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\Storage\UserScopedSettingsStore;

class FeedbackReminder
{
    public $userLogin;

    public function __construct()
    {
        $this->userLogin = Piwik::getCurrentUserLogin();
    }

    public function getUserOption()
    {
        return $this->getStore()->get('Feedback', $this->userLogin, 'nextFeedbackReminder', false);
    }

    public function setUserOption($value)
    {
        $this->getStore()->set('Feedback', $this->userLogin, 'nextFeedbackReminder', $value);
    }

    private function getStore(): UserScopedSettingsStore
    {
        return StaticContainer::get(UserScopedSettingsStore::class);
    }
}
