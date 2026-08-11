## FAQ

__How do I allow specific IPs to not be blocked?__

Say you are using AWS to replay your traffic using log analytics. When you have the block clouds feature enabled, all the requests from your AWS would be blocked. However, you can specifically allow your own IPs to be allowed and not blocked using the "IP allow list" setting in "Administration => General Settings". Enter one IP address or CIDR range per line.

Alternatively, you can force a list of allowed IP ranges by editing your `config/config.ini.php` file like this (this overrides the setting, and the field is hidden from the UI while the override is in place):

```
[TrackingSpamPrevention]
ip_allow_list[] = "127.0.0.1/32"
ip_allow_list[] = "192.168.0.0/21"
```

Make sure to enter a valid IP range. 

__How do I block specific IPs from being tracked?__

Use the "IP block list" setting in "Administration => General Settings". Enter one IP address or CIDR range per line. If an address matches both the allow list and the block list, the allow list takes precedence.

Alternatively, you can force a list of blocked IP ranges by editing your `config/config.ini.php` file like this (this overrides the setting, and the field is hidden from the UI while the override is in place):

```
[TrackingSpamPrevention]
ip_block_list[] = "203.0.113.88/32"
ip_block_list[] = "198.51.100.0/24"
```

__What happens when it fails to synchronise public IPs from cloud providers?__

Any error is currently ignored and if it does not synchronise successfully, then the IP for the provider that failed are not synced.

To be aware when such an error happens you can enable the following setting:

```
[TrackingSpamPrevention]
block_cloud_sync_throw_exception_on_error = 1
```

It is disabled by default as it could stop other scheduled tasks from being executed.

__How can I block specific organisations from being tracked?__

This can be useful if you are receiving spam requests from a provider that isn't automatically detected yet by this plugin.

For this to work the "Block tracking requests from the cloud" setting must be enabled and a geolocation provider must be enabled.

You can block any organisation (if the geolocation database you are using includes this information) using the "Organisation block list" setting in "Administration => General Settings". Enter one organisation per line. The list is pre-filled with a default set of hosting and datacenter providers, which you can extend or trim as needed.

Alternatively, you can execute a command to block a new organisation like this:

```bash
./console trackingspamprevention:block-geo-ip-organisation --organisation-name="Example"
```

You can also force a list of blocked organisations by editing your `config/config.ini.php` file like this (this overrides the setting, and the field is hidden from the UI while the override is in place):

```
[TrackingSpamPrevention]
organisation_block_list[] = "ExampleOrg"
organisation_block_list[] = "another example"
```

Each organisation will be compared lower case and the organisation only needs to contain the configured value, it does not need to match it exactly.

You can find out the organisation name for an IP address by visiting the website of your geolocation database and using their demo tool.
