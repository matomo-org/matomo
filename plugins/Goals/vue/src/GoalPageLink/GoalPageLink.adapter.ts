/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAttributes, IDirective, IScope } from 'angular';
import GoalPageLink from './GoalPageLink';

export default function piwikGoalPageLink(): IDirective {
  return {
    restrict: 'A',
    link: function piwikGoalPageLinkLink(scope: IScope, element: JQuery, attrs: IAttributes) {
      const binding = {
        instance: null,
        value: {
          idGoal: attrs.piwikGoalPageLink,
        },
        oldValue: null,
        modifiers: {},
        dir: {},
      };

      GoalPageLink.mounted(element[0], binding);
    },
  };
}

window.angular.module('piwikApp').directive('piwikGoalPageLink', piwikGoalPageLink);
