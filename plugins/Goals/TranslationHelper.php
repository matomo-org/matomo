<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals;


use Piwik\Piwik;

class TranslationHelper
{

    public function translateGoalMetricCategory($category)
    {
        // Return either "Goals by %s" or "Goals %s", depending on the category
        if ($category === 'General_Visit') {
                return Piwik::translate('Goals_GoalsAdjective', Piwik::translate('Goals_CategoryText' . $category));
        }
        return Piwik::translate('Goals_GoalsBy', Piwik::translate('Goals_CategoryText' . $category));
    }

    public function translateEcommerceMetricCategory($category)
    {
        // Return either "Sales by %s" or "Sales %s", depending on the category
        if ($category === 'General_Visit') {
                return Piwik::translate('Ecommerce_SalesAdjective', Piwik::translate('Goals_CategoryText' . $category));
        }
        return Piwik::translate('Ecommerce_SalesBy', Piwik::translate('Goals_CategoryText' . $category));
    }

    public function getTranslationForCompleteDescription($match, $patternType, $pattern)
    {
        $description = $this->getTranslationForMatchAttribute($match);
        if($this->isPatternUsedForMatchAttribute($match)) {
            $description = sprintf(
                '%s %s',
                $description,
                $this->getTranslationForPattern(
                    $patternType,
                    $pattern
                )
            );
        }

        return $description;
    }

    protected function isPatternUsedForMatchAttribute($match)
    {
        return in_array(
            $match,
            array('url', 'title', 'event_category', 'event_action', 'event_name', 'file', 'external_website')
        );
    }

    protected function getTranslationForMatchAttribute($match)
    {
        switch ($match) {
            case 'manually':
                return Piwik::translate('Goals_ManuallyTriggeredUsingJavascriptFunction');

            case 'url':
                return Piwik::translate('Goals_VisitUrl');

            case 'title':
                return Piwik::translate('Goals_VisitPageTitle');

            case 'event_category':
            case 'event_action':
            case 'event_name':
                return Piwik::translate('Goals_SendEvent');

            case 'file':
                return Piwik::translate('Goals_Download');

            case 'external_website':
                return Piwik::translate('Goals_ClickOutlink');

            default:
                return '';
        }
    }

    protected function getTranslationForPattern($patternType, $pattern)
    {
        switch ($patternType) {
            case 'regex':
                return sprintf('%s %s',
                    Piwik::translate('Goals_Pattern'),
                    Piwik::translate('Goals_MatchesExpression', array($pattern))
                );

            case 'contains':
                return sprintf('%s %s',
                    Piwik::translate('Goals_Pattern'),
                    Piwik::translate('Goals_Contains', array($pattern))
                );

            case 'exact':
                return sprintf('%s %s',
                    Piwik::translate('Goals_Pattern'),
                    Piwik::translate('Goals_IsExactly', array($pattern))
                );

            default:
                return '';
        }
    }
}