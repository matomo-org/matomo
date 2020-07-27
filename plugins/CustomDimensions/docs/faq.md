## FAQ

__I have a large database, can I install the plugin on the command line?__

Yes, this is not only possible but even recommended as the installation may take hours. To do this follow these steps:

* Download the Plugin from [https://plugins.piwik.org/CustomDimensions](https://plugins.piwik.org/CustomDimensions)
* Extract the files within the downloaded ZIP file
* Copy the `CustomDimensions` directory into the `plugins` directory of your Piwik
* Execute the command `./console plugin:activate CustomDimensions` within your Piwik directory

__Where can I manage Custom Dimensions?__

Custom Dimensions can be managed by clicking on your username or user icon in the top right. There will be a menu
item "Custom Dimensions" within the "Manage" section of the left menu. By clicking on it you can manage Custom Dimensions.
Please note that the permission Admin is required in order to be able to manage them.

__Where can I find the Id for a Custom Dimension?__

You can find them by going to the "Manage Custom Dimensions" page in your personal area. For each dimension you will
find the Id in the table that lists all available Custom Dimensions.

__How do I set a value for a dimension in the JavaScript Tracker?__

Please have a look at the [JavaScript Tracker guide for Custom Dimensions](https://developer.piwik.org/guides/tracking-javascript-guide#custom-dimensions).

__How do I set a value for a dimension in the PHP Tracker?__

`$tracker->setCustomDimension($customDimensionId, $value);`

Please note custom tracking parameters are cleared after each tracking request. If you want to keep the same
Custom Dimensions over all request make sure to call this method before each tracking call.

__I have configured all available Custom Dimension slots, can I add more?__

Yes, this is possible. To make a new Custom Dimension slot available execute the following command including the scope option:

```
./console customdimensions:add-custom-dimension --scope=action
./console customdimensions:add-custom-dimension --scope=visit
```

Be aware that this can take a long time depending on the size of your database as it requires MySQL schema changes.
You can directly create multiple Custom Dimension slots. To do this add the option `--count=X`. Usually it doesn't take much
longer to create directly multiple new slots.

__Is it possible to delete a Custom Dimension and all of its data?__

In the UI it is only possible to deactivate a dimension. However, on the command line you can remove a Custom Dimension
and report it's log data by executing the following console command:

```
./console customdimensions:remove-custom-dimension --scope=$scope --index=$index
```

Make sure to replace `$scope` and `$index` with the correct values. To get a list of all available indexes execute `./console customdimensions:info`.

Removing a Custom Dimension may take a long time as it requires MySQL schema changes. Currently, only log data is removed. Archived reports will be
not deleted currently.
