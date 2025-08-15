# Unified Settings Access

## Approach

Plugin provides a set of getters, one for each possible way where/how a setting value can be obtained.

Each getter attempts to get a setting based on plugin name and setting name.

When a setting is found, its value should be returned. If a setting can't be found or obtained, an exception should
be thrown. The access class then can continue to the next getter in the hierarchy list, until a value is found.

An event is posted with the plugin name, setting name and the value so that other places can hook into it and update the value if needed,
e.g. to force a specific setting based on other conditions/flags.

## Testing

Add whatever calls you want into the temporary controller class and access /index.php?module=UnifiedSettingsAccess&action=index to trigger the code.

## Caveats found so far/TODOs

- Measurable setting will return null/default value when the setting does not exist. We'd need an addition to check if a setting exists
- Hierarchy is not working if a getter class doesn't throw an exception and the first value is returned, even when it's the default
