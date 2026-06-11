<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AITraffic;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Url;

class AITraffic extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Template.beforeContent' => 'addMovingToAIInsightsNotice',
        ];
    }

    public function addMovingToAIInsightsNotice(&$out, $context)
    {
        if ($context !== 'dashboard') {
            return;
        }
        if (Common::getRequestVar('category', '', 'string') !== 'General_AIAssistants') {
            return;
        }

        $url = 'index.php' . Url::getCurrentQueryStringWithParametersModified([
            'module' => 'AITraffic',
            'action' => 'index',
            'subcategory' => false,
        ]);

        $message = Piwik::translate('AITraffic_MovingToAIInsights');
        $linkText = Piwik::translate('AITraffic_ViewInAIInsights');

        $out .= '<div class="alert alert-info">'
            . htmlspecialchars($message)
            . ' <a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($linkText) . '</a>'
            . '</div>';
    }
}
