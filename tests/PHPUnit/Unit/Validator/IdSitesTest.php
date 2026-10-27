<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Validator;

use Piwik\Validators\Exception;
use Piwik\Validators\IdSites;

/**
 * @group Validator
 * @group IdSites
 * @group IdSitesTest
 */
class IdSitesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getValidValues
     */
    public function testValidateAcceptsSiteIds($value)
    {
        self::expectNotToPerformAssertions();

        (new IdSites())->validate($value);
    }

    public function getValidValues()
    {
        return [
            [1],
            ['1'],
            ['1,4,5'],
            ['1, 4 ,5'],
            ['1,,5'],
            [[1, 4, 5]],
            [['1', '4']],
            [''],
            [[]],
            [null],
            [false],
        ];
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testValidateRejectsValuesThatNameNoSite($value)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorNotIdSites');

        (new IdSites())->validate($value);
    }

    public function getInvalidValues()
    {
        return [
            [0],
            ['0'],
            [-1],
            [','],
            ['1,abc'],
            ['1.5'],
            ['01'],
            [['']],
            [[1, 'abc']],
            [[[1]]],
            ['all'],
            [true],
        ];
    }

    public function testValidateAcceptsAllOnlyWhenAllowed()
    {
        (new IdSites(true))->validate('all');
        (new IdSites(true))->validate(['all']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('General_ValidatorErrorNotIdSitesOrAll');

        (new IdSites(true))->validate('0');
    }
}
