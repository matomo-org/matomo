/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DeepReadonly } from 'vue';
import SiteTypesStore from './SiteTypesStore';
import SiteType from './SiteType';

function sitesManagerTypeModelAdapter() {
  return {
    get typesById() {
      return SiteTypesStore.typesById.value;
    },
    fetchTypeById(typeId: string): Promise<DeepReadonly<SiteType>> {
      return SiteTypesStore.fetchAvailableTypes().then((types) => types[typeId]);
    },
    fetchAvailableTypes(): ReturnType<typeof SiteTypesStore['fetchAvailableTypes']> {
      return SiteTypesStore.fetchAvailableTypes();
    },
    hasMultipleTypes(): Promise<boolean> {
      return SiteTypesStore.fetchAvailableTypes().then(
        (types) => types && Object.keys(types).length > 1,
      );
    },
    removeEditSiteIdParameterFromHash:
      SiteTypesStore.removeEditSiteIdParameterFromHash.bind(SiteTypesStore),
    getEditSiteIdParameter:
      SiteTypesStore.getEditSiteIdParameter.bind(SiteTypesStore),
  };
}

window.angular.module('piwikApp.service').factory(
  'sitesManagerTypeModel',
  sitesManagerTypeModelAdapter,
);
