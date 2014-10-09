/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * AngularJS directive that manages an AJAX form.
 *
 * This directive will detect inputs & selects defined within an element and when a
 * submit button is clicked, will post data from the inputs & selects to a Piwik API method.
 *
 * When the POST request is finished the result will, by default, be displayed as a
 * notification.
 *
 * This directive accepts the following attributes:
 *
 * - **save-api-method**: **required** The Piwik API method that handles the POST request.
 * - **send-json-payload**: Whether to send the data as a form encoded URL or to send it as JSON.
 *                          If sending as JSON, the payload will still be a form encoded value,
 *                          but will contain a JSON object like `{data: {...form data...}}`.
 *
 *                          This is for forms with lots of fields where having the same number
 *                          of parameters in an API method would not be desired.
 * - **no-error-notification**: If true, does not display an error notification if the AJAX post
 *                              fails.
 * - **no-success-notification**: If true, does not display an error notification if the AJAX
 *                                results in success.
 *
 * **Custom Success/Error Handling**
 *
 * On success/failure, the response will be stored in controller scope. Child elements of a
 * piwik-ajax-form element can access this data, and thus, can customize what happens when
 * a form submit succeeds/fails.
 *
 * See the ajax-form.controller.js file for more info.
 *
 * Usage:
 *
 *     <div piwik-ajax-form
 *          save-api-method="'MyPlugin.myFormSaveMethod'"
 *          send-json-payload="true"
 *          ng-model="myFormData">
 *
 *          <h2>My Form</h2>
 *          <input name="myOption" value="myDefaultValue" type="text" />
 *          <input name="myOtherOption" type="checkbox" checked="checked" />
 *          <input type="submit" value="Submit" ng-disabled="ajaxForm.isSubmitting" />
 *
 *          <div piwik-notification context='error' ng-show="errorPostResponse">ERROR!</div>
 *     </div>
 */
(function () {
    angular.module('piwikApp').directive('piwikAjaxForm', piwikAjaxForm);

    piwikAjaxForm.$inject = ['$parse'];

    function piwikAjaxForm($parse) {
        return {
            restrict: 'A',
            scope: {
                submitApiMethod: '=',
                sendJsonPayload: '=',
                noErrorNotification: '=',
                noSuccessNotification: '=',
                useCustomDataBinding: '='
            },
            require: '?ngModel',
            controller: 'AjaxFormController',
            controllerAs: 'ajaxForm',
            transclude: true,
            compile: function (element, attrs) {
                attrs.noErrorNotification = !! attrs.noErrorNotification;

                return function (scope, element, attrs, ngModel, transclude) {
                    if (!scope.submitApiMethod) {
                        throw new Error("submitApiMethod is required");
                    }

                    scope.ajaxForm.submitApiMethod = scope.submitApiMethod;
                    scope.ajaxForm.sendJsonPayload = scope.sendJsonPayload;
                    scope.ajaxForm.noErrorNotification = scope.noErrorNotification;
                    scope.ajaxForm.noSuccessNotification = scope.noSuccessNotification;

                    scope.ajaxForm.data = {};

                    // if a model is supplied, initiate form data w/ model value
                    if (ngModel) {
                        var ngModelGetter = $parse(attrs.ngModel); // probably redundant, but I cannot find another way to
                                                                   // get the ng model value here
                        scope.ajaxForm.data = ngModelGetter(scope.$parent);
                    }

                    // on change of any input, change appropriate value in model, but only if requested
                    if (!scope.useCustomDataBinding) {
                        element.on('change', 'input,select', function () {
                            setFormValueFromInput(this);
                        });
                    }

                    // on submit call controller submit method
                    element.on('click', 'input[type=submit]', function () {
                        scope.ajaxForm.submitForm();
                    });

                    // make sure child elements can access this directive's scope
                    transclude(scope, function(clone, scope) {
                        if (!scope.useCustomDataBinding) {
                            var $inputs = clone.find('input,select').not('[type=submit]');

                            // initialize form data to input values (include <select>s
                            $inputs.each(function () {
                                setFormValueFromInput(this, true);
                            });
                        }

                        element.append(clone);
                    });

                    function setFormValueFromInput(inputElement, skipScopeApply) {
                        var $ = angular.element,
                            name = $(inputElement).attr('name'),
                            val;

                        if ($(inputElement).attr('type') == 'checkbox') {
                            val = $(inputElement).is(':checked');
                        } else {
                            val = $(inputElement).val();
                        }

                        scope.ajaxForm.data[name] = val;

                        if (!skipScopeApply) {
                            scope.$apply();
                        }
                    }
                };
            }
        };
    }
})();