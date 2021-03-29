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

var HelloComponent = function () {
    var angular1Dependency =  piwikHelper.getAngularDependency('piwikApi');
    console.log(angular1Dependency);
};
//HelloComponent.parameters = [[new ng.core.Inject(ng.common.http.HttpClient)]];
HelloComponent.annotations = [
    new ng.core.Component({
        selector: 'app-root',
        template: 'Hello World!'
    })
];

var AppModule = function () {
    this.ngDoBootstrap = function (app) {
        // this.upgrade.bootstrap(document.getElementById('angularRoot'), ['HelloComponent']); //, { strictDi: true }
        app.bootstrap(document.getElementById('angularRoot'), ['HelloComponent']); //, { strictDi: true }
    }
};
AppModule.annotations = [
    new ng.core.NgModule({
        imports: [ng.platformBrowser.BrowserModule, ng.upgrade.static.UpgradeModule],
        declarations: [HelloComponent],
        entryComponents: [],
        bootstrap: [HelloComponent],
    })
];document.addEventListener('DOMContentLoaded', function () {
    ng.platformBrowserDynamic
        .platformBrowserDynamic().bootstrapModule(AppModule);
});
