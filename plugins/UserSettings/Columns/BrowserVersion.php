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
use Piwik\Plugins\UserSettings\Segment;

class BrowserVersion extends \Piwik\Plugins\DevicesDetection\Columns\BrowserVersion
{
    protected $columnName = 'config_browser_version';
    protected $columnType = 'VARCHAR(20) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('browserVersion');
        $segment->setName('UserSettings_ColumnBrowserVersion');
        $segment->setAcceptedValues('1.0, 8.0, etc.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnBrowserVersion');
    }
}