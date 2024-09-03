<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Translation\Loader;

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Validators\IdSite;

/**
 * @group Validator
 * @group IdSiteTest
 */
class IdSiteTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2012-03-04 05:06:07');
        Fixture::createWebsite('2012-03-04 05:06:07');
        Fixture::createWebsite('2012-03-04 05:06:07');
    }

    public function testValidateSuccessValueNotEmpty()
    {
        self::expectNotToPerformAssertions();

        $this->validate('1');
        $this->validate('2');
        $this->validate(1);
        $this->validate(2);
    }

    public function testValidateFailValueDoesNotExist()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);

        $this->validate(99);
    }

    public function testValidateFailValueIsEmpty()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);

        $this->validate(0);
    }

    public function testValidateFailValueIsFalse()
    {
        $this->expectException(\Piwik\Exception\UnexpectedWebsiteFoundException::class);

        $this->validate(false);
    }

    private function validate($value)
    {
        $validator = new IdSite();
        $validator->validate($value);
    }
}
