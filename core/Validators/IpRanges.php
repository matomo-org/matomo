<?php
/**
 * Matomo free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Matomo\Network\IPUtils;
use Piwik\Piwik;

class IpRanges extends BaseValidator
{
    public function validate($value)
    {
        if (!empty($value)) {
            if (!is_array($value)) {
                throw new Exception('The IP ranges need to be an array');
            }
            $ips = array_map('trim', $value);
            $ips = array_filter($ips, 'strlen');

            foreach ($ips as $ip) {
                if (IPUtils::getIPRangeBounds($ip) === null) {
                    throw new Exception(Piwik::translate('SitesManager_ExceptionInvalidIPFormat', array($ip, "1.2.3.4, 1.2.3.*, or 1.2.3.4/5")));
                }
            }
        }
    }
}