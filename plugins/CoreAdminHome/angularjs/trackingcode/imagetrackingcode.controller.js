/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller for image tracking code generator
 */
(function () {

    // cache for not refetching data for same site twice
    var sitePromises = {}, goalPromises = {};

    angular.module('piwikApp').controller('ImageTrackingCodeController', ImageTrackingCodeController);

    ImageTrackingCodeController.$inject = ['piwikApi', '$q'];

    function ImageTrackingCodeController(piwikApi, $q) {

        this.allGoals = {};
        this.isLoading = false;

        var piwikHost = window.location.host,
            piwikPath = location.pathname.substring(0, location.pathname.lastIndexOf('/')),
            self = this;

        var currencyPromise = piwikApi.fetch({method: 'SitesManager.getCurrencySymbols', filter_limit: '-1'});

        function requestSiteData(idSite)
        {
            if (!sitePromises[idSite]) {
                sitePromises[idSite] = piwikApi.fetch({
                    module: 'API',
                    method: 'SitesManager.getSiteFromId',
                    idSite: idSite
                });
            }

            return sitePromises[idSite];
        }

        function requestGoalData(idSite)
        {
            if (!goalPromises[idSite]) {
                goalPromises[idSite] = piwikApi.fetch({
                    module: 'API',
                    method: 'Goals.getGoals',
                    filter_limit: '-1',
                    idSite: idSite
                });
            }

            return goalPromises[idSite];
        }

        // function that generates image tracker link
        var generateImageTrackingAjax = null,
            generateImageTrackerLink = function (trackingCodeChangedManually) {
            // get data used to generate the link
            var postParams = {
                piwikUrl: piwikHost + piwikPath,
                actionName: self.pageName
            };

            if (self.trackGoal && self.trackIdGoal) {
                postParams.idGoal = self.trackIdGoal;
                postParams.revenue = self.revenue;
            }

            if (generateImageTrackingAjax) {
                generateImageTrackingAjax.abort();
            }

            generateImageTrackingAjax = piwikApi.post({
                module: 'API',
                format: 'json',
                method: 'SitesManager.getImageTrackingCode',
                idSite: self.site.id
            }, postParams).then(function (response) {
                generateImageTrackingAjax = null;

                self.trackingCode = response.value;

                if (trackingCodeChangedManually) {
                    var jsCodeTextarea = $('#image-tracking-text .codeblock');
                    jsCodeTextarea.effect("highlight", {}, 1500);
                }
            });
        };

        this.updateTrackingCode = function () {
            generateImageTrackerLink(true);
        };

        this.changeSite = function (changedManually) {

            self.isLoading = true;

            var sitePromise = requestSiteData(this.site.id);
            var goalPromise =  requestGoalData(this.site.id);

            return $q.all([currencyPromise, sitePromise, goalPromise]).then(function (data) {

                self.isLoading = false;

                var currencySymbols = data[0] || {};
                var currency = data[1].currency || '';
                var goals = data[2] || [];

                var goalsList = [{key: '', value: _pk_translate('UserCountryMap_None')}];
                for (var key in goals) {
                    goalsList.push({key: goals[key].idgoal, value: goals[key].name});
                }

                self.allGoals = goalsList;

                $('[name=image-revenue] .site-currency').text(currencySymbols[currency.toUpperCase()]);
                generateImageTrackerLink(changedManually);

            });
        };

        if (this.site && this.site.id) {
            this.changeSite(false);
        }

    }
})();