<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @backupGlobals enabled
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Request;

/**
 * @group Core
 */
class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testFromRequest(): void
    {
        $_GET = [
            'param1' => 'value',
            'param2' => 'other',
        ];
        $_POST = [
            'param1' => 'another value',
            'param3' => 'third',
        ];

        $request = Request::fromRequest();

        self::assertEquals(
            [
                'param1' => 'value',
                'param2' => 'other',
                'param3' => 'third',
            ],
            $request->getParameters()
        );
    }

    public function testFromPost(): void
    {
        $_GET = [
            'param1' => 'value',
            'param2' => 'other',
        ];
        $_POST = [
            'param1' => 'another value',
            'param3' => 'third',
        ];

        $request = Request::fromPost();

        self::assertEquals(
            [
                'param1' => 'another value',
                'param3' => 'third',
            ],
            $request->getParameters()
        );
    }

    public function testFromGet(): void
    {
        $_GET = [
            'param1' => 'value',
            'param2' => 'other',
        ];
        $_POST = [
            'param1' => 'another value',
            'param3' => 'third',
        ];

        $request = Request::fromGet();

        self::assertEquals(
            [
                'param1' => 'value',
                'param2' => 'other',
            ],
            $request->getParameters()
        );
    }

    public function testFromQueryString(): void
    {
        $_GET = [
            'param1' => 'value',
            'param2' => 'other',
        ];
        $_POST = [
            'param1' => 'another value',
            'param3' => 'third',
        ];

        $request = Request::fromQueryString('var1=val1&var3=val5&arr[]=1&arr[]=2');

        self::assertEquals(
            [
                'var1' => 'val1',
                'var3' => 'val5',
                'arr' => [1, 2]
            ],
            $request->getParameters()
        );
    }

    /**
     * @dataProvider getValidIntValues
     */
    public function testGetIntegerParameterValidValue($requestValue, $expectedValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame($request->getIntegerParameter('parameter'), $expectedValue);
    }

    public function getValidIntValues(): iterable
    {
        yield 'Integer value' => [17, 17];
        yield 'String value' => ['17', 17];
        yield 'Float value, without floating digits' => [17.0, 17];
        yield 'Exp value' => [2e3, 2000];
        yield 'Hex value' => [0x3, 3];
        yield 'Binary value' => [0b11, 3];
        yield 'Octal value' => [0123, 83];
    }

    /**
     * @dataProvider getInvalidIntValues
     */
    public function testGetIntegerParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame(23, $request->getIntegerParameter('parameter', 23));
    }

    /**
     * @dataProvider getInvalidIntValues
     */
    public function testGetIntegerParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getIntegerParameter('parameter');
    }

    public function getInvalidIntValues(): iterable
    {
        yield 'null value' => [null];
        yield 'bool value' => [true];
        yield 'empty string' => [''];
        yield 'random string' => ['random string'];
        yield 'array' => [['x' => 'y']];
        yield 'object' => [new \stdClass()];
        yield 'float value' => [1.333];
        yield 'Exp value as string' => ['2e3'];
        yield 'Hex value as string' => ['0x3'];
        yield 'Binary value as string' => ['0b11'];
        yield 'Octal value as string' => ['0123'];
    }

    /**
     * @dataProvider getValidFloatValues
     */
    public function testGetFloatParameterValidValue($requestValue, $expectedValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame($request->getFloatParameter('parameter'), $expectedValue);
    }

    public function getValidFloatValues(): iterable
    {
        yield 'Integer value' => [17, 17.0];
        yield 'Float value' => [17.123, 17.123];
        yield 'String value' => ['17.123', 17.123];
        yield 'Negative string value' => ['-17.123', -17.123];
        yield 'Positive string value' => ['+17.123', 17.123];
        yield 'String value, only fraction digits' => ['.123', 0.123];
        yield 'String value, no fraction digits' => ['123.e-3', 0.123];
        yield 'Negative string value, only fraction digits' => ['-.123', -0.123];
        yield 'Exp value' => [2e-2, 0.02];
        yield 'String exp value' => ['2e-3', 0.002];
        yield 'String Exp value' => ['1.2E-26', 1.2E-26];
        yield 'Octal exp value' => [0123e-2, 1.23];
        yield 'Octal value as string' => ['0123', 123.0];
        yield 'Underscore notation as string' => ['1_123.123_33', 1123.12333];
        yield 'String value with many digits' => ['1254254645455484545.1', 1.2542546454554847E+18];
        yield 'String value with many fraction digits' => ['14.051545421864646123', 14.051545421864645];
    }

    /**
     * @dataProvider getInvalidFloatValues
     */
    public function testGetFloatParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame(23.1, $request->getFloatParameter('parameter', 23.1));
    }

    /**
     * @dataProvider getInvalidFloatValues
     */
    public function testGetFloatParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getFloatParameter('parameter');
    }

    public function getInvalidFloatValues(): iterable
    {
        yield 'null value' => [null];
        yield 'bool value' => [true];
        yield 'empty string' => [''];
        yield 'random string' => ['random string'];
        yield 'array' => [['x' => 'y']];
        yield 'object' => [new \stdClass()];
        yield 'Hex value as string' => ['0x3'];
        yield 'Binary value as string' => ['0b11'];
        yield 'Invalid exp float string' => ['4e5.5'];
    }

    /**
     * @dataProvider getValidStringValues
     */
    public function testGetStringParameterValidValue($requestValue, $expectedValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame($request->getStringParameter('parameter'), $expectedValue);
    }

    public function getValidStringValues(): iterable
    {
        yield 'String value' => ['random string', 'random string'];
        yield 'String value with null byte' => ["ran\0dom str\0ing", 'random string'];
        yield 'empty string' => ['', ''];
        yield 'whitespace value' => [' ', ' '];
        yield 'Integer value' => [17, '17'];
        yield 'Float value' => [17.123, '17.123'];
        yield 'Exp value' => [2e2, '200'];
        yield 'Octal exp value' => [0123e2, '12300'];
        yield 'Exp value as string' => ['2e-3', '2e-3'];
        yield 'Hex value as string' => ['0x3', '0x3'];
        yield 'Binary value as string' => ['0b11', '0b11'];
        yield 'Octal value as string' => ['0123', '0123'];
    }

    /**
     * @dataProvider getInvalidStringValues
     */
    public function testGetStringParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame('default', $request->getStringParameter('parameter', 'default'));
    }

    /**
     * @dataProvider getInvalidStringValues
     */
    public function testGetStringParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getStringParameter('parameter');
    }

    public function getInvalidStringValues(): iterable
    {
        yield 'null value' => [null];
        yield 'bool value' => [true];
        yield 'array' => [['x' => 'y']];
        yield 'object' => [new \stdClass()];
    }

    /**
     * @dataProvider getValidBoolValues
     */
    public function testGetBoolParameterValidValue($requestValue, $expectedValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame($request->getBoolParameter('parameter'), $expectedValue);
    }

    public function getValidBoolValues(): iterable
    {
        yield 'bool true' => [true, true];
        yield 'string true' => ['true', true];
        yield 'string tRuE' => ['tRuE', true];
        yield 'string TRUE' => ['TRUE', true];
        yield 'string 1' => ['1', true];
        yield 'int 1' => [1, true];
        yield 'bool false' => [false, false];
        yield 'string false' => ['false', false];
        yield 'string fALsE' => ['fALsE', false];
        yield 'string FALSE' => ['FALSE', false];
        yield 'string 0' => ['0', false];
        yield 'int 0' => [0, false];
    }

    /**
     * @dataProvider getInvalidBoolValues
     */
    public function testGetBoolParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame(false, $request->getBoolParameter('parameter', false));
    }

    /**
     * @dataProvider getInvalidBoolValues
     */
    public function testGetBoolParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getBoolParameter('parameter');
    }

    public function getInvalidBoolValues(): iterable
    {
        yield 'null value' => [null];
        yield 'integer value' => [7];
        yield 'float value' => [1.22];
        yield 'string value' => ['random string'];
        yield 'empty string' => [''];
        yield 'array' => [['x' => 'y']];
        yield 'object' => [new \stdClass()];
    }

    /**
     * @dataProvider getValidArrayValues
     */
    public function testGetArrayParameterValidValue($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame($request->getArrayParameter('parameter'), $requestValue);
    }

    public function getValidArrayValues(): iterable
    {
        yield 'numeric indexed array, string values' => [['a', 'b', 'f']];
        yield 'numeric indexed array, mixed values' => [['a', 1, 1.22]];
        yield 'string indexed array, string values' => [['a' => 'b', 'b' => 'a']];
        yield 'string indexed array, mixed values' => [['a' => 'b', 'b' => false]];
        yield 'multidim array' => [['a' => 'b', 'b' => [false, 1.22, 'key' => new \stdClass()]]];
    }

    public function testGetArrayFiltersNullBytes()
    {
        $request = new Request(['parameter' => ['a' => "my\0 string", 3, false]]);
        self::assertSame($request->getArrayParameter('parameter'), ['a' => 'my string', 3, false]);
    }

    /**
     * @dataProvider getInvalidArrayValues
     */
    public function testGetArrayParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame(['a' => 'b'], $request->getArrayParameter('parameter', ['a' => 'b']));
    }

    /**
     * @dataProvider getInvalidArrayValues
     */
    public function testGetArrayParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getArrayParameter('parameter');
    }

    public function getInvalidArrayValues(): iterable
    {
        yield 'null value' => [null];
        yield 'bool value' => [true];
        yield 'integer value' => [7];
        yield 'float value' => [1.22];
        yield 'string value' => ['random string'];
        yield 'empty string' => [''];
        yield 'object' => [new \stdClass()];
    }

    /**
     * @dataProvider getValidJsonValues
     */
    public function testGetJsonParameterValidValue($requestValue, $expectedValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertEquals($request->getJsonParameter('parameter'), $expectedValue);
    }

    public function getValidJsonValues(): iterable
    {
        yield 'string value' => ['"random string value"', 'random string value'];
        yield 'string value with encoded null byte' => ['"my \u0000string"', 'my string'];
        yield 'float value' => ['1.234', 1.234];
        yield 'integer value' => ['19', 19];
        yield 'bool value' => ['false', false];
        yield 'array value' => ['{"a":"b\u0000","0":"c"}', ['a' => 'b', 'c']];
        yield 'multidim array' => [
            '{"a":"b","b":{"0":false,"1":1.22,"key":{}}}',
            ['a' => 'b', 'b' => [false, 1.22, 'key' => []]]
        ];
    }

    /**
     * @dataProvider getInvalidJsonValues
     */
    public function testGetJsonParameterInvalidValueReturnsDefault($requestValue)
    {
        $request = new Request(['parameter' => $requestValue]);
        self::assertSame('random default', $request->getJsonParameter('parameter', 'random default'));
    }

    /**
     * @dataProvider getInvalidJsonValues
     */
    public function testGetJsonParameterInvalidValueThrowsExceptionIfNoDefault($requestValue)
    {
        self::expectException(\InvalidArgumentException::class);

        $request = new Request(['parameter' => $requestValue]);
        $request->getJsonParameter('parameter');
    }

    public function getInvalidJsonValues(): iterable
    {
        yield 'null value' => [null];
        yield 'bool value' => [true];
        yield 'integer value' => [7];
        yield 'float value' => [1.22];
        yield 'string value' => ['random string'];
        yield 'empty string' => [''];
        yield 'object value' => [new \stdClass()];
        yield 'array value' => [['a' => 'b', 'c']];
        yield 'invalid json' => ['{"a":"b","b":{"0":false,"1:1.22,"key":{}}}'];
    }

    /**
     * @dataProvider getDifferentDefaultTypes
     */
    public function testGetJsonParameterWithDifferentDefaultTypes($default)
    {
        $request = new Request(['parameter' => 'invalid json']);
        self::assertSame($default, $request->getJsonParameter('parameter', $default));
    }

    public function getDifferentDefaultTypes(): iterable
    {
        yield 'bool value' => [true];
        yield 'integer value' => [7];
        yield 'float value' => [1.22];
        yield 'array value' => [['a' => 'b', 'c', 5 => true]];
        yield 'string value' => ['random string'];
        yield 'json string value' => ['{"a":"b","b":{"0":false,"1":1.22,"key":{}}}'];
    }
}
