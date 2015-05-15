/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('AjaxFormController', AjaxFormController);

    AjaxFormController.$inject = ['piwikApi', '$filter'];

    function AjaxFormController(piwikApi, $filter) {
        var vm = this;

        /**
         * Set to non-null when a form submit request returns successfully. When successful, it will
         * be the entire JSON parsed response of the request.
         *
         * @type {null|string}
         */
        vm.successfulPostResponse = null;

        /**
         * Set to non-null when a form submit request results in an error. When an error occurs,
         * it will be set to the string error message.
         *
         * @type {null|string}
         */
        vm.errorPostResponse = null;

        /**
         * true if currently submitting a POST request, false if otherwise.
         *
         * @type {bool}
         */
        vm.isSubmitting = false;

        vm.submitForm = submitForm;

        /**
         * Sends a POST to the configured API method.
         */
        function submitForm() {
            var postParams;

            vm.successfulPostResponse = null;
            vm.errorPostResponse = null;

            if (vm.sendJsonPayload) {
                postParams = {data: JSON.stringify(vm.data)};
            } else {
                postParams = vm.data;
            }

            vm.isSubmitting = true;
            piwikApi.post(
                { // GET params
                    module: 'API',
                    method: vm.submitApiMethod
                },
                postParams,
                { // request options
                    createErrorNotification: !vm.noErrorNotification
                }
            ).then(function (response) {
                vm.successResponse = response;

                if (!vm.noSuccessNotification) {
                    var UI = require('piwik/UI');
                    var notification = new UI.Notification();
                    notification.show($filter('translate')('General_YourChangesHaveBeenSaved'), {
                        context: 'success',
                        type: 'toast',
                        id: 'ajaxHelper'
                    });
                    notification.scrollToNotification();
                }
            })['catch'](function (errorMessage) {
                vm.errorPostResponse = errorMessage;
            })['finally'](function () {
                vm.isSubmitting = false;
            });
        }
    }
})();