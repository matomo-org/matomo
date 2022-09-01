/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';
import { Subcategory } from '../ReportingMenu/Subcategory';

export interface Widget extends Orderable {
  uniqueId?: string;
  module?: string;
  action?: string;
  viewDataTable?: string;
  parameters?: Record<string, unknown>;
  subcategory?: Subcategory;
  isContainer?: boolean;
  isReport?: boolean;
  middlewareParameters?: Record<string, unknown>;
  documentation?: string;
  layout?: string;
  isWide?: boolean;
  isFirstInPage?: boolean;
}

// get around DeepReadonly<> not being able to handle recursive types by moving the
// recursive properties to subtypes that are only referenced when needed
export interface WidgetContainer extends Widget {
  widgets?: Widget[];
}

export interface GroupedWidgets {
  group: boolean;
  left?: Widget[];
  right?: Widget[];
}
