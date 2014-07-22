/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 *
 * <div piwik-translation-search></div>
 *
 * Will show a text box which allows the user to search for translation keys and actual translations. Currently,
 * only english is supported.
 */
angular.module('piwikApp').directive('piwikTranslationSearch', function($document, piwik, $filter){

    return {
        restrict: 'A',
        scope: {},
        templateUrl: 'plugins/LanguagesManager/angularjs/translationsearch/translationsearch.html?cb=' + piwik.cacheBuster,
        controller: 'TranslationSearchController'
    };
});