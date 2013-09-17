Create a Theme for Piwik
==============

Quick start
------------

1. Create a file describing your plugin at the following path: "plugins/YourPluginName/plugin.json"

```json
{
  "theme": true,
  "stylesheet": "stylesheets/theme.less"
}
```

2. Create your stylesheet file with the following path: "plugins/YourPluginName/stylesheets/theme.less"

3. Activate your theme on the Piwik instance: Settings > Platform > Themes


About the plugin.json file
------------

You can complete your plugin.json file with the following entries:

* "description"
* "homepage"
* "author"
* "author_homepage"
* "license"
* "license_homepage"
* "version"


Activate the development mode
-------------
If you change your theme.less file, you will not see the difference on your Piwik instance.
The stylesheets have a cache mode to prevent from compiling them on every page call.
To disable it, you have to modify the "config/config.ini.php" file:

```ini
	[Debug]
	disable_merged_assets = 1
```


Limitations
------------
You just can not theme:

* Installation plugin pages
* CoreUpdater plugin pages


How to theme 
===============

Images
----------
You can stock your images in the folder "plugins/YourPluginName/images".
To use images in CSS, you have to use a relative path that start at the root folder.

Example: 

```css
  background-image: url(plugins/YourPluginName/images/dropDown.jpg);
```

Multiple stylesheets files
----------
You can submit only one stylesheets file for theme.
But you can import other Less files from the main theme file:

Example: 

```css
  @import "../../plugins/YourPluginName/stylesheets/_yourSubStylesheetName.less"
```

It's important to use this complex path to prevent compilation bugs.
It is better to prefix your sub stylesheet file name with an '_'. 


Graphs
----------
You can style some graph elements.
You should see "plugins/CoreHome/stylesheets/jqplotColors.less" for more informations.


Sparklines
----------
You can style some sparklines elements.
You should see "plugins/CoreHome/stylesheets/sparklineColors.less" for more informations.


Transitions
----------
You can style some transitions elements.
You should see "plugins/Transition/stylesheets/_transitionColors.less" for more informations.

