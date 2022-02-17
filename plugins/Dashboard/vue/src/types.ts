/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Widget } from 'CoreHome';

interface WidgetRef {
  module: string;
  action: string;
}

export interface Dashboard {
  id: string|number;
  name: string;
  widgets: WidgetRef[];
}

interface DashboardLayoutConfig {
  layout: string;
}

export interface DashboardLayout {
  columns: Widget[][];
  config: DashboardLayoutConfig;
}
