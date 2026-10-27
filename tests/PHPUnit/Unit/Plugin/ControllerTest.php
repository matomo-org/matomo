<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\Controller;
use ReflectionMethod;

/**
 * @group Core
 */
class ControllerTest extends TestCase
{
    /**
     * @dataProvider getPhpVersionsForCriticalVulnerability
     */
    public function testIsPhpVersionAffectedByCriticalVulnerability(int $major, int $minor, int $release, bool $expected): void
    {
        $method = new ReflectionMethod(Controller::class, 'isPhpVersionAffectedByCriticalVulnerability');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke(null, $major, $minor, $release));
    }

    public function getPhpVersionsForCriticalVulnerability(): iterable
    {
        yield 'PHP 7.2 has no upstream fix' => [7, 2, 5, true];
        yield 'PHP 7.4 has no upstream fix' => [7, 4, 33, true];
        yield 'PHP 8.0 has no upstream fix' => [8, 0, 30, true];
        yield 'PHP 8.1 has no upstream fix' => [8, 1, 34, true];

        yield 'PHP 8.2 before fix' => [8, 2, 34, true];
        yield 'PHP 8.2 fixed release' => [8, 2, 35, false];
        yield 'PHP 8.2 after fix' => [8, 2, 36, false];

        yield 'PHP 8.3 before fix' => [8, 3, 35, true];
        yield 'PHP 8.3 fixed release' => [8, 3, 36, false];
        yield 'PHP 8.3 after fix' => [8, 3, 37, false];

        yield 'PHP 8.4 before fix' => [8, 4, 26, true];
        yield 'PHP 8.4 fixed release' => [8, 4, 27, false];
        yield 'PHP 8.4 after fix' => [8, 4, 28, false];

        yield 'PHP 8.5 before fix' => [8, 5, 11, true];
        yield 'PHP 8.5 fixed release' => [8, 5, 12, false];
        yield 'PHP 8.5 after fix' => [8, 5, 13, false];

        yield 'PHP 8.6 not affected' => [8, 6, 0, false];
        yield 'PHP 9.0 not affected' => [9, 0, 0, false];
    }
}
