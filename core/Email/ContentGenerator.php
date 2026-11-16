<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Email;

use Piwik\View;
use Piwik\View\HtmlReportEmailHeaderView;

class ContentGenerator
{
    /**
     * @param View|string $body
     * @param string|null $unsubscribeLink When set, the footer shows a line with this link to unsubscribe
     * @param string|null $unsubscribeFooterKey Translation key for that line, with a `%1$s` placeholder for
     *                                          the link. Defaults to the scheduled report wording.
     * @return string
     */
    public function generateHtmlContent($body, ?string $unsubscribeLink = null, ?string $unsubscribeFooterKey = null)
    {
        if ($body instanceof View) {
            HtmlReportEmailHeaderView::assignCommonParameters($body);
            $bodyHtml = $body->render();
        } else {
            $bodyHtml = (string)$body;
        }

        $header = new View("@CoreHome/_htmlEmailHeader.twig");
        HtmlReportEmailHeaderView::assignCommonParameters($header);
        $headerHtml = $header->render();

        $footer = new View("@CoreHome/_htmlEmailFooter.twig");
        HtmlReportEmailHeaderView::assignCommonParameters($footer);
        $footer->unsubscribeLink = $unsubscribeLink;
        $footer->unsubscribeFooterKey = $unsubscribeFooterKey;
        $footerHtml = $footer->render();

        return $headerHtml . $bodyHtml . $footerHtml;
    }
}
