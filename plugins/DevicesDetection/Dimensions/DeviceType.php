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

class DeviceType extends Base
{
    protected $fieldName = 'config_device_type';
    protected $fieldType = 'TINYINT( 100 ) NULL DEFAULT NULL';

    public function getName()
    {
        return Piwik::translate('DevicesDetection_DeviceType');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getDevice();
    }
}
