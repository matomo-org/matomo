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
            show-selected-site="shim.showSelectedSite"
            show-all-sites-item="shim.showAllSitesItem"
            switch-site-on-select="shim.switchSiteOnSelect"
            only-sites-with-admin-access="shim.onlySitesWithAdminAccess"
            name="shim.inputName"
            all-sites-text="shim.allSitesText"
            all-sites-location="shim.allSitesLocation"
            placeholder="shim.placeholder"
            on-selected-site-change="shim.onSelectedSiteChange($event)"
        ></piwik-siteselector-downgrade>`,
        controllerAs: 'shim',
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