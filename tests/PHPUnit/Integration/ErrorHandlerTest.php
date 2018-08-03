<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;


use Piwik\Http;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ErrorHandlerTest extends IntegrationTestCase
{
    public function test_fatalErrorStackTracesReturned()
    {
        $url = Fixture::getRootUrl() . '/tests/resources/trigger-fatal.php?format=json';
        $response = Http::sendHttpRequest($url, 2);

        $response = json_decode($response, $isAssoc = true);
        $response['message'] = $this->cleanMessage($response['message']);

        $this->assertEquals('error', $response['result']);

        $expectedFormat = <<<FORMAT
Allowed memory size of %s bytes exhausted (tried to allocate %s bytes) on {includePath}/tests/resources/trigger-fatal.php(22)#0 {includePath}/tests/resources/trigger-fatal.php(35): MyClass-&gt;triggerError()#1 {includePath}/tests/resources/trigger-fatal.php(51): MyDerivedClass::staticMethod()#2 {includePath}/tests/resources/trigger-fatal.php(57): myFunction()
FORMAT;

        $this->assertStringMatchesFormat($expectedFormat, $response['message']);
    }

    private function cleanMessage($message)
    {
        return str_replace(PIWIK_INCLUDE_PATH, '{includePath}', $message);
    }
}