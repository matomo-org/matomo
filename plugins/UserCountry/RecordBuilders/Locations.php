<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserCountry\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;

class Locations extends RecordBuilder
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            // TODO
        ];
        // TODO: Implement getRecordMetadata() method.
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        // TODO: Implement aggregate() method.
    }
}
