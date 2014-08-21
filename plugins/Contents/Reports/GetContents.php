<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;

use Piwik\Plugins\Contents\Columns\ContentName;
use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetContents extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('ContentsName');
        $this->dimension     = null;
        $this->documentation = Piwik::translate('ContentsDocumentation');
        $this->dimension     = new ContentName();
        $this->order         = 35;

        $this->menuTitle   = 'Contents_Contents';
        $this->widgetTitle = $this->menuTitle;

        $this->metrics = array('nb_impressions', 'nb_conversions', 'conversion_rate');
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        if (!empty($this->dimension)) {
            $view->config->addTranslations(array('label' => $this->dimension->getName()));
        }

        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);
        $view->requestConfig->filter_sort_column = 'nb_impressions';
    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
         return array(); // eg return array(new XyzReport());
    }
}
