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
(function () {
    angular.module('piwikApp').directive('piwikTranslationSearch', piwikTranslationSearch);

    piwikTranslationSearch.$inject = ['piwik'];

    function piwikTranslationSearch(piwik){

        return {
            restrict: 'A',
            scope: {},
            templateUrl: 'plugins/LanguagesManager/angularjs/translationsearch/translationsearch.directive.html?cb=' + piwik.cacheBuster,
            controller: 'TranslationSearchController',
            controllerAs: 'translationSearch'
        };
    }
})();