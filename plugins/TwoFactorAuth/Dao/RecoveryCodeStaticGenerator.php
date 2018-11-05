<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth\Dao;

use Piwik\Common;

class RecoveryCodeStaticGenerator
{
    private $index = 0;
    public function generateCode()
    {
        $this->index++;
        return str_pad($this->index, 16, '0');
    }
}

