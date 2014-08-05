# Piwik Platform Changelog

This is a changelog for Piwik platform developers. All new API's, changes, new features for our HTTP API's, Plugins, Themes, etc will be listed here.

## Piwik 2.5.0

### Breaking Changes
* The [settings](http://developer.piwik.org/guides/piwik-configuration) API does now receive the actual entered value and will no longer convert characters like `&` to `&amp;`. If you still want this behavior - for instance to prevent XSS - you can define a filter by setting the `transform` property like this:
  `$setting->transform = function ($value) { return Common::sanitizeInputValue($value); }`
* Config setting `disable_merged_assets` moved from `Debug` section to `Development`. The updater will automatically change the section for you.

### Deprecations
* The following events are considered as deprecated and the new structure should be used in the future. We have not scheduled when those events will be removed, probably in Piwik 3.0 which is not scheduled yet and won't be soon.
 * "API.getReportMetadata", "API.getSegmentDimensionMetadata", "Goals.getReportsWithGoalMetrics"
   => use [Report](http://developer.piwik.org/api-reference/Piwik/Plugin/Report) class instead to define new reports. There is an updated guide as well [Part1](http://developer.piwik.org/guides/getting-started-part-1)
 * "WidgetsList.addWidgets"
   => use [Widgets](http://developer.piwik.org/api-reference/Piwik/Plugin/Widgets) class instead to define new widgets
 * "Menu.Admin.addItems", "Menu.Reporting.addItems", "Menu.Top.addItems" 
   => use [Menu](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu) class instead
 * "TaskScheduler.getScheduledTasks"
   => use [Tasks](http://developer.piwik.org/api-reference/Piwik/Plugin/Tasks) class instead to define new tasks
 * "Tracker.recordEcommerceGoal", "Tracker.recordStandardGoals", "Tracker.newConversionInformation"
   => use [Conversion Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ConversionDimension) class instead
 * "Tracker.existingVisitInformation", "Tracker.newVisitorInformation", "Tracker.getVisitFieldsToPersist"
   => use [Visit Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/VisitDimension) class instead

### New features
* Translation key search: As a plugin developer you might want to reuse existing translation keys. You can now find all available translations and translation keys by going to the "Settings => Development:Translation search".

### New APIs
* [Report](http://developer.piwik.org/api-reference/Piwik/Plugin/Report)
* [Action Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ActionDimension) to add a dimension that tracks action related information
* [Visit Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/VisitDimension) to add a dimension that tracks visit related information
* [Conversion Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ConversionDimension) to add a dimension that tracks conversion related information
* [Dimension](http://developer.piwik.org/api-reference/Piwik/Columns/Dimension) to add a basic non tracking dimension that can be used in `Reports`
* [Widgets](http://developer.piwik.org/api-reference/Piwik/Plugin/Widgets) to add or modfiy widgets
* [Menu](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu) to add or modify menu items
* [Tasks](http://developer.piwik.org/api-reference/Piwik/Plugin/Tasks) to add scheduled tasks

### New commmands
* `development:enable` Let's you enable the development mode which will will disable some caching to make code changes directly visible and it will assist developers by performing additional checks to prevent for instance typos. Should not be used in production.
* `development:disable` Let's you disable the development mode 
* `generate:update` Let's you generate an update file
* `generate:report` Let's you generate a report
* `generate:dimension` Let's you enhance the tracking by adding new dimensions
* `generate:menu` Let's you generate a menu class to add or modify menu items
* `generate:widgets` Let's you generate a widgets class to add or modify widgets
* `generate:tasks` Let's you generate a tasks class to add or modify tasks

<!--
## Temlate: Piwik version number

### Breaking Changes
### Deprecations
### New features
### New APIs
### New commmands
### New guides
 -->