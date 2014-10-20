# Piwik Platform Changelog

This is a changelog for Piwik platform developers. All changes for our HTTP API's, Plugins, Themes, etc will be listed here.

## Piwik 2.8.1

### Library updates
* Updated AngularJS from 1.2.25 to 1.2.26
* Updated jQuery from 1.11.0 to 1.11.1

## Piwik 2.8.0

### Breaking Changes
* The Auth interface has been modified, existing Auth implementations will have to be modified. Changes include:
  * The initSession method has been moved. Since this behavior must be executed for every Auth implementation, it has been put into a new class: SessionInitializer.
    If your Auth implementation implements its own session logic you will have to extend and override SessionInitializer.
  * The following methods have been added: setPassword, setPasswordHash, getTokenAuthSecret and getLogin.
  * Clarifying semantics of each method and what they must support and can support.
  * **Read the documentation for the [Auth interface](http://developer.piwik.org/api-reference/Piwik/Auth) to learn more.**
* The `Piwik\Unzip\*` classes have been extracted out of the Piwik repository into a separate component named [Decompress](https://github.com/piwik/component-decompress).
  * `Piwik\Unzip` has not moved, it is kept for backward compatibility. If you have been using that class, you don't need to change anything.
  * The `Piwik\Unzip\*` classes (Tar, PclZip, Gzip, ZipArchive) have moved to the `Piwik\Decompress\*` namespace (inside the new repository).
  * `Piwik\Unzip\UncompressInterface` has been moved and renamed to `Piwik\Decompress\DecompressInterface` (inside the new repository).

### Deprecations
* The `Piwik::setUserHasSuperUserAccess` method is deprecated, instead use Access::doAsSuperUser. This method will ensure that super user access is properly rescinded after the callback finishes.
* The class is `\IntegrationTestCase` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\TestCase\SystemTestCase` instead.
* The class is `\DatabaseTestCase` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\TestCase\IntegrationTestCase` instead.
* The class is `\BenchmarkTestCase` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\TestCase\BenchmarkTestCase` instead.
* The class is `\ConsoleCommandTestCase` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase` instead.
* The class is `\FakeAccess` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\Mock\FakeAccess` instead.
* The class is `\Piwik\Tests\Fixture` deprecated and will be removed from February 6th 2015. Use `\Piwik\Tests\Framework\Fixture` instead.
* The class is `\Piwik\Tests\OverrideLogin` deprecated and will be removed from February 6ths 2015. Use `\Piwik\Framework\Framework\OverrideLogin` instead.

### New API Features
* The pivotBy and related query parameters can be used to pivot reports by another dimension. Read more about the new query parameters [here](http://developer.piwik.org/api-reference/reporting-api#optional-api-parameters).

### Library updates
* Updated AngularJS from 1.2.13 to 1.2.25

### New commands
* `generate:angular-directive` Let's you easily generate a template for a new angular directive for any plugin.

### Internal change
* Piwik 2.8.0 now requires PHP >= 5.3.3. 
 * If you use an older PHP version, please upgrade now to the latest PHP so you can enjoy improvements and security fixes in Piwik. 

## Piwik 2.7.0

### Reporting APIs
* Several APIs will now expose a new metric `nb_users` which measures the number of unique users when a [User ID](http://piwik.org/docs/user-id/) is set.
* New APIs have been added for [Content Tracking](http://piwik.org/docs/content-tracking/) feature: Contents.getContentNames, Contents.getContentPieces

### Deprecations
* The `Piwik\Menu\MenuAbstract::add()` method is deprecated in favor of `addItem()`. Read more about this here: [#6140](https://github.com/piwik/piwik/issues/6140). We do not plan to remove the deprecated method before Piwik 3.0.

### New APIs
* It is now easier to generate the URL for a menu item see [#6140](https://github.com/piwik/piwik/issues/6140), [urlForDefaultAction()](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu#urlfordefaultaction), [urlForAction()](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu#urlforaction), [urlForModuleAction()](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu#urlformoduleaction)

### New commands
* `core:clear-caches` Lets you easily delete all caches. This command can be useful for instance after updating Piwik files manually.


## Piwik 2.6.0

### Deprecations
* The `'json'` API format is considered deprecated. We ask all new code to use the `'json2'` format. Eventually when Piwik 3.0 is released the `'json'` format will be replaced with `'json2'`. Differences in the json2 format include:
  * A bug in JSON formatting was fixed so API methods that return simple associative arrays like `array('name' => 'value', 'name2' => 'value2')` will now appear correctly as `{"name":"value","name2":"value2"}` in JSON API output instead of `[{"name":"value","name2":"value2"}]`. API methods like **SitesManager.getSiteFromId** & **UsersManager.getUser** are affected.

#### Reporting API
* If an API returns an indexed array, it is now possible to use `filter_limit` and `filter_offset`. This was before only possible if an API returned a DataTable.
* The Live API now returns only visitor information of activated plugins. So if for instance the Referrers plugin is deactivated a visitor won't contain any referrers related properties. This is a bugfix as the API was crashing before if some core plugins were deactivated. Affected methods are for instance `getLastVisitDetails` or `getVisitorProfile`. If all core plugins are enabled as by default there will be no change at all except the order of the properties within one visitor.

### New commands
* `core:run-scheduled-tasks` Let's you run all scheduled tasks due to run at this time. Useful for instance when testing tasks.

#### Internal change
 * We removed our own autoloader that was used to load Piwik files in favor of the composer autoloader which we already have been using for some libraries. This means the file `core/Loader.php` will no longer exist. In case you are using Piwik from Git make sure to run `php composer.phar self-update && php composer.phar install` to make your Piwik work again. Also make sure to no longer include `core/Loader.php` in case it is used in any custom script.
 * We do no longer store the list of plugins that are used during tracking in the config file. They are dynamically detect instead. The detection of a tracker plugin works the same as before. A plugin has to either listen to any `Tracker.*` or `Request.initAuthenticationObject` event or it has to define dimensions in order to be detected as a tracker plugin.

## Piwik 2.5.0

### Breaking Changes
* Javascript Tracking API: if you are using `getCustomVariable` function to access custom variables values that were set on previous page views, you now must also call `storeCustomVariablesInCookie` before the first call to `trackPageView`. Read more about [Javascript Tracking here](http://developer.piwik.org/api-reference/tracking-javascript).
* The [settings](http://developer.piwik.org/guides/piwik-configuration) API will receive the actual entered value and will no longer convert characters like `&` to `&amp;`. If you still want this behavior - for instance to prevent XSS - you can define a filter by setting the `transform` property like this:
  `$setting->transform = function ($value) { return Common::sanitizeInputValue($value); }`
* Config setting `disable_merged_assets` moved from `Debug` section to `Development`. The updater will automatically change the section for you.
* `API.getRowEvolution` will throw an exception if a report is requested that does not have a dimension, for instance `VisitsSummary.get`. This is a fix as an invalid format was returned before see [#5951](https://github.com/piwik/piwik/issues/5951)
* `MultiSites.getAll` returns from now on always an array of websites. In the past it returned a single object and it didn't contain all properties in case only one website was found which was a bug see [#5987](https://github.com/piwik/piwik/issues/5987)

### Deprecations
The following events are considered as deprecated and the new structure should be used in the future. We have not scheduled when those events will be removed but probably in Piwik 3.0 which is not scheduled yet and won't be soon. New features will be added only to the new classes.

* `API.getReportMetadata`, `API.getSegmentDimensionMetadata`, `Goals.getReportsWithGoalMetrics`, `ViewDataTable.configure`, `ViewDataTable.getDefaultType`: use [Report](http://developer.piwik.org/api-reference/Piwik/Plugin/Report) class instead to define new reports. There is an updated guide as well [Part1](http://developer.piwik.org/guides/getting-started-part-1)
* `WidgetsList.addWidgets`: use [Widgets](http://developer.piwik.org/api-reference/Piwik/Plugin/Widgets) class instead to define new widgets
* `Menu.Admin.addItems`, `Menu.Reporting.addItems`, `Menu.Top.addItems`: use [Menu](http://developer.piwik.org/api-reference/Piwik/Plugin/Menu) class instead
* `TaskScheduler.getScheduledTasks`: use [Tasks](http://developer.piwik.org/api-reference/Piwik/Plugin/Tasks) class instead to define new tasks
* `Tracker.recordEcommerceGoal`, `Tracker.recordStandardGoals`, `Tracker.newConversionInformation`: use [Conversion Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ConversionDimension) class instead
* `Tracker.existingVisitInformation`, `Tracker.newVisitorInformation`, `Tracker.getVisitFieldsToPersist`: use [Visit Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/VisitDimension) class instead
* `ViewDataTable.addViewDataTable`: This event is no longer needed. Visualizations are automatically discovered if they are placed within a `Visualizations` directory inside the plugin.

### New features

#### Translation search
As a plugin developer you might want to reuse existing translation keys. You can now find all available translations and translation keys by opening the page "Settings => Development:Translation search" in your Piwik installation. Read more about [internationalization](http://developer.piwik.org/guides/internationalization) here.

#### Reporting API
It is now possible to use the `filter_sort_column` parameter when requesting `Live.getLastVisitDetails`. For instance `&filter_sort_column=visitCount`. 

#### @since annotation
We are using `@since` annotations in case we are introducing new API's to make it easy to see in which Piwik version a new method was added. This information is now displayed in the [Classes API-Reference](http://developer.piwik.org/api-reference/classes). 

### New APIs
* [Report](http://developer.piwik.org/api-reference/Piwik/Plugin/Report) to add a new report
* [Action Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ActionDimension) to add a dimension that tracks action related information
* [Visit Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/VisitDimension) to add a dimension that tracks visit related information
* [Conversion Dimension](http://developer.piwik.org/api-reference/Piwik/Plugin/Dimension/ConversionDimension) to add a dimension that tracks conversion related information
* [Dimension](http://developer.piwik.org/api-reference/Piwik/Columns/Dimension) to add a basic non tracking dimension that can be used in `Reports`
* [Widgets](http://developer.piwik.org/api-reference/Piwik/Plugin/Widgets) to add or modfiy widgets
* These Menu classes got new methods that make it easier to add new items to a specific section
  * [MenuAdmin](http://developer.piwik.org/api-reference/Piwik/Menu/MenuAdmin) to add or modify admin menu items. 
  * [MenuReporting](http://developer.piwik.org/api-reference/Piwik/Menu/MenuReporting) to add or modify reporting menu items
  * [MenuUser](http://developer.piwik.org/api-reference/Piwik/Menu/MenuUser) to add or modify user menu items
* [Tasks](http://developer.piwik.org/api-reference/Piwik/Plugin/Tasks) to add scheduled tasks

### New commands
* `generate:theme` Let's you easily generate a new theme and customize colors, see the [Theming guide](http://developer.piwik.org/guides/theming)
* `generate:update` Let's you generate an update file
* `generate:report` Let's you generate a report
* `generate:dimension` Let's you enhance the tracking by adding new dimensions
* `generate:menu` Let's you generate a menu class to add or modify menu items
* `generate:widgets` Let's you generate a widgets class to add or modify widgets
* `generate:tasks` Let's you generate a tasks class to add or modify tasks
* `development:enable` Let's you enable the development mode which will will disable some caching to make code changes directly visible and it will assist developers by performing additional checks to prevent for instance typos. Should not be used in production.
* `development:disable` Let's you disable the development mode 

<!--
## Template: Piwik version number

### Breaking Changes
### Deprecations
### New features
### New APIs
### New commands
### New guides
### Library updates
### Internal change
 -->
