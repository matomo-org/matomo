<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Reports;

use Piwik\Piwik;
use Piwik\Plugins\UserSettings\Columns\Language;

class GetLanguageCode extends GetLanguage
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Language();
        $this->name          = Piwik::translate('UserSettings_LanguageCode');
        $this->documentation = '';
        $this->order = 11;
        $this->widgetTitle  = 'UserSettings_LanguageCode';
    }

    public function getRelatedReports()
    {
        return array(
            new GetLanguage()
        );
    }

}
