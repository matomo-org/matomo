<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TwoFactorAuth\Commands;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\TwoFactorAuth\TwoFactorAuthentication;

class Disable2FAForUser extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('twofactorauth:disable-2fa-for-user');
        $this->setDescription(
            'Disable two-factor authentication for a user. Useful if a user loses the device that was used for'
            . ' two-factor authentication. After it was disabled, the user will be able to set it up again.'
        );
        $this->addRequiredValueOption('login', null, 'Login of an existing user');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $this->checkAllRequiredOptionsAreNotEmpty();
        $login = $input->getOption('login');

        // Note: We can't use API here, as the API method would require a password confirmation
        $t2f = StaticContainer::get(TwoFactorAuthentication::class);
        $t2f->disable2FAforUser($login);

        $message = sprintf('<info>Disabled two-factor authentication for user: %s</info>', $login);
        $output->writeln($message);

        return self::SUCCESS;
    }
}
