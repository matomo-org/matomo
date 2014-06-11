<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugin\ActionDimension;
use Piwik\Plugins\Actions\Segment;
use Piwik\Tracker\Request;

class PageTitle extends ActionDimension
{
    protected $fieldName = 'idaction_name';
    protected $fieldType = 'INTEGER(10) UNSIGNED';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('pageTitle');
        $segment->setName('Actions_ColumnPageName');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnPageName');
    }
/*
    public function shouldHandleAction(Request $request)
    {
        return true;
    }

    public function getActionId()
    {
        return 4;
    }*/
}
