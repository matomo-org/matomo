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
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserSettings\Columns\Language;

class GetLanguage extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Language();
        $this->name          = Piwik::translate('UserSettings_BrowserLanguage');
        $this->documentation = ''; // TODO
        $this->order = 10;
        $this->widgetTitle  = 'UserSettings_BrowserLanguage';
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_search = false;
        $view->config->columns_to_display = array('label', 'nb_visits');
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', $this->dimension->getName());

        $view->requestConfig->filter_sort_column = 'nb_visits';
        $view->requestConfig->filter_sort_order  = 'desc';
    }

    public function getRelatedReports() {
        return array(
            new GetLanguageCode()
        );
    }

}
