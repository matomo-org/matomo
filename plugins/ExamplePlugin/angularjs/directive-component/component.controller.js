/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('ComponentController', ComponentController);

    ComponentController.$inject = [];

    function ComponentController() {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var vm = this;
        vm.myProperty  = 'component';
        vm.doSomething = doSomething;

        function doSomething() {

        }
    }
})();