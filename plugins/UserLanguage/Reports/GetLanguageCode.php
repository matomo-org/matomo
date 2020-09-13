<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserLanguage\Reports;

use Piwik\Piwik;
use Piwik\Plugins\UserLanguage\Columns\Language;
use Piwik\Plugin\ReportsProvider;

class GetLanguageCode extends GetLanguage
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Language();
        $this->name          = Piwik::translate('UserLanguage_LanguageCode');
        $this->documentation = Piwik::translate('UserLanguage_getLanguageCodeDocumentation');
        $this->order = 11;
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('UserLanguage', 'getLanguage'),
        );
    }

}
