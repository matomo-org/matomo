<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Option;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\SegmentEditor\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentEditor
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /** @var array */
    private $backupGet = [];

    /** @var array */
    private $backupRequest = [];

    /** @var int[] */
    private $createdSegmentIds = [];

    /** @var string|false */
    private $backupBrowserTriggerArchivingOption = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;
        $this->backupBrowserTriggerArchivingOption = Option::get(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        FakeAccess::clearAccess($superUser = true, $idSitesAdmin = [1], $idSitesView = [1], $login = 'superUserLogin');
    }

    public function tearDown(): void
    {
        foreach ($this->createdSegmentIds as $idSegment) {
            try {
                API::getInstance()->delete($idSegment);
            } catch (\Throwable $ex) {
                // Ignore cleanup errors in tests.
            }
        }

        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;
        if ($this->backupBrowserTriggerArchivingOption === false) {
            Option::delete(Rules::OPTION_BROWSER_TRIGGER_ARCHIVING);
        } else {
            Rules::setBrowserTriggerArchiving((bool) $this->backupBrowserTriggerArchivingOption);
        }

        parent::tearDown();
    }

    public function testManageSegmentsRendersRealtimeAndPreProcessedRowsAndOrder(): void
    {
        Rules::setBrowserTriggerArchiving(false);

        $selectedPreProcessed = [
            'name' => 'Selected preprocessed segment',
            'definition' => 'countryCode==fr',
            'idSite' => 1,
            'autoArchive' => true,
        ];
        $starredPreProcessed = [
            'name' => 'Starred preprocessed segment',
            'definition' => 'browserCode==FF',
            'idSite' => 1,
            'autoArchive' => true,
        ];
        $realtime = [
            'name' => 'Realtime segment',
            'definition' => 'visitCount>=1',
            'idSite' => 1,
            'autoArchive' => false,
        ];

        $selectedSegmentId = API::getInstance()->add(
            $selectedPreProcessed['name'],
            $selectedPreProcessed['definition'],
            $selectedPreProcessed['idSite'],
            $selectedPreProcessed['autoArchive'],
            $enabledAllUsers = true
        );
        $this->createdSegmentIds[] = $selectedSegmentId;

        $starredSegmentId = API::getInstance()->add(
            $starredPreProcessed['name'],
            $starredPreProcessed['definition'],
            $starredPreProcessed['idSite'],
            $starredPreProcessed['autoArchive'],
            $enabledAllUsers = true
        );
        $this->createdSegmentIds[] = $starredSegmentId;

        $realtimeSegmentId = API::getInstance()->add(
            $realtime['name'],
            $realtime['definition'],
            $realtime['idSite'],
            $realtime['autoArchive'],
            $enabledAllUsers = true
        );
        $this->createdSegmentIds[] = $realtimeSegmentId;

        API::getInstance()->star($starredSegmentId);

        $_GET = [
            'idSite' => '1',
            'period' => 'range',
            'date' => '2010-03-06,2010-03-08',
            'segment' => $selectedPreProcessed['definition'],
        ];
        $_REQUEST = $_GET;

        $html = (new Controller())->manageSegments();

        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);

        $this->assertSame(
            '3',
            $this->getAllVisitsRowAttribute($xpath, 'data-segment-order')
        );
        $this->assertSame(
            '2',
            $this->getRowAttribute($xpath, $selectedPreProcessed['name'], 'data-segment-order')
        );
        $this->assertSame(
            '1',
            $this->getRowAttribute($xpath, $starredPreProcessed['name'], 'data-segment-order')
        );
        $this->assertSame(
            '0',
            $this->getRowAttribute($xpath, $realtime['name'], 'data-segment-order')
        );

        $this->assertSame('-', $this->getNumericCellValue($xpath, $realtime['name'], 1));
        $this->assertSame('-', $this->getNumericCellValue($xpath, $realtime['name'], 2));

        $selectedVisits = $this->getNumericCellValue($xpath, $selectedPreProcessed['name'], 1);
        $selectedActions = $this->getNumericCellValue($xpath, $selectedPreProcessed['name'], 2);
        $this->assertNotSame('-', $selectedVisits);
        $this->assertNotSame('-', $selectedActions);
    }

    private function getRowAttribute(\DOMXPath $xpath, string $segmentName, string $attribute): string
    {
        $nodeList = $xpath->query(sprintf('//tr[@data-segment-name=%s]', $this->quoteXpathLiteral($segmentName)));
        $this->assertNotFalse($nodeList);
        $this->assertGreaterThan(0, $nodeList->length, sprintf('Could not find row for "%s"', $segmentName));

        $row = $nodeList->item(0);
        $this->assertNotNull($row);
        $attributeNode = $row->attributes->getNamedItem($attribute);
        $this->assertNotNull($attributeNode);

        return (string) $attributeNode->nodeValue;
    }

    private function getAllVisitsRowAttribute(\DOMXPath $xpath, string $attribute): string
    {
        $nodeList = $xpath->query('//tr[@data-segment-definition=""]');
        $this->assertNotFalse($nodeList);
        $this->assertGreaterThan(0, $nodeList->length, 'Could not find all visits row');

        $row = $nodeList->item(0);
        $this->assertNotNull($row);
        $attributeNode = $row->attributes->getNamedItem($attribute);
        $this->assertNotNull($attributeNode);

        return (string) $attributeNode->nodeValue;
    }

    private function getNumericCellValue(\DOMXPath $xpath, string $segmentName, int $numericCellIndex): string
    {
        $expression = sprintf(
            '(//tr[@data-segment-name=%s]/td[contains(@class,"entityTable_Numeric")])[%d]',
            $this->quoteXpathLiteral($segmentName),
            $numericCellIndex
        );
        $nodeList = $xpath->query($expression);
        $this->assertNotFalse($nodeList);
        $this->assertGreaterThan(0, $nodeList->length, sprintf('Could not find numeric cell %d for "%s"', $numericCellIndex, $segmentName));

        return trim($nodeList->item(0)->textContent);
    }

    private function quoteXpathLiteral(string $value): string
    {
        if (!str_contains($value, '"')) {
            return '"' . $value . '"';
        }
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }

        $parts = explode('"', $value);
        $quotedParts = array_map(static function (string $part): string {
            return '"' . $part . '"';
        }, $parts);

        return 'concat(' . implode(', \'"\', ', $quotedParts) . ')';
    }
}
