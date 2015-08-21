<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

abstract class BasePeriodTest extends IntegrationTestCase
{
    public function provideContainerConfig()
    {
        return array(
            'test.vars.loadRealTranslations' => true,
        );
    }
}
