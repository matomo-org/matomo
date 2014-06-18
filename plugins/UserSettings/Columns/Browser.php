<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugins\DevicesDetection\Columns\BrowserName;
use Piwik\Plugins\UserSettings\Segment;

class Browser extends BrowserName
{
    protected $fieldName = 'config_browser_name';
    protected $fieldType = 'VARCHAR(10) NOT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('browserCode');
        $segment->setName('UserSettings_ColumnBrowser');
        $segment->setAcceptedValues('FF, IE, CH, SF, OP, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnBrowser');
    }
}