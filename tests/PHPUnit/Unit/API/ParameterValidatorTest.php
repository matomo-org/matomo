<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

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
    /**
     * @param mixed $status
     * @param mixed $idSites
     * @param mixed $unchecked
     */
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

    /**
     * @param mixed $period
     */
    // @phpstan-ignore matomo.apiValidation.rejectedDefault (the default deliberately fails, it must be skipped at runtime)
    public function getReport(#[WhitelistedValue(['day', 'week'])] $period = false): string
    {
        $this->checkParameters(__METHOD__, get_defined_vars());

        return 'ok';
    }

    public function confirm(
        #[NumberRange(1000, max: 9999)]
        #[\SensitiveParameter]
        ?int $pin = null
    ): string {
        $this->checkParameters(__METHOD__, get_defined_vars());

        return 'ok';
    }

    public function getWithUnresolvableArgument(
        #[NumberRange(\Piwik\Tests\Unit\API\ParameterValidatorTestMissingClass::MIN)] ?int $limit = null // @phpstan-ignore class.notFound
    ): void {
        $this->checkParameters(__METHOD__, get_defined_vars());
    }
}

class ParameterValidatorTestChildApi extends ParameterValidatorTestApi
{
    /**
     * Overrides without repeating the attributes, the parent's validators still apply.
     *
     * @param mixed $status
     * @param mixed $idSites
     * @param mixed $unchecked
     */
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

    /**
     * @param mixed $value
     * @return void
     */
    public function validate($value)
    {
    }
}

class ParameterValidatorTestBrokenApi extends \Piwik\Plugin\API
{
    /**
     * @param mixed $value
     */
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

    public function testDeclaredDefaultIsSkipped(): void
    {
        // `false` is not whitelisted, but as the declared default it is not validated
        $this->assertSame('ok', $this->api->getReport());
        $this->assertSame('ok', $this->api->getReport(false));
        $this->assertSame('ok', $this->api->getReport('week'));

        $this->assertSame(['period'], $this->getViolatedParameters(function () {
            $this->api->getReport('month');
        }));
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

    public function testSensitiveParameterGetsAGenericMessage(): void
    {
        try {
            $this->api->confirm(42);
            $this->fail('Expected a ParameterValidationException');
        } catch (ParameterValidationException $e) {
            $this->assertSame(
                [['parameter' => 'pin', 'message' => 'General_ValidatorErrorInvalidValue']],
                $e->getViolations()
            );
            $this->assertStringNotContainsString('42', $e->getMessage());
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

    public function testDescribeFallsBackToTheValidatorNameForUnresolvableArguments(): void
    {
        $descriptions = (new ParameterValidator())->describe(
            new ReflectionMethod(ParameterValidatorTestApi::class, 'getWithUnresolvableArgument')
        );

        $this->assertSame(['limit' => ['NumberRange']], $descriptions);
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
