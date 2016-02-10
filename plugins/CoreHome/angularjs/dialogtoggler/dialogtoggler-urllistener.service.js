/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * AngularJS service that handles the popover query parameter for Piwik's angular code.
 *
 * If the popover parameter's first part is the name of an existing AngularJS directive,
 * a dialog is created using ngDialog with the contents being an element with that directive.
 * The other parts of the parameter are treated as attributes for the element, eg,
 * `"mydirective:myparam=val:myotherparam=val2"`.
 *
 * It should not be necessary to use this service directly, instead the piwik-dialogtoggler
 * directive should be used.
 *
 * TODO: popover as a query parameter refers less to dialogs and more to any popup window
 *       (ie, not necessarily modal). should replace it w/ 'dialog' or maybe 'modal'.
 */
(function () {
    angular.module('piwikApp').factory('piwikDialogtogglerUrllistener', piwikDialogtogglerUrllistener);

    piwikDialogtogglerUrllistener.$inject = ['$rootScope', '$location', '$injector', '$rootElement', 'ngDialog'];

    function piwikDialogtogglerUrllistener($rootScope, $location, $injector, $rootElement, ngDialog) {
        var service = {},
            dialogQueryParamName = 'popover';

        function getHtmlFromDialogQueryParam(paramValue) {
            var info = paramValue.split(':'),
                directiveName = info.shift(),
                dialogContent = '';

            dialogContent += '<div ' + directiveName;
            angular.forEach(info, function (argumentAssignment) {
                var pair = argumentAssignment.split('='),
                    key = pair[0],
                    value = pair[1];
                dialogContent += ' ' + key + '="' + decodeURIComponent(value) + '"';
            });
            dialogContent += '/>';

            return dialogContent;
        }

        function directiveExists(directiveAttributeString) {
            // NOTE: directiveNormalize is not exposed by angularjs and the devs don't seem to want to expose it:
            //       https://github.com/angular/angular.js/issues/7955
            //       so logic is duplicated here.
            var PREFIX_REGEXP = /^(x[\:\-_]|data[\:\-_])/i,
                directiveName = angular.element.camelCase(directiveAttributeString.replace(PREFIX_REGEXP, ''));

            return $injector.has(directiveName + 'Directive');
        }

        service.checkUrlForDialog = function () {
            var dialogParamValue = $location.search()[dialogQueryParamName];
            if (dialogParamValue && directiveExists(dialogParamValue)) {
                var dialog = ngDialog.open({
                    template: getHtmlFromDialogQueryParam(dialogParamValue),
                    plain: true,
                    className: ''
                });

                dialog.closePromise.then(function () {
                    $location.search(dialogQueryParamName, null);
                });
            }
        };

        service.propagatePersistedDialog = function (directive, attributes) {
            var paramValue = directive;
            angular.forEach(attributes, function (value, name) {
                paramValue += ':' + name + '=' + encodeURIComponent(value);
            });

            $location.search(dialogQueryParamName, paramValue);
        };

        $rootScope.$on('$locationChangeSuccess', function () {
            service.checkUrlForDialog();
        });

        service.checkUrlForDialog(); // check on initial page load

        return service;
    }
})();