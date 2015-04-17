/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('TranslationSearchController', TranslationSearchController);

    TranslationSearchController.$inject = ['piwikApi'];

    function TranslationSearchController(piwikApi) {

        var vm = this;
        vm.existingTranslations = [];

        fetchTranslations();

        function fetchTranslations() {
            piwikApi.fetch({
                method: 'LanguagesManager.getTranslationsForLanguage',
                filter_limit: -1,
                languageCode: 'en'
            }).then(function (response) {
                if (response) {
                    vm.existingTranslations = response;
                }
            });
        }
    }
})();