<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\SettingsPiwik;

/**
 * Signs the login in a trial request email's unsubscribe link, so the link works without logging in
 * but cannot be altered to unsubscribe another user.
 */
class UnsubscribeToken
{
    private const PURPOSE = 'Marketplace.unsubscribeTrialRequestEmails';

    public function generate(string $login): string
    {
        return hash_hmac('sha256', self::PURPOSE . '|' . $login, SettingsPiwik::getSalt());
    }

    public function isValid(string $login, string $token): bool
    {
        return $login !== '' && hash_equals($this->generate($login), $token);
    }
}
