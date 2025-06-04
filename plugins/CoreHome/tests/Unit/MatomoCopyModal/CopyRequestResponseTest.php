<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreHome\tests\Unit\MatomoCopyModal;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreHome\MatomoCopyModal\CopyRequestResponse;

/**
 * @group CoreHome
 * @group CoreHomeTest
 * @group MatomoCopyModal
 */
class CopyRequestResponseTest extends TestCase
{
    /**
     * @var CopyRequestResponse
     */
    private $copyRequestResponse;

    protected function setUp(): void
    {
        $this->copyRequestResponse = new CopyRequestResponse();
    }

    public function testHasResponseBeenModified()
    {
        $this->assertFalse($this->copyRequestResponse->hasResponseBeenModified());
    }

    /**
     * @dataProvider getPropertyNames
     */
    public function testHasResponseBeenModifiedSetValue($methodName, $value)
    {
        $this->assertTrue(method_exists($this->copyRequestResponse, $methodName));

        $this->copyRequestResponse->$methodName($value);

        $this->assertTrue($this->copyRequestResponse->hasResponseBeenModified());
    }

    public function getPropertyNames(): array
    {
        return [
            ['setIsCopySuccessful', false],
            ['setIsCopySuccessful', true],
            ['setSuccessMessage', ''],
            ['setSuccessMessage', 'Some message'],
            ['setResponseData', []],
            ['setResponseData', ['key' => 'value']],
            ['setErrorMessage', ''],
            ['setErrorMessage', 'Some message'],
            ['setErrorMessage', 'Another message'],
            ['setErrorCode', 0],
            ['setErrorCode', 400],
        ];
    }
}
