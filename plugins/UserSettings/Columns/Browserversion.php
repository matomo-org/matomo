<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugins\UserSettings\Segment;

class Browserversion extends \Piwik\Plugins\DevicesDetection\Columns\BrowserVersion
{    
    protected $fieldName = 'config_browser_version';
    protected $fieldType = 'VARCHAR(20) NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('browserVersion');
        $segment->setName('UserSettings_ColumnBrowserVersion');
        $segment->setAcceptValues('1.0, 8.0, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnBrowserVersion');
    }
}