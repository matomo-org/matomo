/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

angular.module('piwikApp').controller('TranslationSearchController', function($scope, piwikApi) {

    $scope.existingTranslations = [];

    piwikApi.fetch({
        method: 'LanguagesManager.getTranslationsForLanguage',
        languageCode: 'en'
    }).then(function (response) {
        if (response) {
            $scope.existingTranslations = response;
        }
    });

});
