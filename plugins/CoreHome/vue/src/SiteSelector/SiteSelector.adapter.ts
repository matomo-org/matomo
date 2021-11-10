/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import createAngularJsAdapter from '../createAngularJsAdapter';
import SiteSelector from './Siteselector.vue';
import {INgModelController, ITimeoutService} from 'angular';
import Matomo from '../Matomo/Matomo';

// TODO: ngModel tests
export default createAngularJsAdapter<[ITimeoutService]>({
  component: SiteSelector,
  scope: {
    showSelectedSite: {
      angularJsBind: '=',
    },
    showAllSitesItem: {
      angularJsBind: '=',
    },
    switchSiteOnSelect: {
      angularJsBind: '=',
    },
    onlySitesWithAdminAccess: {
      angularJsBind: '=',
    },
    name: {
      angularJsBind: '@',
    },
    allSitesText: {
      angularJsBind: '@',
    },
    allSitesLocation: {
      angularJsBind: '@',
    },
    placeholder: {
      angularJsBind: '@',
    },
  },
  $inject: ['$timeout'],
  directiveName: 'piwikSiteselector',
  events: {
    'update:modelValue': (newValue, vm, scope, element, attrs, ngModel) => {
      if (newValue.id != vm.selectedSite.id) {
        element.attr('siteid', newValue.id);
        element.trigger('change', vm.selectedSite);

        if (ngModel) {
          ngModel.$setViewValue(newValue);
        }
      }
    },
  },
  postCreate(vm, scope, element, attrs, controller, $timeout: ITimeoutService) {
    const ngModel = controller as INgModelController;

    // setup ng-model mapping
    if (ngModel) {
      ngModel.$setViewValue(vm.selectedSite);

      ngModel.$render = () => {
        if (angular.isString(ngModel.$viewValue)) {
          vm.selectedSite = JSON.parse(ngModel.$viewValue);
        } else {
          vm.selectedSite = ngModel.$viewValue;
        }
      };
    }

    $timeout(function () {
      if (attrs.siteid && attrs.sitename) {
        vm.selectedSite = {id: attrs.siteid, name: Matomo.helper.htmlDecode(attrs.sitename)};
      }
    });
  },
});
