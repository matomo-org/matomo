/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import './noAdblockFlag';
import './MatomoUrl/MatomoUrl.adapter';
import './Matomo/Matomo.adapter';
import './Periods/Day';
import './Periods/Week';
import './Periods/Month';
import './Periods/Year';
import './Periods/Range';
import './Periods/Periods.adapter';
import './AjaxHelper/AjaxHelper.adapter';
import './PopoverHandler/PopoverHandler';
import './Alert/Alert.adapter';
import './DropdownMenu/DropdownMenu.adapter';
import './FocusAnywhereButHere/FocusAnywhereButHere.adapter';
import './FocusIf/FocusIf.adapter';
import './ExpandOnClick/ExpandOnClick.adapter';
import './ExpandOnHover/ExpandOnHover.adapter';
import './ShowSensitiveData/ShowSensitiveData.adapter';
import './DropdownButton/DropdownButton.adapter';
import './SelectOnFocus/SelectOnFocus.adapter';
import './CopyToClipboard/CopyToClipboard.adapter';
import './SideNav/SideNav.adapter';
import './MatomoDialog/MatomoDialog.adapter';
import './EnrichedHeadline/EnrichedHeadline.adapter';
import './ContentBlock/ContentBlock.adapter';
import './Comparisons/Comparisons.adapter';
import './MenuItemsDropdown/MenuItemsDropdown.adapter';
import './DatePicker/DatePicker.adapter';
import './DateRangePicker/DateRangePicker.adapter';
import './PeriodDatePicker/PeriodDatePicker.adapter';
import './SiteSelector/SiteSelector.adapter';
import './SiteSelector/SitesStore.adapter';
import './QuickAccess/QuickAccess.adapter';
import './FieldArray/FieldArray.adapter';
import './MultiPairField/MultiPairField.adapter';
import './PeriodSelector/PeriodSelector.adapter';
import './ReportingMenu/ReportingMenu.adapter';
import './ReportingMenu/ReportingMenu.store.adapter';
import './ReportingPages/ReportingPages.store.adapter';
import './ReportMetadata/ReportMetadata.store.adapter';
import './WidgetLoader/WidgetLoader.adapter';
import './WidgetContainer/WidgetContainer.adapter';
import './WidgetByDimensionContainer/WidgetByDimensionContainer.adapter';
import './Widget/Widget.adapter';
import './ReportingPage/ReportingPage.adapter';
import './ReportExport/ReportExport.adapter';
import './Sparkline/Sparkline.adapter';
import './Progressbar/Progressbar.adapter';
import './ContentIntro/ContentIntro.adapter';
import './ContentTable/ContentTable.adapter';
import './AjaxForm/AjaxForm.adapter';
import './ShowHelpLink/ShowHelpLink.adapter';

export { default as createVueApp } from './createVueApp';
export { default as useExternalPluginComponent } from './useExternalPluginComponent';
export { default as DirectiveUtilities } from './directiveUtilities';
export { default as debounce } from './debounce';
export { default as getFormattedEvolution } from './getFormattedEvolution';
export {
  default as createAngularJsAdapter,
  transformAngularJsBoolAttr,
  transformAngularJsIntAttr,
  removeAngularJsSpecificProperties,
  clone,
  cloneThenApply,
} from './createAngularJsAdapter';
export { default as activityIndicatorAdapter } from './ActivityIndicator/ActivityIndicator.adapter';
export { default as ActivityIndicator } from './ActivityIndicator/ActivityIndicator.vue';
export * from './translate';
export { default as Alert } from './Alert/Alert.vue';
export { default as AjaxHelper, AjaxOptions } from './AjaxHelper/AjaxHelper';
export { setCookie, getCookie, deleteCookie } from './CookieHelper/CookieHelper';
export { default as MatomoUrl } from './MatomoUrl/MatomoUrl';
export { default as Matomo } from './Matomo/Matomo';
export * from './Periods';
export { default as DropdownMenu } from './DropdownMenu/DropdownMenu';
export { default as FocusAnywhereButHere } from './FocusAnywhereButHere/FocusAnywhereButHere';
export { default as FocusIf } from './FocusIf/FocusIf';
export { default as Tooltips } from './Tooltips/Tooltips';
export { default as MatomoDialog } from './MatomoDialog/MatomoDialog.vue';
export { default as ExpandOnClick } from './ExpandOnClick/ExpandOnClick';
export { default as ExpandOnHover } from './ExpandOnHover/ExpandOnHover';
export { default as ShowSensitiveData } from './ShowSensitiveData/ShowSensitiveData';
export { default as DropdownButton } from './DropdownButton/DropdownButton';
export { default as SelectOnFocus } from './SelectOnFocus/SelectOnFocus';
export { default as CopyToClipboard } from './CopyToClipboard/CopyToClipboard';
export { default as SideNav } from './SideNav/SideNav';
export { default as EnrichedHeadline } from './EnrichedHeadline/EnrichedHeadline.vue';
export { default as ContentBlock } from './ContentBlock/ContentBlock.vue';
export { default as Comparisons } from './Comparisons/Comparisons.vue';
export { default as MenuItemsDropdown } from './MenuItemsDropdown/MenuItemsDropdown.vue';
export { default as DatePicker } from './DatePicker/DatePicker.vue';
export { default as DateRangePicker } from './DateRangePicker/DateRangePicker.vue';
export { default as PeriodDatePicker } from './PeriodDatePicker/PeriodDatePicker.vue';
export * from './Notification';
export { default as SitesStore } from './SiteSelector/SitesStore';
export { default as Site } from './SiteSelector/Site';
export { default as SiteSelector } from './SiteSelector/SiteSelector.vue';
export { default as SiteRef } from './SiteSelector/SiteRef';
export { default as QuickAccess } from './QuickAccess/QuickAccess.vue';
export { default as FieldArray } from './FieldArray/FieldArray.vue';
export { default as MultiPairField } from './MultiPairField/MultiPairField.vue';
export { default as PeriodSelector } from './PeriodSelector/PeriodSelector.vue';
export { default as ReportingMenu } from './ReportingMenu/ReportingMenu.vue';
export { default as ReportingMenuStore } from './ReportingMenu/ReportingMenu.store';
export { default as ReportingPagesStore } from './ReportingPages/ReportingPages.store';
export { default as ReportMetadataStore } from './ReportMetadata/ReportMetadata.store';
export { default as WidgetsStore } from './Widget/Widgets.store';
export { default as WidgetLoader } from './WidgetLoader/WidgetLoader.vue';
export { default as WidgetContainer } from './WidgetContainer/WidgetContainer.vue';
export { default as WidgetByDimensionContainer } from './WidgetByDimensionContainer/WidgetByDimensionContainer.vue';
export { default as Widget } from './Widget/Widget.vue';
export {
  Widget as WidgetType,
  WidgetContainer as WidgetContainerType,
  GroupedWidgets as GroupedWidgetsType,
} from './Widget/types';
export { default as ReportingPage } from './ReportingPage/ReportingPage.vue';
export { default as ReportExport } from './ReportExport/ReportExport';
export { default as Sparkline } from './Sparkline/Sparkline.vue';
export { default as Progressbar } from './Progressbar/Progressbar.vue';
export { default as ContentIntro } from './ContentIntro/ContentIntro';
export { default as ContentTable } from './ContentTable/ContentTable';
export { default as AjaxForm } from './AjaxForm/AjaxForm.vue';
