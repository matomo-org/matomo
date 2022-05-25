/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAttributes, IDirective, IScope } from 'angular';
import ShowSensitiveData from './ShowSensitiveData';

export default function piwikShowSensitiveData(): IDirective {
  return {
    restrict: 'A',
    link: function piwikShowSensitiveDataLink(scope: IScope, element: JQuery, attr: IAttributes) {
      const binding = {
        instance: null,
        value: {
          sensitiveData: attr.piwikShowSensitiveData || (attr.text ? attr.text() : ''),
          showCharacters: attr.showCharacters ? parseInt(attr.showCharacters, 10) : undefined,
          clickElementSelector: attr.clickElementSelector as string,
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      ShowSensitiveData.mounted(element[0], binding);
    },
  };
}

window.angular.module('piwikApp').directive('piwikShowSensitiveData', piwikShowSensitiveData);
