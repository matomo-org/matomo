/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import Setting from './Setting';

export default interface SettingsForSinglePlugin {
  pluginName: string;
  settings: Setting[];
}
