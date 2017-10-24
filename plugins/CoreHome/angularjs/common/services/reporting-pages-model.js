/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp.service').factory('reportingPagesModel', reportingPagesModelService);

    reportingPagesModelService.$inject = ['piwikApi'];

    function reportingPagesModelService (piwikApi) {
        var fetchAllPagesPromise = false;

        // those sites are going to be displayed
        var model = {
            pages : [],
            findPage: findPage,
            findPageInCategory: findPageInCategory,
            reloadAllPages : reloadAllPages,
            getAllPages : getAllPages
        };

        return model;

        function findPageInCategory(categoryId) {
            var found = null;

            angular.forEach(model.pages, function (page) {
                // happens when user switches between sites, in this case check if the same category exists and if so,
                // select first entry from that category
                if (!found && page &&
                    page.category && page.subcategory &&
                    page.category.id === categoryId && page.subcategory.id) {
                    found = page;
                }
            });

            return found;
        }

        function findPage(categoryId, subcategoryId)
        {
            var found = null;

            angular.forEach(model.pages, function (page) {
                if (!found &&
                    page &&
                    page.category && page.subcategory &&
                    page.category.id === categoryId && ('' + page.subcategory.id) === subcategoryId) {
                    found = page;
                }
            });

            return found;
        }

        function reloadAllPages()
        {
            fetchAllPagesPromise = null;
            return getAllPages();
        }

        function getAllPages()
        {
            if (!fetchAllPagesPromise) {
                fetchAllPagesPromise = piwikApi.fetch({method: 'API.getReportPagesMetadata', filter_limit: '-1'}).then(function (response) {
                    model.pages = response;
                    return response;
                });
            }

            return fetchAllPagesPromise;
        }
    }
})();