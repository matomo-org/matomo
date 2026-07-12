/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Orderable } from '../Orderable';
import { Category } from '../ReportingMenu/Category';
import { Subcategory } from '../ReportingMenu/Subcategory';

export interface ClientComponent {
  plugin: string;
  name: string;
  props?: Record<string, unknown>;
}

export interface Widget extends Orderable {
  uniqueId?: string;
  name?: string;
  module?: string;
  action?: string;
  viewDataTable?: string;
  parameters?: Record<string, unknown>;
  category?: Category;
  subcategory?: Subcategory;
  isContainer?: boolean;
  isReport?: boolean;
  middlewareParameters?: Record<string, unknown>;
  clientComponent?: ClientComponent;
  documentation?: string;
  layout?: string;
  isWide?: boolean;
  isFirstInPage?: boolean;

  // Discriminant against GroupedWidgets: a plain widget is never a group. Lets the reporting page
  // template narrow `Widget | GroupedWidgets` via `widget.group`.
  group?: false;
}

// get around DeepReadonly<> not being able to handle recursive types by moving the
// recursive properties to subtypes that are only referenced when needed
export interface WidgetContainer extends Widget {
  widgets?: Widget[];
}

export interface GroupedWidgets {
  group: true;
  // Grouped widgets have no id of their own; declared optional so the shared `Widget | GroupedWidgets`
  // list can be keyed by uniqueId in templates.
  uniqueId?: undefined;
  left?: Widget[];
  right?: Widget[];
}
