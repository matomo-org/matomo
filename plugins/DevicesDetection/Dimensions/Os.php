<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection\Dimensions;

use Piwik\Piwik;
use Piwik\Tracker\Request;

class Os extends Base
{
    protected $fieldName = 'config_os';
    protected $fieldType = 'CHAR(3) NOT NULL';

    public function getName()
    {
        return Piwik::translate('DevicesDetection_OperatingSystemFamilies');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getOs("short_name");
    }
}
