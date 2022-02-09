/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import {
  SettingsForSinglePlugin,
} from 'CorePluginsAdmin';

interface SiteType {
  id: string;
  name: string;
  howToSetupUrl?: string;
  settings?: SettingsForSinglePlugin[];
}

export default SiteType;
