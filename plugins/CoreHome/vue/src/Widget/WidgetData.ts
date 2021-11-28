import { Subcategory } from '../ReportingMenu/ReportingMenu.store';

export default interface WidgetData {
  viewDataTable: string;
  parameters: Record<string, unknown>;
  subcategory: Subcategory,
}
