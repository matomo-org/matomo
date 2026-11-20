<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use Attribute;
use PHPUnit\Framework\TestCase;
use Piwik\API\ParameterValidationException;
use Piwik\API\ParameterValidator;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\CharacterLength;
use Piwik\Validators\IdSites;
use Piwik\Validators\NumberRange;
use Piwik\Validators\WhitelistedValue;
use ReflectionMethod;

class ParameterValidatorTestApi extends \Piwik\Plugin\API
{
    public function getUsers(
        #[NumberRange(0)] ?int $limit = null,
        #[WhitelistedValue(['active', 'pending'])] $status = null,
        #[CharacterLength(1, max: 5)] string $name = 'jane',
        #[IdSites(allowAll: true)] $idSites = null,
        $unchecked = null
    ): string {
        $this->checkParameters(__METHOD__, get_defined_vars());

        return 'ok';
    }
}

class ParameterValidatorTestChildApi extends ParameterValidatorTestApi
{
    // overrides without repeating the attributes, the parent's validators still apply
    public function getUsers(?int $limit = null, $status = null, string $name = 'jane', $idSites = null, $unchecked = null): string
    {
        return parent::getUsers($limit, $status, $name, $idSites, $unchecked);
    }
}

#[Attribute(Attribute::TARGET_PARAMETER)]
class ParameterValidatorTestFailingValidator extends BaseValidator
{
    public function __construct()
    {
        throw new \RuntimeException('cannot be created');
    }

    public function validate($value)
    {
    }
}

class ParameterValidatorTestBrokenApi extends \Piwik\Plugin\API
{
    public function get(#[ParameterValidatorTestFailingValidator] $value = null): string
    {
        $this->checkParameters(__METHOD__, get_defined_vars());

        return 'ok';
    }
}

/**
 * @group Core
 */
class ParameterValidatorTest extends TestCase
{
    /**
     * @var ParameterValidatorTestApi
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = new ParameterValidatorTestApi();
    }

    public function testValidValuesPass(): void
    {
        $this->assertSame('ok', $this->api->getUsers(10, 'active', 'jane', [1, 2], ['anything']));
        $this->assertSame('ok', $this->api->getUsers(0, null, 'jane', 'all'));
    }

    public function testNullValuesAreSkipped(): void
    {
        $this->assertSame('ok', $this->api->getUsers());
    }

    public function testNamedArgumentsAreChecked(): void
    {
        $this->assertSame(['name'], $this->getViolatedParameters(function () {
            $this->api->getUsers(name: 'a name that is far too long');
        }));
    }

    public function testEveryViolationIsReportedAtOnce(): void
    {
        try {
            $this->api->getUsers(-1, 'unknown', 'jane', '0');
            $this->fail('Expected a ParameterValidationException');
        } catch (ParameterValidationException $e) {
            $this->assertSame(400, $e->getCode());
            $this->assertSame(['limit', 'status', 'idSites'], array_column($e->getViolations(), 'parameter'));
            $this->assertSame(2, substr_count($e->getMessage(), '; '));
        }
    }

    public function testArraysAreRejectedUnlessEveryValidatorAcceptsThem(): void
    {
        $this->assertSame(['status'], $this->getViolatedParameters(function () {
            $this->api->getUsers(null, ['active']);
        }));

        $this->assertSame('ok', $this->api->getUsers(null, null, 'jane', ['1', '4']));
    }

    public function testValidatorsOfTheDeclaringMethodApplyToOverrides(): void
    {
        $this->assertSame(['limit'], $this->getViolatedParameters(function () {
            (new ParameterValidatorTestChildApi())->getUsers(-1);
        }));
    }

    public function testValidatorThatCannotBeCreatedFailsEveryCall(): void
    {
        $api = new ParameterValidatorTestBrokenApi();

        foreach ([1, 2] as $call) {
            try {
                $api->get('value');
                $this->fail('Expected call ' . $call . ' to fail');
            } catch (\RuntimeException $e) {
                $this->assertSame('cannot be created', $e->getMessage());
            }
        }
    }

    public function testDescribeListsTheValidatorsWithTheirArguments(): void
    {
        $descriptions = (new ParameterValidator())->describe(new ReflectionMethod(ParameterValidatorTestApi::class, 'getUsers'));

        $this->assertSame([
            'limit' => ['NumberRange(0)'],
            'status' => ["WhitelistedValue(['active', 'pending'])"],
            'name' => ['CharacterLength(1, max: 5)'],
            'idSites' => ['IdSites(allowAll: true)'],
        ], $descriptions);
    }

    /**
     * @return string[]
     */
    private function getViolatedParameters(callable $call): array
    {
        try {
            $call();
        } catch (ParameterValidationException $e) {
            return array_column($e->getViolations(), 'parameter');
        }

        $this->fail('Expected a ParameterValidationException');
    }
}
