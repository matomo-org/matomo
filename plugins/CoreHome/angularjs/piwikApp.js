/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function () {
    angular.module('piwikApp', [
        'ngSanitize',
        'ngAnimate',
        'ngCookies',
        'piwikApp.config',
        'piwikApp.service',
        'piwikApp.directive',
        'piwikApp.filter'
    ]);
    angular.module('app', []);

    angular.module('piwikApp').config(['$locationProvider', function($locationProvider) {
        $locationProvider.html5Mode({ enabled: false, rewriteLinks: false }).hashPrefix('');
    }]);
})();

var AppModule = /** @class */ (function () {
    function AppModule(upgrade) {
        this.upgrade = upgrade;
    }
    AppModule.prototype.ngDoBootstrap = function () {
        this.upgrade.bootstrap(document.documentElement, ['piwikApp'], { strictDi: false });
            angular.module('piwikApp').factory('$location', ng.upgrade.static.downgradeInjectable(ng.common.upgrade.$locationShim));
        document.addEventListener('DOMContentLoaded', function () {
           piwikHelper.compileAngularComponents(document.body);
        });
    };
    return AppModule;
}());
AppModule.decorators = [
    { type: ng.core.NgModule, args: [{
            imports: [
                ng.platformBrowser.BrowserModule, ng.upgrade.static.UpgradeModule,
                window['core-home'].CoreHomeModule
            ]
        },] }
];
AppModule.ctorParameters = function () { return [
    { type: ng.upgrade.static.UpgradeModule }
]; };
//
ng.platformBrowserDynamic
    .platformBrowserDynamic().bootstrapModule(AppModule);
//});
