<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;

class SegmentNotSupportedMessageHelper
{
    public static function addSegmentNotSupportedMessage(ViewDataTable $view): void
    {
        if (!self::isSegmentApplied()) {
            return;
        }

        // The footer container spaces its items apart, so the message has to be one.
        $message = '<div class="datatableFooterMessage__item">'
            . '<p class="alert alert-info">' . Piwik::translate('BotTracking_SegmentNotSupported') . '</p>'
            . '</div>';
        $existing = $view->config->show_footer_message;

        // The real time reports already carry a row-limit footer message; replacing it would drop it.
        $view->config->show_footer_message = is_string($existing) && $existing !== ''
            ? $existing . $message
            : $message;
    }

    private static function isSegmentApplied(): bool
    {
        // Comparisons keep the compared segments in their own parameter, where "All visits" is an empty entry.
        $segments = Common::getRequestVar('compareSegments', [], 'array');
        $segments[] = Common::getRequestVar('segment', '', 'string');

        foreach ($segments as $segment) {
            if ('' !== trim((string) $segment)) {
                return true;
            }
        }

        return false;
    }
}
