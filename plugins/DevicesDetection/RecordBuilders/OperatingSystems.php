<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicesDetection\RecordBuilders;

use Piwik\Plugins\DevicesDetection\Archiver;

class OperatingSystems extends Base
{
    public function __construct()
    {
        parent::__construct(Archiver::OS_RECORD_NAME, Archiver::OS_FIELD);
    }
}
