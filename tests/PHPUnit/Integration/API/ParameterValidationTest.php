<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\API;

use Piwik\API\DocumentationGenerator;
use Piwik\API\ParameterValidationException;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Plugins\Dashboard\API as DashboardApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Parameter validator attributes on real API methods, for HTTP-style and direct PHP calls.
 *
 * @group Core
 * @group ParameterValidation
 */
class ParameterValidationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;
        Fixture::createWebsite('2024-01-01 00:00:00');
    }

    public function testProxiedCallReportsEveryInvalidParameter(): void
    {
        $message = $this->getErrorMessage('ImageGraph.get', [
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get',
            'outputType' => '5',
            'width' => '-1',
            'fontSize' => '0',
        ]);

        $this->assertSame(3, substr_count($message, 'InvalidValueForParameter'));
        $this->assertStringContainsString('ValidatorErrorXNotWhitelisted', $message);
        $this->assertSame(2, substr_count($message, 'ValidatorErrorNumberTooLow'));
    }

    public function testArrayIsRejectedForASingleValueParameter(): void
    {
        $message = $this->getErrorMessage('ImageGraph.get', [
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get',
            'width' => ['1'],
        ]);

        $this->assertStringContainsString('ValidatorErrorNotSingleValue', $message);
    }

    /**
     * @dataProvider getInvalidIdSites
     */
    public function testIdSitesMustNameAWebsite($idSites): void
    {
        $message = $this->getErrorMessage('PrivacyManager.anonymizeSomeRawData', [
            'idSites' => $idSites,
            'date' => '2024-01-01',
            'anonymizeIp' => '1',
        ]);

        $this->assertStringContainsString('ValidatorErrorNotIdSitesOrAll', $message);
    }

    public function getInvalidIdSites(): iterable
    {
        yield 'zero' => ['0'];
        yield 'only a separator' => [','];
        yield 'non-numeric entry' => ['1,abc'];
        yield 'non-numeric entry in an array' => [['1', 'abc']];
    }

    public function testIdSitesAcceptsAnArrayOfWebsiteIds(): void
    {
        $result = Request::processRequest('CoreAdminHome.invalidateArchivedReports', [
            'idSites' => ['1'],
            'dates' => '2024-01-01',
            'period' => 'day',
        ]);

        $this->assertIsArray($result);
    }

    public function testValidTypedValuesStillRun(): void
    {
        $this->assertSame([], Request::processRequest('SitesManager.getSitesWithMinimumAccess', [
            'permission' => 'view',
            'pattern' => 'no such site',
            'limit' => '0',
        ]));

        $message = $this->getErrorMessage('SitesManager.getSitesWithMinimumAccess', [
            'permission' => 'view',
            'limit' => '-1',
        ]);
        $this->assertStringContainsString('ValidatorErrorNumberTooLow', $message);
    }

    public function testDirectPhpCallIsValidatedToo(): void
    {
        $violations = $this->getViolations(function () {
            DashboardApi::getInstance()->copyDashboardToUser(0, 'superUserLogin', str_repeat('a', 101));
        });

        $this->assertSame(['idDashboard', 'dashboardName'], array_column($violations, 'parameter'));
    }

    public function testApiReferenceShowsTheValidators(): void
    {
        $className = Request::getClassNameAPI('ImageGraph');
        Proxy::getInstance()->registerClass($className);

        $this->assertSame(
            [
                'outputType' => ['WhitelistedValue([0, 1, 2])'],
                'width' => ['NumberRange(0)'],
                'height' => ['NumberRange(0)'],
                'fontSize' => ['NumberRange(1)'],
            ],
            Proxy::getInstance()->getParameterRules($className, 'get')
        );

        $documentation = (new DocumentationGenerator())->getApiDocumentationAsString(false);
        $this->assertStringContainsString("<i>int</i> fontSize = '9' <small>NumberRange(1)</small>", $documentation);
    }

    /**
     * Request::processRequest() rethrows API errors as a plain exception carrying the rendered message
     * (the JSON `errors` list is covered by the renderer's unit test).
     *
     * @param array<string, mixed> $parameters
     */
    private function getErrorMessage(string $method, array $parameters): string
    {
        try {
            Request::processRequest($method, $parameters);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $this->fail('Expected the request to fail');
    }

    /**
     * @return list<array{parameter: string, message: string}>
     */
    private function getViolations(callable $call): array
    {
        try {
            $call();
        } catch (ParameterValidationException $e) {
            $this->assertSame(400, $e->getCode());

            return $e->getViolations();
        }

        $this->fail('Expected a ParameterValidationException');
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
