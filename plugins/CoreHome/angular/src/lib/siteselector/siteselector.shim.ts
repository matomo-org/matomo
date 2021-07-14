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
            [showSelectedSite]="showSelectedSite"
            [showAllSitesItem]="showAllSitesItem"
            [switchSiteOnSelect]="switchSiteOnSelect"
            [onlySitesWithAdminAccess]="onlySitesWithAdminAccess"
            [name]="inputName"
            [allSitesText]="allSitesText"
            [allSitesLocation]="allSitesLocation"
            [placeholder]="placeholder"
            [siteid]="siteid"
            [sitename]="sitename"
            (onSelectedSiteChange)="onSelectedSiteChange($event)"
        ></piwik-siteselector-downgrade>`,
        link: function (scope: any, element: any, attrs: any, ngModel: any) {
            scope.inputName = attrs.inputName;
            scope.allSitesText = attrs.allSitesText;
            scope.allSitesLocation = attrs.allSitesLocation;
            scope.placeholder = attrs.placeholder;
            scope.siteid = attrs.siteid;
            scope.sitename = attrs.sitename;

            // TODO: the default values logic from before still needs to be here...
            scope.switchSiteOnSelect = typeof scope.switchSiteOnSelect === 'undefined' ? true : scope.switchSiteOnSelect;

            scope.onSelectedSiteChange = function ($event: any) {
                scope.selectedSite = $event.data;
                if (ngModel) {
                    ngModel.$setViewValue($event.data); // TODO: does this work?
                }
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