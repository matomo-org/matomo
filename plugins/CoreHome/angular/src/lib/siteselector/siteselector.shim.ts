declare var angular: angular.IAngularStatic;

piwikSiteselectorShim.$inject = ['$timeout'];

export function piwikSiteselectorShim($timeout: any) {
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
        template: `<piwik-siteselector-downgrade
            [show-selected-site]="showSelectedSite"
            [show-all-sites-item]="showAllSitesItem"
            [switch-site-on-select]="switchSiteOnSelect"
            [only-sites-with-admin-access]="onlySitesWithAdminAccess"
            [name]="inputName"
            [all-sites-text]="allSitesText"
            [all-sites-location]="allSitesLocation"
            [placeholder]="placeholder"
            [siteid]="siteid"
            [sitename]="sitename"
            (on-selected-site-change)="onSelectedSiteChange($event)"
        ></piwik-siteselector-downgrade>`,
        link: function (scope: any, element: any, attrs: any, ngModel: any) {
            scope.inputName = attrs.inputName;
            scope.allSitesText = attrs.allSitesText;
            scope.allSitesLocation = attrs.allSitesLocation;
            scope.placeholder = attrs.placeholder;
            scope.siteid = attrs.siteid;
            scope.sitename = attrs.sitename;
            scope.onSelectedSiteChange = function ($event: any) {
                scope.selectedSite = $event.data;
                ngModel.$setViewValue($event.data); // TODO: does this work?
            };

            if (ngModel) {
                ngModel.$setViewValue(scope.selectedSite);
            }

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
                (window as any).initTopControls();
            });
        },
    };
}