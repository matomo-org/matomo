<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Email;

use Piwik\View;
use Piwik\View\HtmlReportEmailHeaderView;

class ContentGenerator
{
    public function generateHtmlContent(View $body)
    {
        HtmlReportEmailHeaderView::assignCommonParameters($body);
        $bodyHtml = $body->render();

        $header = new View("@CoreHome/_htmlEmailHeader.twig");
        HtmlReportEmailHeaderView::assignCommonParameters($header);
        $headerHtml = $header->render();

        $footer = new View("@CoreHome/_htmlEmailFooter.twig");
        HtmlReportEmailHeaderView::assignCommonParameters($footer);
        $footerHtml = $footer->render();

        return $headerHtml . $bodyHtml . $footerHtml;
    }
}
