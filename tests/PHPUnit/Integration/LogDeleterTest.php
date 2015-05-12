<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class LogDeleterTest extends IntegrationTestCase
{
    // TODO

    public function test_deleteVisits_RemovesVisitsAndOtherRelatedLogs()
    {
        // TODO
    }

    public function test_deleteVisitActions_RemovesVisitActionsOnly()
    {
        // TODO
    }

    public function test_deleteConversions_RemovesConversionsAndConversionItems()
    {
        // TODO
    }

    public function test_deleteConversionItems_RemovesConversionItems()
    {
        // TODO
    }

    public function test_deleteVisitsFor_DeletesVisitsForSpecifiedRangeAndSites_AndInvokesCallbackAfterEveryChunkIsDeleted()
    {
        // TODO
    }
}