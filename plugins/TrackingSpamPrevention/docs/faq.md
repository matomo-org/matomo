## FAQ

__How do I allow specific IPs to not be blocked?__

Say you are using AWS to replay your traffic using log analytics. When you have cloud provider IP range blocking enabled, all the requests from your AWS would be blocked. However, you can specifically allow your own IPs to be allowed and not blocked using the "IP allow list" setting in "Administration => General Settings". Enter one IP address or CIDR range per line.

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

For this to work a geolocation provider must be enabled, and "Block tracking requests from Cloud hosting providers" in "Administration => General Settings" must be set to "Use custom organisation list". The "Organisation block list" starts from whatever list is already stored for your installation, which is Matomo's default set of hosting and datacenter providers until something changes it. Extend or trim it as needed. Note that a stored list stops following Matomo's maintained one, so if you find yourself on this option after upgrading and did not choose it, selecting "Use Matomo's default provider list" puts you back on the list Matomo keeps up to date.

The other two options need no list of your own: "Use Matomo's default provider list" always matches against the current default set, and "Do not block Cloud hosting providers" turns organisation matching off. Your custom list is kept either way, so it is still there when you switch back.

Alternatively, you can execute a command to block a new organisation like this:

```bash
./console trackingspamprevention:block-geo-ip-organisation --organisation-name="Example"
```

When "Use Matomo's default provider list" is selected the command switches to the custom organisation list, so that the organisation it adds is actually matched. When blocking is turned off it leaves that alone and tells you the entry has no effect until you select the custom list.

You can also force a list of blocked organisations by editing your `config/config.ini.php` file like this (this overrides the setting, and the field is hidden from the UI while the override is in place):

```
[TrackingSpamPrevention]
organisation_block_list[] = "ExampleOrg"
organisation_block_list[] = "another example"
```

An overridden list is used whenever organisation blocking is on, including under "Use Matomo's default provider list".

Each organisation will be compared lower case and the organisation only needs to contain the configured value, it does not need to match it exactly.

You can find out the organisation name for an IP address by visiting the website of your geolocation database and using their demo tool.
