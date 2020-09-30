<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var TwoFactorAuthentication
     */
    private $twoFa;

    public function __construct(TwoFactorAuthentication $twoFa)
    {
        $this->twoFa = $twoFa;
    }

    public function schedule()
    {
        $this->daily('cleanupTwoFaCodesUsedRecently');
    }

    public function cleanupTwoFaCodesUsedRecently()
    {
        $this->twoFa->cleanupTwoFaCodesUsedRecently();
    }

}
