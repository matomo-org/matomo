/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PiwikMarketplaceController', PiwikMarketplaceController);

    PiwikMarketplaceController.$inject = ['piwik'];

    function PiwikMarketplaceController(piwik) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        this.changePluginSort = function () {
            piwik.broadcast.propagateNewPage('query=&sort=' + this.pluginSort);
        };

        this.changePluginType = function () {
            piwik.broadcast.propagateNewPage('query=&show=' + this.pluginType);
        };
    }
})();