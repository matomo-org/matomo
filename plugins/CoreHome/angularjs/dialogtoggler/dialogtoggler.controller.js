/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Controller for the piwikDialogToggler directive. Adds a couple methods to the
 * scope allowing elements to open and close dialogs.
 */
(function () {
    angular.module('piwikApp').controller('DialogTogglerController', DialogTogglerController);

    DialogTogglerController.$inject = ['$scope', 'piwik', 'ngDialog', 'piwikDialogtogglerUrllistener'];

    function DialogTogglerController($scope, piwik, ngDialog, piwikDialogtogglerUrllistener) {
        /**
         * Open a new dialog window using ngDialog.
         *
         * @param {object|string} contentsInfo If an object, it is assumed to be ngDialog open(...) config and is
         *                                     passed to ngDialog.open unaltered.
         *                                     If a string that beings with '#', we assume it is an ID of an element
         *                                     with the dialog contents. (Note: ngDialog doesn't appear to support arbitrary
         *                                     selectors).
         *                                     If a string that ends with .html, we assume it is a link to a an angular
         *                                     template.
         *                                     Otherwise we assume it is a raw angular
         * @return {object} Returns the result of ngDialog.open. Can be used to close the dialog or listen for
         *                  when the dialog is closed.
         */
        $scope.open = function (contentsInfo) {
            var ngDialogInfo;
            if (typeof(contentsInfo) == 'object') { // is info to pass directly to ngDialog
                ngDialogInfo = contentsInfo;
            } else if (contentsInfo.substr(0, 1) == '#') { // is ID of an element
                ngDialogInfo = {template: contentsInfo.substr(1)};
            } else if (contentsInfo.substr(-4) == '.html') { // is a link to an .html file
                ngDialogInfo = {template: contentsInfo};
            } else { // is a raw HTML string
                ngDialogInfo = {template: contentsInfo, plain: true};
            }

            return ngDialog.open(ngDialogInfo);
        };

        /**
         * Opens a persisted dialog. Persisted dialogs are dialogs that will be launched on reload
         * of the current URL. They are accomplished by modifying the URL and adding a 'popover'
         * query parameter.
         *
         * @param {string} directive The denormalized name of an angularjs directive. An element with
         *                           this directive will be the contents of the dialog.
         * @param {object} attributes Key value mapping of the HTML attributes to add to the dialog's
         *                            contents element.
         */
        $scope.persist = function (directive, attributes) {
            piwikDialogtogglerUrllistener.propagatePersistedDialog(directive, attributes);
        };

        /**
         * Closes the currently open dialog window.
         */
        $scope.close = function () {
            ngDialog.close();
        };
    }
})();