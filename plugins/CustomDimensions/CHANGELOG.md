## Changelog

* 3.1.10
  * Performance & code improvements
  * Translation updates
* 3.1.9
  * Remove custom dimension configs for the correct scope 
  * Improve speed of auto suggest for custom dimension for high traffic sites
  * Compability with [Google Analytics Importer](https://matomo.org/docs/google-analytics-importer/)
* 3.1.8
  * Trim and truncate raw values from tracker
* 3.1.7
  * Add primary key for better replication
* 3.1.6
  * Fix custom dimensions in scheduled reports may be repeated
* 3.1.5
  * Fix a permission check
* 3.1.4
  * Fix flattened reports may define wrong segment selector
  * Internal changes
* 3.1.1
  * Adds support for [Custom Reports](https://plugins.piwik.org/CustomReports)
  * Better sorting for auto suggestion in segments
* 3.1.0
  * Makes plugin compatible with Piwik 3.1.0 (Adjustments to make custom dimensions visible in visitor log and profile)
* 3.0.2
  * Make sure to unsanitize extraction patterns so HTML entities can be used
* 3.0.1: 
  * Language updates
  * No longer show an empty entry as `Value not defined`
* 3.0.0: Compatibility with Piwik 3.0
* 0.1.5 
  * Fix some problems where a wrong whitespace might cause JavaScript errors and causes the UI to not work
  * Fix a typo in the UI in the JavaScript code which sets a custom dimension  
* 0.1.4 Fix a possible JavaScript error if Transitions plugin is disabled
* 0.1.3 Fix UI of Custom Dimensions was not working properly when not using English as language
* 0.1.2
  * New feature: Mark an extraction as case sensitive
  * New feature : Show actions that had no value defined
  * New feature : Link to Page URLs in subtables
* 0.1.1 Bugfixes
* 0.1.0 Initial release
