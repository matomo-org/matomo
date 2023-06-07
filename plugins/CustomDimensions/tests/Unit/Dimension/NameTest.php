<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Unit\Dimension;
use Piwik\Plugins\CustomDimensions\Dimension\Name;
use Piwik\Tests\Framework\Fixture;

/**
 * @group CustomDimensions
 * @group NameTest
 * @group Name
 * @group Plugins
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        Fixture::resetTranslations();
    }

    public function test_check_shouldFailWhenNameIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_NameIsRequired');

        $this->buildName('')->check();
    }

    /**
     * @dataProvider getInvalidNames
     */
    public function test_check_shouldFailWhenNameIsInvalid($name)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_NameAllowedCharacters');

        $this->buildName($name)->check();
    }

    public function test_check_shouldFailWhenNameIsTooLong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CustomDimensions_NameIsTooLong');

        $this->buildName(str_pad('test', 256, '434'))->check();
    }

    public function getInvalidNames()
    {
        return array(
            array('test.name'),
            array('.'),
            array('..'),
            array('../'),
            array('/'),
            array('<b>test</b>'),
            array('\\test'),
            array('/tmp'),
            array('&amp;'),
            array('<test'),
            array('Test>te'),
        );
    }

    /**
     * @dataProvider getValidNames
     */
    public function test_check_shouldNotFailWhenScopeIsValid($name)
    {
        self::expectNotToPerformAssertions();

        $this->buildName($name)->check();
    }

    public function getValidNames()
    {
        return array(
            array('testname012ewewe er 54 -_ 454'),
            array('testname'),
            array('öüätestnam'),
        );
    }

    private function buildName($name)
    {
        return new Name($name);
    }

}
