<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicePlugins\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\ArchiveProcessor\RecordBuilder;
use Piwik\Plugins\DevicePlugins\Archiver;

class DevicePlugins extends RecordBuilder
{
    public function getRecordMetadata(ArchiveProcessor $archiveProcessor)
    {
        return [
            Record::make(Record::TYPE_BLOB, Archiver::PLUGIN_RECORD_NAME),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor)
    {
        // TODO: Implement aggregate() method.
    }
}