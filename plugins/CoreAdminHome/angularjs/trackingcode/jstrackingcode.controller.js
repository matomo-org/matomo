/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller for javascript tracking code generator
 */
(function () {

    // gets the list of custom variables entered by the user in a custom variable section
    function getCustomVariables(customVars) {
        var result = [];
        angular.forEach(customVars, function (customVar) {
            result.push([customVar.name, customVar.value]);
        });
        return result;
    };

    // quickly gets the host + port from a url
    function getHostNameFromUrl(url) {
        var element = $('<a></a>')[0];
        element.href = url;
        return element.hostname;
    };

    angular.module('piwikApp').controller('JsTrackingCodeController', JsTrackingCodeController);

    JsTrackingCodeController.$inject = ['$scope', 'piwikApi'];

    function JsTrackingCodeController($scope, piwikApi) {

        this.showAdvanced = false;
        this.isLoading = false;
        this.customVars = [];
        this.siteUrls = {};
        this.hasManySiteUrls = false;
        this.maxCustomVariables = parseInt(angular.element('[name=numMaxCustomVariables]').val(), 10);
        this.canAddMoreCustomVariables = this.maxCustomVariables && this.maxCustomVariables > 0;

        // get preloaded server-side data necessary for code generation
        var piwikHost = window.location.host,
            piwikPath = location.pathname.substring(0, location.pathname.lastIndexOf('/')),
            self = this;

        // queries Piwik for needed site info for one site
        var getSiteData = function (idSite, sectionSelect, callback) {
            // if data is already loaded, don't do an AJAX request
            if (self.siteUrls[idSite]) {

                callback();
                return;
            }

            // disable section
            self.isLoading = true;

            piwikApi.fetch({
                module: 'API',
                method: 'SitesManager.getSiteUrlsFromId',
                idSite: idSite,
                filter_limit: '-1'
            }).then(function (data) {
                self.siteUrls[idSite] = data || [];

                // re-enable controls
                self.isLoading = false;

                callback();
            });
        };


        // function that generates JS code
        var generateJsCodeAjax = null;
        var generateJsCode = function (trackingCodeChangedManually) {
            // get params used to generate JS code
            var params = {
                piwikUrl: piwikHost + piwikPath,
                groupPageTitlesByDomain: self.groupByDomain ? 1 : 0,
                mergeSubdomains: self.trackAllSubdomains ? 1 : 0,
                mergeAliasUrls: self.trackAllAliases ? 1 : 0,
                visitorCustomVariables: self.trackCustomVars ? getCustomVariables(self.customVars) : 0,
                customCampaignNameQueryParam: null,
                customCampaignKeywordParam: null,
                doNotTrack: self.doNotTrack ? 1 : 0,
                disableCookies: self.disableCookies ? 1 : 0,
                crossDomain: self.crossDomain ? 1 : 0,
                trackNoScript: self.trackNoScript ? 1: 0
            };

            if (self.useCustomCampaignParams) {
                params.customCampaignNameQueryParam = self.customCampaignName;
                params.customCampaignKeywordParam = self.customCampaignKeyword;
            }

            if (generateJsCodeAjax) {
                generateJsCodeAjax.abort();
            }

            generateJsCodeAjax = piwikApi.post({
                module: 'API',
                format: 'json',
                method: 'SitesManager.getJavascriptTag',
                idSite: self.site.id
            }, params).then(function (response) {
                generateJsCodeAjax = null;

                self.trackingCode = response.value;

                if(trackingCodeChangedManually) {
                    var jsCodeTextarea = $('#javascript-text .codeblock');
                    jsCodeTextarea.effect("highlight", {}, 1500);
                }
            });

            return generateJsCodeAjax;
        };

        this.onCrossDomainToggle = function () {
            if (this.crossDomain) {
                this.trackAllAliases = true;
            }
        };

        this.addCustomVar = function () {
            if (this.canAddMoreCustomVariables) {
                this.customVars.push({name: '', value: ''});
            }

            this.canAddMoreCustomVariables = this.maxCustomVariables > this.customVars.length;
        };

        this.addCustomVar();

        this.updateTrackingCode = function () {
            generateJsCode(true);
        };

        this.changeSite = function (trackingCodeChangedManually) {

            $('.current-site-name').html(self.site.name);

            getSiteData(this.site.id, '#js-code-options', function () {

                self.hasManySiteUrls = self.siteUrls[self.site.id] && self.siteUrls[self.site.id].length > 1;

                if (!self.hasManySiteUrls) {
                    self.crossDomain = false; // we make sure to disable cross domain if it has only one url or less
                }

                var siteHost = getHostNameFromUrl(self.siteUrls[self.site.id][0]);
                $('.current-site-host').text(siteHost);

                var defaultAliasUrl = 'x.' + siteHost;
                $('.current-site-alias').text(self.siteUrls[self.site.id][1] || defaultAliasUrl);

                generateJsCode(true);
            });
        };

        if (this.site && this.site.id) {
            this.changeSite(false);
        }
    }
})();