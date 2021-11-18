/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { createAngularJsAdapter } from 'CoreHome';
import FormField from './FormField.vue';

export default createAngularJsAdapter({
  component: FormField,
  scope: {
    piwikFormField: {
      vue: 'formField',
      angularJsBind: '=',
      transform(value, vm, scope) {
        let transformed = value;
        if (value.condition) {
          transformed = {
            ...value,
            condition: (values: unknown[]) => scope.$eval(value.condition, values),
          };
        }
        return transformed;
        // TODO
      },
    },
    allSettings: {
      angularJsBind: '=',
    },
  },
  directiveName: 'piwikFormField',
});
