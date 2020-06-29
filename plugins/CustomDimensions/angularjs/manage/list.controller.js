/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('CustomDimensionsListController', CustomDimensionsListController);

    CustomDimensionsListController.$inject = ['customDimensionsModel', 'piwik'];

    function CustomDimensionsListController(customDimensionsModel, piwik) {
        customDimensionsModel.fetchCustomDimensionsConfiguration();

        this.siteName = piwik.siteName;

        this.model = customDimensionsModel;
    }
})();