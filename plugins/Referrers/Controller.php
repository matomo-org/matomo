<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers;

use Piwik\Common;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Request;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;

        parent::__construct();
    }

    public function getSparklines()
    {
        $_GET['forceView'] = '1';
        $_GET['viewDataTable'] = Sparklines::ID;

        return FrontController::getInstance()->fetchDispatch('Referrers', 'get');
    }

    public function getEvolutionGraph($typeReferrer = false, array $columns = [], array $defaultColumns = [])
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referrers.getReferrerType');

        $view->config->add_total_row = true;

        // configure displayed columns
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }
        if (false !== $columns) {
            $columns = !is_array($columns) ? array($columns) : $columns;
        }

        if (!empty($columns)) {
            $view->config->columns_to_display = $columns;
        } elseif (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        // configure selectable columns
        $period = Common::getRequestVar('period', false);

        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            $selectable = array('nb_visits', 'nb_uniq_visitors', 'nb_users', 'nb_actions');
        } else {
            $selectable = array('nb_visits', 'nb_actions');
        }
        $view->config->selectable_columns = $selectable;

        // configure displayed rows
        $view->config->row_picker_match_rows_by = 'referrer_type';

        $visibleRows = Common::getRequestVar('rows', false);
        if ($visibleRows !== false) {
            // this happens when the row picker has been used
            $visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
            $visibleRows = array_map('urldecode', $visibleRows);

            // typeReferrer is redundant if rows are defined, so make sure it's not used
            $view->config->custom_parameters['typeReferrer'] = false;
        } else {
            // use $typeReferrer as default
            if ($typeReferrer === false) {
                $typeReferrer = Request::fromRequest()->getIntegerParameter('typeReferrer', Common::REFERRER_TYPE_DIRECT_ENTRY);
            }

            if (!empty($view->config->rows_to_display)) {
                $visibleRows = $view->config->rows_to_display;
            } else {
                $visibleRows = [(string) $typeReferrer, 'total'];
            }

            $view->requestConfig->request_parameters_to_modify['rows'] = $typeReferrer . ',total';
        }

        $view->config->rows_to_display = $visibleRows;

        $view->config->documentation = $this->translator->translate('Referrers_EvolutionDocumentation') . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation') . '<br />'
            . $this->translator->translate('Referrers_EvolutionDocumentationMoreInfo', '&quot;'
                . $this->translator->translate('Referrers_ReferrerTypes') . '&quot;');

        return $this->renderView($view);
    }
}
