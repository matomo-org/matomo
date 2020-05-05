/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('TranslationSearchController', TranslationSearchController);

    TranslationSearchController.$inject = ['piwikApi'];

    function TranslationSearchController(piwikApi) {

        function fetchTranslations(languageCode) {
            piwikApi.fetch({
                method: 'LanguagesManager.getTranslationsForLanguage',
                filter_limit: -1,
                languageCode: languageCode
            }).then(function (response) {
                if (response) {
                    if (languageCode === 'en') {
                        vm.existingTranslations = response;
                    } else {
                        vm.compareTranslations = {};
                        angular.forEach(response, function (translation) {
                            vm.compareTranslations[translation.label] = translation.value;
                        });
                    }
                }
            });
        }

        function fetchLanguages() {
            piwikApi.fetch({
                method: 'LanguagesManager.getAvailableLanguagesInfo',
                filter_limit: -1
            }).then(function (languages) {
                vm.languages = [{key: '', value: 'None'}];
                if (languages) {
                    angular.forEach(languages, function (language) {
                        if (language.code === 'en') {
                            return;
                        }
                        vm.languages.push({key: language.code, value: language.name});
                    });
                }
            });
        }

        var vm = this;
        vm.compareTranslations = null;
        vm.existingTranslations = [];
        vm.languages = [];
        vm.compareLanguage = '';

        this.doCompareLanguage = function () {
            if (vm.compareLanguage) {
                vm.compareTranslations = null;
                fetchTranslations(vm.compareLanguage);
            }
        };

        fetchTranslations('en');

        fetchLanguages();

    }
})();