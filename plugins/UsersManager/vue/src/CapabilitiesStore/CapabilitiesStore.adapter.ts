/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { DeepReadonly } from 'vue';
import Capability from './Capability';
import CapabilitiesStore from './CapabilitiesStore';

function permissionsMetadataServiceAdapter() {
  return {
    getAllCapabilities(): Promise<DeepReadonly<Capability[]>> {
      return CapabilitiesStore.fetchCapabilities();
    },
  };
}

window.angular.module('piwikApp.service').factory(
  'permissionsMetadataService',
  permissionsMetadataServiceAdapter,
);
