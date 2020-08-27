<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\View;


use Piwik\View;

class HtmlEmailFooterView extends View
{
    const TEMPLATE_FILE = '@CoreHome/ReportRenderer/_htmlReportFooter';

    public function __construct($unsubscribeLink = null)
    {
        parent::__construct(self::TEMPLATE_FILE);

        HtmlReportEmailHeaderView::assignCommonParameters($this);

        $this->unsubscribeLink = $unsubscribeLink;
    }
}