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

class BrowserName extends Base
{
    protected $fieldName = 'config_browser_name';
    protected $fieldType = 'VARCHAR(10) NOT NULL';

    public function getName()
    {
        return Piwik::translate('UserSettings_BrowserFamilies');
    }

    public function onNewVisit(Request $request, $visit)
    {
        $userAgent = $request->getUserAgent();
        $parser    = $this->getUAParser($userAgent);

        return $parser->getBrowser("short_name");
    }
}
