<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage\Reports;

use Piwik\Piwik;
use Piwik\Plugins\UserLanguage\Columns\Language;

class GetLanguageCode extends GetLanguage
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Language();
        $this->name          = Piwik::translate('UserLanguage_LanguageCode');
        $this->documentation = '';
        $this->order = 11;
        $this->widgetTitle  = 'UserLanguage_LanguageCode';
    }

    public function getRelatedReports()
    {
        return array(
            self::factory('UserLanguage', 'getLanguage'),
        );
    }

}
