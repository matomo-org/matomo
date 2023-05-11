<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\Actions\Metrics;
use Piwik\RankingQuery;
use Piwik\Tracker\Action;

// TODO: remove
abstract class Base extends RecordBuilder
{
}