/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('TranslationSearchController', TranslationSearchController);

    function TranslationSearchController($scope, piwikApi) {

        $scope.existingTranslations = [];

        piwikApi.fetch({
            method: 'LanguagesManager.getTranslationsForLanguage',
            languageCode: 'en'
        }).then(function (response) {
            if (response) {
                $scope.existingTranslations = response;
            }
        });
    }
})();