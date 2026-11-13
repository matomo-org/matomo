<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Fixtures;

use Piwik\Archive;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\Actions\Archiver as ActionsArchiver;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;

/**
 * Selects two page URLs for pre-processed Transitions and archives the first one's day, so the report can be served
 * from its stored data, together with the page URLs the report offers. The entries get fixed dates and creators, as
 * the admin page shows them.
 */
class PreprocessedPages extends SomeVisitsManyPageviewsWithTransitions
{
    public const ARCHIVED_PAGE_URL = 'http://example.org/page/one.html';
    public const OTHER_PAGE_URL = 'http://example.org/sub/dir/page2.html';
    public const DAY = '2010-03-06';

    public function setUp(): void
    {
        parent::setUp();

        $this->selectPage(self::ARCHIVED_PAGE_URL, '2026-11-02 09:14:00', 'superUserLogin');
        // added by a user who has since been deleted
        $this->selectPage(self::OTHER_PAGE_URL, '2026-10-02 14:51:00', null);

        $idAction = StaticContainer::get(Service::class)->getIdActionForUrl(self::ARCHIVED_PAGE_URL);
        if ($idAction === null) {
            throw new \Exception('The page to archive was not tracked.');
        }

        // the UI spec runs without browser archiving, so the reports it opens are archived here
        $archive = Archive::build($this->idSite, 'day', self::DAY);
        $archive->getNumeric(['nb_visits', ActionsArchiver::METRIC_PAGEVIEWS_RECORD_NAME, Records::getRebuildReportName($idAction)]);
        $archive->getDataTable(ActionsArchiver::PAGE_URLS_RECORD_NAME);
    }

    private function selectPage(string $url, string $createdDate, ?string $createdBy): void
    {
        $entry = API::getInstance()->addPreprocessedPage($this->idSite, $url);

        Db::query(
            'UPDATE ' . Common::prefixTable(Dao::TABLE_NAME) . ' SET created_date = ?, created_by = ? WHERE idpage = ?',
            [$createdDate, $createdBy, $entry['idpage']]
        );
    }
}
