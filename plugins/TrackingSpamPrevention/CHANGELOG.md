## Changelog

# 5.2.0 - 2026-08-10
- Added an "Organisation block list" setting to the UI (General Settings, shown while "Block tracking requests from the cloud" is enabled) to manage the organisations blocked by that feature. Existing `block_geoip_organisations` config values are migrated to the new `organisation_block_list` system setting and removed from the config file; an emptied config list is migrated as an empty list, keeping organisation blocking disabled, while a removed config key results in the default block list applying again
- The `trackingspamprevention:block-geo-ip-organisation` command now saves the organisation to the new system setting instead of the config file
- Added introduction text for TrackingSpamPrevention system setting

# 5.1.0 - 2026-08-03
- Added an "IP allow list" setting to the UI (General Settings). Existing `iprange_allowlist` config values are migrated to the new `ip_allow_list` system setting and removed from the config file
- Added an "IP block list" setting to the UI (General Settings) to block tracking requests from specific IP addresses or ranges
- Fixed Digital Ocean IP ranges not being blocked when "Block tracking requests from the cloud" is enabled
- Added more hosting/datacenter providers to the default block list used by "Block tracking requests from the cloud"

# 5.0.11 - 2026-07-06
- Enabled block headless browser by default

# 5.0.10 - 2026-06-22
- Removed Verein zur Foerderung eines Deutschen Forschungsnetzes (DFN) from the default block list

# 5.0.9 - 2026-05-11
- Added code to block more providers by default

# 5.0.8 - 2025-05-26
- Fixed broken Azure link for looking up IP ranges

# 5.0.7 - 2025-01-06
- Added Matomo URL to email report

# 5.0.6 - 2024-12-09
- Fixed broken Azure link for looking up IP ranges

# 5.0.5 - 2024-10-24
- Look for headless browser in clientHints to detect spam

# 5.0.4 - 2024-10-21
- Compatability with PHP 8.4

# 5.0.3
- Textual changes

# 5.0.2
- Added plugin category for Marketplace

# 5.0.1
- Fix issue where max action limit was blocking IP addresses from the allow list

# 5.0.0
- Compatibility with Matomo 5.0

# 4.1.7
- Fixed Azure IP ranges download URL parsing code better accounting for character encoding

# 4.1.6
- Started including userAgent in the banned IP email

# 4.1.5
- Azure IP ranges download code updated and added tests to alert if download fails

# 4.1.4
- Add new command to block new organisations `./console trackingspamprevention:block-geo-ip-organisation --organisation-name="Example"`

# 4.1.3
- Translation changes
- Added code to not throw exception if digitalOcean file is empty

# 4.1.2
- Fix location data in email to show IP instead of IP range
- Started blocking digital ocean through providers.
- Stopped checking GeoIp DB if UserCountry plugin is disabled
- Azure IP ranges download code updated

# 4.1.1
- Fixed IP ban notification email leading to internal sever error

# 4.1.0
- Exclude user agents from load testing services
- Exclude user agents from server side tracking SDK by enabling an option

# 4.0.0
* Initial version
