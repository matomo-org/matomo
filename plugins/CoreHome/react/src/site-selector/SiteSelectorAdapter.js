import {SiteSelector} from "./SiteSelector";

const { angular } = window;

angular.module('piwikApp').directive('piwikSiteselector', piwikSiteselector);

piwikSiteselector.$inject = ['piwik', '$filter', '$timeout'];

function piwikSiteselector(piwik, $filter, $timeout){
    var defaults = {
        name: '',
        siteid: piwik.idSite,
        sitename: piwik.helper.htmlDecode(piwik.siteName),
        allSitesLocation: 'bottom',
        allSitesText: $filter('translate')('General_MultiSitesSummary'),
        showSelectedSite: 'false',
        showAllSitesItem: 'true',
        switchSiteOnSelect: 'true',
        onlySitesWithAdminAccess: 'false'
    };

    return {
        restrict: 'A',
        scope: {
            showSelectedSite: '=',
            showAllSitesItem: '=',
            switchSiteOnSelect: '=',
            onlySitesWithAdminAccess: '=',
            inputName: '@name',
            allSitesText: '@',
            allSitesLocation: '@',
            placeholder: '@'
        },
        require: "?ngModel",
        compile: function (element, attrs) {

            for (var index in defaults) {
                if (attrs[index] === undefined) {
                    attrs[index] = defaults[index];
                }
            }

            return {
                link: function (scope, element, attrs, ngModel) {


                    if (ngModel) {
                        ngModel.$setViewValue(scope.selectedSite);
                    }

                    scope.onSiteSelected = function (selectedSite) {
                        if (scope.selectedSite != selectedSite) {
                            scope.selectedSite = Object.assign({}, selectedSite);

                            element.attr('siteid', selectedSite.id);
                            element.trigger('change', scope.selectedSite);

                            ngModel.$setViewValue(selectedSite);
                        }
                    };

                    if (ngModel) {
                        ngModel.$render = function() {
                            if (angular.isString(ngModel.$viewValue)) {
                                scope.selectedSite = JSON.parse(ngModel.$viewValue);
                            } else {
                                scope.selectedSite = ngModel.$viewValue;
                            }
                        };
                    }

                    $timeout(function () {
                        window.initTopControls();
                    });
                },
                post: function postLink( scope, element, attrs ) {
                    $timeout(function(){
                        SiteSelector.renderTo(element[0], scope);
                    });
                }
            };
        }
    };
}
