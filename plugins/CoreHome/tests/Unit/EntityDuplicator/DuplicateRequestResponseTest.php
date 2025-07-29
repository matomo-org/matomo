<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\EntityDuplicator;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\EntityDuplicator\DuplicateRequestResponse;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group EntityDuplicator
 */
class DuplicateRequestResponseTest extends TestCase
{
    /**
     * @var DuplicateRequestResponse
     */
    private $duplicateRequestResponse;

    protected function setUp(): void
    {
        $this->duplicateRequestResponse = new DuplicateRequestResponse();
    }

    public function testHasResponseBeenModified()
    {
        $this->assertFalse($this->duplicateRequestResponse->hasResponseBeenModified());
    }

    /**
     * @dataProvider getPropertyNames
     */
    public function testHasResponseBeenModifiedSetValue($methodName, $value)
    {
        $this->assertTrue(method_exists($this->duplicateRequestResponse, $methodName));

        $this->duplicateRequestResponse->$methodName($value);

        $this->assertTrue($this->duplicateRequestResponse->hasResponseBeenModified());
    }

    public function testGetJsonResponseNoChanges()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No duplicate request response properties were set.');

        $this->duplicateRequestResponse->getJsonResponse();
    }

    public function testGetResponseArraySuccess()
    {
        $this->duplicateRequestResponse->setSuccess(true);
        $this->duplicateRequestResponse->setMessage('Item copied!');

        $this->assertSame(['success' => true, 'message' => 'Item copied!'], $this->duplicateRequestResponse->getResponseArray());
    }

    public function testGetResponseArraySuccessFail()
    {
        $this->duplicateRequestResponse->setSuccess(false);
        $this->duplicateRequestResponse->setMessage('Item duplication failed!');

        $this->assertSame(['success' => false, 'message' => 'Item duplication failed!'], $this->duplicateRequestResponse->getResponseArray());
    }

    public function testGetJsonResponseSuccess()
    {
        $this->duplicateRequestResponse->setSuccess(true);
        $this->duplicateRequestResponse->setMessage('Item copied!');

        $this->assertSame('{"success":true,"message":"Item copied!"}', $this->duplicateRequestResponse->getJsonResponse());
    }

    public function testGetJsonResponseSuccessWithData()
    {
        $this->duplicateRequestResponse->setSuccess(true);
        $this->duplicateRequestResponse->setMessage('Item copied!');
        $this->duplicateRequestResponse->setAdditionalData(['foo' => 'bar']);

        $this->assertSame('{"success":true,"message":"Item copied!","additionalData":{"foo":"bar"}}', $this->duplicateRequestResponse->getJsonResponse());
    }

    public function testGetJsonResponseSuccessFail()
    {
        $this->duplicateRequestResponse->setSuccess(false);
        $this->duplicateRequestResponse->setMessage('Item duplication failed!');

        $this->assertSame('{"success":false,"message":"Item duplication failed!"}', $this->duplicateRequestResponse->getJsonResponse());
    }

    public function getPropertyNames(): array
    {
        return [
            ['setSuccess', false],
            ['setSuccess', true],
            ['setMessage', ''],
            ['setMessage', 'Some message'],
            ['setMessage', 'Another message'],
            ['setAdditionalData', []],
            ['setAdditionalData', ['key' => 'value']],
        ];
    }
}
