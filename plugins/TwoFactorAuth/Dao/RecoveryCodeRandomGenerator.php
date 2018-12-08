<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth\Dao;

use Piwik\Common;

class RecoveryCodeRandomGenerator
{
    public function generateCode()
    {
       return Common::getRandomString(16);
    }
}

