<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Tests\Framework\TestAspect;

/**
 * TODO
 */
class GetProviderConfig extends TestAspect
{
    /**
     * @var string
     */
    private $getProviderConfigMethod;

    public function __construct($getProviderConfigMethod)
    {
        $this->getProviderConfigMethod = $getProviderConfigMethod;
    }

    public static function isClassAspect()
    {
        return false;
    }
}