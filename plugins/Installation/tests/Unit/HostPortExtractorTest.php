<?php

namespace Piwik\Plugins\Installation\tests\Unit;

use Piwik\Plugins\Installation\HostPortExtractor;

class HostPortExtractorTest extends \PHPUnit\Framework\TestCase
{
    public function validInputs()
    {
        return [
            ['127.0.0.1:3000', ['127.0.0.1', '3000']],
            ['localhost:3000', ['localhost', '3000']],
            ['/test/path/socket', ['', '/test/path/socket']],
            ['[2001:db8:3333:4444:5555:6666:7777:8888]', ['[2001:db8:3333:4444:5555:6666:7777:8888]', '']],
            ['[2001:db8:3333:4444:5555:6666:7777:8888]:3000', ['[2001:db8:3333:4444:5555:6666:7777:8888]', '3000']],
            ['[2001::8888]', ['[2001::8888]', '']],
            ['[2001::8888]:3000', ['[2001::8888]', '3000']],
        ];
    }

    public function invalidInputs()
    {
        return [
            ['127.0.0.1'],
            ['localhost'],
            ['2001::8888'],
            ['[2001::db8::8888]'],
            ['[200r::11zz]'],
            ['a [2001::8888] a'],
            ['[::]:3000a'],
        ];
    }

    /**
     * @dataProvider validInputs
     */
    public function testValidDbHosts($input, $expected)
    {
        $extractedHostAndPort = HostPortExtractor::extract($input);
        $this->assertEquals($extractedHostAndPort->host, $expected[0]);
        $this->assertEquals($extractedHostAndPort->port, $expected[1]);
    }

    /**
     * @dataProvider invalidInputs
     */
    public function testInvalidDbHosts($input)
    {
        $extractedHostAndPort = HostPortExtractor::extract($input);
        $this->assertNull($extractedHostAndPort);
    }
}
