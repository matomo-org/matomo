<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\RecordBuilders;

use Piwik\Plugins\DevicesDetection\Archiver;

class ClientTypes extends Base
{
    public function __construct()
    {
        parent::__construct(Archiver::CLIENT_TYPE_RECORD_NAME, Archiver::CLIENT_TYPE_FIELD, true);
    }
}
