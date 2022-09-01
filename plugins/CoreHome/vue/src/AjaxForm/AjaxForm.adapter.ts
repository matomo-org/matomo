/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DirectiveBinding, ref } from 'vue';
import { IDirective, IDirectiveLinkFn, IParseService } from 'angular';
import createVueApp from '../createVueApp';
import AjaxForm from './AjaxForm.vue';

const { $ } = window;

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
 * - **submit-api-method**: **required** The Piwik API method that handles the POST request.
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
 *          submit-api-method="'MyPlugin.myFormSaveMethod'"
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
 * @deprecated
 */

function piwikAjaxForm($parse: IParseService): IDirective {
  return {
    restrict: 'A',
    scope: {
      submitApiMethod: '=',
      sendJsonPayload: '=',
      noErrorNotification: '=',
      noSuccessNotification: '=',
      useCustomDataBinding: '=',
    },
    require: '?ngModel',
    transclude: true,
    compile: function piwikAjaxFormCompile(compileElement, compileAttrs): IDirectiveLinkFn {
      compileAttrs.noErrorNotification = !!compileAttrs.noErrorNotification;

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      return function piwikAjaxFormLink(scope: any, element, attrs, ngModel, transclude) {
        if (!scope.submitApiMethod) {
          throw new Error('submitApiMethod is required');
        }

        scope.ajaxForm = {};
        scope.ajaxForm.submitApiMethod = scope.submitApiMethod;
        scope.ajaxForm.sendJsonPayload = scope.sendJsonPayload;
        scope.ajaxForm.noErrorNotification = scope.noErrorNotification;
        scope.ajaxForm.noSuccessNotification = scope.noSuccessNotification;

        scope.ajaxForm.data = {};

        // if a model is supplied, initiate form data w/ model value
        if (ngModel) {
          // probably redundant, but I cannot find another way to get the ng model value here
          const ngModelGetter = $parse(attrs.ngModel);
          scope.ajaxForm.data = ngModelGetter(scope.$parent);
        }

        interface SpecialDirectiveBinding {
          submitForm: () => void;
        }

        const specialBindDirective = {
          mounted(el: HTMLElement, binding: DirectiveBinding<SpecialDirectiveBinding>) {
            scope.ajaxForm.submitForm = binding.value.submitForm;
          },
        };

        const rootTemplate = `
        <AjaxForm
          :form-data="data"
          :submit-api-method="submitApiMethod"
          :send-json-payload="sendJsonPayload"
          :no-error-notification="noErrorNotification"
          :no-success-notification="noSuccessNotification"
        >
          <template v-slot:default="ajaxFormVue">
            <div
              ref="transcludeTarget"
              v-special-bind-directive="{ submitForm: ajaxFormVue.submitForm }"
            />
          </template>
        </AjaxForm>`;

        const app = createVueApp({
          template: rootTemplate,
          data() {
            return scope.ajaxForm;
          },
          setup() {
            const transcludeTarget = ref(null);
            return {
              transcludeTarget,
            };
          },
        });
        app.component('AjaxForm', AjaxForm);
        app.directive('SpecialBindDirective', specialBindDirective);
        const vm = app.mount(element[0]);

        element.on('$destroy', () => {
          app.unmount();
        });

        function setFormValueFromInput(inputElement: HTMLElement, skipScopeApply?: boolean) {
          const name = $(inputElement).attr('name')!;
          let val;

          if ($(inputElement).attr('type') === 'checkbox') {
            val = $(inputElement).is(':checked');
          } else {
            val = $(inputElement).val();
          }

          scope.ajaxForm.data[name] = val;

          if (!skipScopeApply) {
            setTimeout(() => {
              scope.$apply();
            }, 0);
          }
        }

        // on change of any input, change appropriate value in model, but only if requested
        if (!scope.useCustomDataBinding) {
          element.on('change', 'input,select', (event) => {
            setFormValueFromInput(event.target as HTMLElement);
          });
        }

        // make sure child elements can access this directive's scope
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        transclude!(scope, (clone, transcludeScope: any) => {
          if (!transcludeScope.useCustomDataBinding) {
            const $inputs = clone!.find('input,select').not('[type=submit]');

            // initialize form data to input values (include <select>s
            $inputs.each(function inputEach() {
              setFormValueFromInput(this, true);
            });
          }

          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          $((vm as any).transcludeTarget as HTMLElement).append(clone!);
        });
      };
    },
  };
}

piwikAjaxForm.$inject = ['$parse'];

window.angular.module('piwikApp').directive('piwikAjaxForm', piwikAjaxForm);
